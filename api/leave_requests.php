<?php
/**
 * Leave Requests API
 * Endpoint: GET/POST/PUT /api/leave_requests.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiLogin();


require_once '../config/database.php';
require_once '../config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            // Get leave requests
            $id = $_GET['id'] ?? null;
            $userId = $_GET['user_id'] ?? null;
            $status = $_GET['status'] ?? null;
            
            if ($id) {
                // Get single leave request
                $stmt = $db->prepare("
                    SELECT lr.*, u.user_id, u.name, u.email, u.department, u.course,
                           r.name as reviewer_name
                    FROM leave_requests lr
                    JOIN users u ON lr.user_id = u.id
                    LEFT JOIN users r ON lr.reviewed_by = r.id
                    WHERE lr.id = ?
                ");
                $stmt->execute([$id]);
                $request = $stmt->fetch();
                
                if (!$request) {
                    errorResponse('Leave request not found');
                }
                
                successResponse('Leave request retrieved', $request);
            } else {
                // Get all leave requests
                $query = "
                    SELECT lr.*, u.user_id, u.name, u.email, u.department, u.course
                    FROM leave_requests lr
                    JOIN users u ON lr.user_id = u.id
                    WHERE 1=1
                ";
                $params = [];
                
                if ($userId) {
                    $query .= " AND (lr.user_id = ? OR u.user_id = ?)";
                    $params[] = $userId;
                    $params[] = $userId;
                }
                
                if ($status) {
                    $query .= " AND lr.status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY lr.created_at DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $requests = $stmt->fetchAll();
                
                // Calculate days for each request
                foreach ($requests as &$request) {
                    $request['total_days'] = daysBetween($request['start_date'], $request['end_date']) + 1;
                }
                
                successResponse('Leave requests retrieved', $requests);
            }
            break;
            
        case 'POST':
            // Submit new leave request
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['user_id', 'leave_type', 'start_date', 'end_date', 'reason'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            // Get user
            $stmt = $db->prepare("SELECT id, name, email FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$input['user_id'], $input['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                errorResponse('User not found');
            }
            
            // Validate dates
            $startDate = $input['start_date'];
            $endDate = $input['end_date'];
            
            if (strtotime($endDate) < strtotime($startDate)) {
                errorResponse('End date must be after start date');
            }
            
            // Insert leave request
            $stmt = $db->prepare("
                INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, supporting_document, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $user['id'],
                $input['leave_type'],
                $startDate,
                $endDate,
                sanitize($input['reason']),
                sanitize($input['supporting_document'] ?? '')
            ]);
            
            $requestId = $db->lastInsertId();
            
            logActivity($user['id'], 'leave_request_submitted', "Leave request from $startDate to $endDate");
            
            createNotification(
                $user['id'],
                'Leave Request Submitted',
                'Your leave request has been submitted for approval.',
                'info',
                'medium'
            );
            
            // Send email notification
            if (getSetting('enable_email_notifications', false)) {
                $totalDays = daysBetween($startDate, $endDate) + 1;
                $subject = "Leave Request Submitted";
                $message = "Dear {$user['name']},\n\nYour leave request has been submitted:\n\nType: {$input['leave_type']}\nFrom: $startDate\nTo: $endDate\nDays: $totalDays\n\nStatus: Pending Review\n\nBest regards,\nAttendance System";
                sendEmail($user['email'], $subject, $message);
            }
            
            successResponse('Leave request submitted successfully', ['id' => $requestId]);
            break;
            
        case 'PUT':
            // Update leave request (approve/reject)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id']) || empty($input['status'])) {
                errorResponse('Request ID and status are required');
            }
            
            $validStatuses = ['approved', 'rejected', 'cancelled'];
            if (!in_array($input['status'], $validStatuses)) {
                errorResponse('Invalid status');
            }
            
            // Get request details
            $stmt = $db->prepare("
                SELECT lr.*, u.name, u.email 
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                WHERE lr.id = ?
            ");
            $stmt->execute([$input['id']]);
            $request = $stmt->fetch();
            
            if (!$request) {
                errorResponse('Leave request not found');
            }
            
            // Update request
            $stmt = $db->prepare("
                UPDATE leave_requests 
                SET status = ?, reviewed_by = ?, review_date = NOW(), review_comments = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['status'],
                (int)($input['reviewed_by'] ?? 0),
                sanitize($input['review_comments'] ?? ''),
                $input['id']
            ]);
            
            // If approved, mark attendance as excused for those dates
            if ($input['status'] === 'approved') {
                $currentDate = $request['start_date'];
                $endDate = $request['end_date'];
                
                while (strtotime($currentDate) <= strtotime($endDate)) {
                    if (!isWeekend($currentDate)) {
                        // Check if attendance record exists
                        $stmt = $db->prepare("
                            SELECT id FROM attendance 
                            WHERE user_id = ? AND date = ?
                        ");
                        $stmt->execute([$request['user_id'], $currentDate]);
                        
                        if ($stmt->fetch()) {
                            // Update existing
                            $stmt = $db->prepare("
                                UPDATE attendance 
                                SET status = 'excused', remarks = 'Leave approved'
                                WHERE user_id = ? AND date = ?
                            ");
                            $stmt->execute([$request['user_id'], $currentDate]);
                        } else {
                            // Insert new
                            $stmt = $db->prepare("
                                INSERT INTO attendance (user_id, date, time_in, status, remarks)
                                VALUES (?, ?, '00:00:00', 'excused', 'Leave approved')
                            ");
                            $stmt->execute([$request['user_id'], $currentDate]);
                        }
                    }
                    
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                }
            }
            
            logActivity($request['user_id'], 'leave_request_' . $input['status'], "Leave request {$input['status']}");
            
            // Create notification
            $notificationType = $input['status'] === 'approved' ? 'success' : 'warning';
            $notificationMessage = $input['status'] === 'approved'
                ? 'Your leave request has been approved.'
                : 'Your leave request has been rejected.';
            
            createNotification(
                $request['user_id'],
                'Leave Request ' . ucfirst($input['status']),
                $notificationMessage,
                $notificationType,
                'high'
            );
            
            // Send email
            if (getSetting('enable_email_notifications', false)) {
                $subject = "Leave Request " . ucfirst($input['status']);
                $message = "Dear {$request['name']},\n\nYour leave request has been {$input['status']}.\n\n";
                if (!empty($input['review_comments'])) {
                    $message .= "Comments: {$input['review_comments']}\n\n";
                }
                $message .= "Best regards,\nAttendance System";
                sendEmail($request['email'], $subject, $message);
            }
            
            successResponse('Leave request updated successfully');
            break;
            
        default:
            errorResponse('Method not allowed', 405);
    }
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

