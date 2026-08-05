<?php
/**
 * Lectures Management API
 * Endpoint: GET/POST/PUT/DELETE /api/lectures.php
 */

require_once '../config/auth.php';
header('Content-Type: application/json');
setCorsHeaders();
$authUser = requireApiLogin();


require_once '../config/database.php';
require_once '../config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            $courseId = $_GET['course_id'] ?? null;
            $date = $_GET['date'] ?? null;
            $status = $_GET['status'] ?? null;
            
            if ($id) {
                // Get single lecture with attendance details
                $stmt = $db->prepare("
                    SELECT l.*, c.course_code, c.course_name, c.department,
                           u.name as professor_name,
                           (SELECT COUNT(*) FROM lecture_attendance WHERE lecture_id = l.id) as total_students,
                           (SELECT COUNT(*) FROM lecture_attendance WHERE lecture_id = l.id AND status IN ('present', 'late')) as present_count
                    FROM lectures l
                    JOIN courses c ON l.course_id = c.id
                    LEFT JOIN users u ON l.created_by = u.id
                    WHERE l.id = ?
                ");
                $stmt->execute([$id]);
                $lecture = $stmt->fetch();
                
                if (!$lecture) {
                    errorResponse('Lecture not found');
                }
                
                // Get attendance list
                $stmt = $db->prepare("
                    SELECT la.*, u.user_id, u.name, u.department
                    FROM lecture_attendance la
                    JOIN users u ON la.student_id = u.id
                    WHERE la.lecture_id = ?
                    ORDER BY la.status, u.name
                ");
                $stmt->execute([$id]);
                $attendance = $stmt->fetchAll();
                
                $lecture['attendance'] = $attendance;
                
                successResponse('Lecture retrieved', $lecture);
            } else {
                // Get lectures list
                $query = "
                    SELECT l.*, c.course_code, c.course_name, c.department,
                           u.name as professor_name
                    FROM lectures l
                    JOIN courses c ON l.course_id = c.id
                    LEFT JOIN users u ON l.created_by = u.id
                    WHERE 1=1
                ";
                $params = [];
                
                if ($courseId) {
                    $query .= " AND l.course_id = ?";
                    $params[] = $courseId;
                }
                
                if ($date) {
                    $query .= " AND l.lecture_date = ?";
                    $params[] = $date;
                }
                
                if ($status) {
                    $query .= " AND l.status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY l.lecture_date DESC, l.lecture_time DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $lectures = $stmt->fetchAll();
                
                successResponse('Lectures retrieved', $lectures);
            }
            break;
            
        case 'POST':
            // Create new lecture
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['course_id', 'lecture_date', 'lecture_time'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            $stmt = $db->prepare("
                INSERT INTO lectures (course_id, lecture_date, lecture_time, duration, room_number, 
                                    lecture_type, topic, description, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)
            ");
            
            $stmt->execute([
                (int)$input['course_id'],
                $input['lecture_date'],
                $input['lecture_time'],
                (int)($input['duration'] ?? 60),
                sanitize($input['room_number'] ?? ''),
                $input['lecture_type'] ?? 'theory',
                sanitize($input['topic'] ?? ''),
                sanitize($input['description'] ?? ''),
                (int)($input['created_by'] ?? 0)
            ]);
            
            $lectureId = $db->lastInsertId();
            
            // Auto-create attendance records for enrolled students
            if (!empty($input['auto_create_attendance'])) {
                $stmt = $db->prepare("
                    INSERT INTO lecture_attendance (lecture_id, student_id, status)
                    SELECT ?, ce.student_id, 'absent'
                    FROM course_enrollment ce
                    WHERE ce.course_id = ? AND ce.status = 'active'
                ");
                $stmt->execute([$lectureId, $input['course_id']]);
            }
            
            successResponse('Lecture created successfully', ['id' => $lectureId]);
            break;
            
        case 'PUT':
            // Update lecture
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Lecture ID is required');
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['lecture_date', 'lecture_time', 'duration', 'room_number', 
                            'lecture_type', 'topic', 'description', 'status', 'attendance_marked'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update');
            }
            
            $params[] = $input['id'];
            
            $query = "UPDATE lectures SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            successResponse('Lecture updated successfully');
            break;
            
        case 'DELETE':
            // Delete lecture
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Lecture ID is required');
            }
            
            // Check if attendance already marked
            $stmt = $db->prepare("SELECT attendance_marked FROM lectures WHERE id = ?");
            $stmt->execute([$input['id']]);
            $lecture = $stmt->fetch();
            
            if ($lecture && $lecture['attendance_marked']) {
                errorResponse('Cannot delete lecture with marked attendance');
            }
            
            $stmt = $db->prepare("DELETE FROM lectures WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            successResponse('Lecture deleted successfully');
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

