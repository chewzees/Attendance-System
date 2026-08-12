<?php
/**
 * Attendance Records API
 * Endpoint: GET/POST /api/attendance.php
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
            // Get attendance records
            $userId = $_GET['user_id'] ?? null;
            $date = $_GET['date'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $status = $_GET['status'] ?? null;
            $department = $_GET['department'] ?? null;
            
            $query = "
                SELECT a.*, u.user_id, u.name, u.department, u.course, u.semester
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($userId) {
                $query .= " AND (u.id = ? OR u.user_id = ?)";
                $params[] = $userId;
                $params[] = $userId;
            }
            
            if ($date) {
                $query .= " AND a.date = ?";
                $params[] = $date;
            }
            
            if ($startDate && $endDate) {
                $query .= " AND a.date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($status) {
                $query .= " AND a.status = ?";
                $params[] = $status;
            }
            
            if ($department) {
                $query .= " AND u.department = ?";
                $params[] = $department;
            }
            
            $query .= " ORDER BY a.date DESC, a.time_in DESC";
            
            // Pagination
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 50);
            $offset = ($page - 1) * $perPage;
            
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            // Format records
            foreach ($records as &$record) {
                $record['time_in_formatted'] = formatTime($record['time_in']);
                if ($record['time_out']) {
                    $record['time_out_formatted'] = formatTime($record['time_out']);
                }
                $record['date_formatted'] = formatDate($record['date'], 'M d, Y');
                $record['day_name'] = getDayName($record['date']);
            }
            
            successResponse('Attendance records retrieved', $records);
            break;
            
        case 'POST':
            // Manual attendance marking (for bulk or corrections)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['user_id']) || empty($input['date'])) {
                errorResponse('User ID and date are required');
            }
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$input['user_id'], $input['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                errorResponse('User not found');
            }
            
            $userId = $user['id'];
            $date = $input['date'];
            $timeIn = $input['time_in'] ?? date('H:i:s');
            $timeOut = $input['time_out'] ?? null;
            $status = $input['status'] ?? 'present';
            $remarks = $input['remarks'] ?? null;
            $markedBy = $input['marked_by'] ?? null;
            
            // Calculate late status
            $startTime = getSetting('attendance_start_time', '09:00:00');
            $isLate = isLate($timeIn, $startTime);
            $minutesLate = $isLate ? calculateMinutesLate($timeIn, $startTime) : 0;
            
            // Check if already exists
            $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
            $stmt->execute([$userId, $date]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $db->prepare("
                    UPDATE attendance 
                    SET time_in = ?, time_out = ?, status = ?, is_late = ?, 
                        minutes_late = ?, remarks = ?, marked_by = ?
                    WHERE user_id = ? AND date = ?
                ");
                $stmt->execute([
                    $timeIn, $timeOut, $status, $isLate ? 1 : 0, 
                    $minutesLate, $remarks, $markedBy, $userId, $date
                ]);
                
                logActivity($userId, 'attendance_updated', "Attendance updated for $date");
                successResponse('Attendance updated successfully');
            } else {
                // Insert new
                $stmt = $db->prepare("
                    INSERT INTO attendance (user_id, date, time_in, time_out, status, is_late, minutes_late, remarks, marked_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId, $date, $timeIn, $timeOut, $status, 
                    $isLate ? 1 : 0, $minutesLate, $remarks, $markedBy
                ]);
                
                logActivity($userId, 'attendance_marked', "Manual attendance marked for $date");
                successResponse('Attendance marked successfully');
            }
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

