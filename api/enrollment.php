<?php
/**
 * Course Enrollment API
 * Endpoint: GET/POST/DELETE /api/enrollment.php
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
            // Get enrollments
            $studentId = $_GET['student_id'] ?? null;
            $courseId = $_GET['course_id'] ?? null;
            $semesterId = $_GET['semester_id'] ?? null;
            
            $query = "
                SELECT ce.*, c.course_code, c.course_name, c.credits, c.department,
                       u.user_id, u.name as student_name,
                       s.semester_name
                FROM course_enrollment ce
                JOIN courses c ON ce.course_id = c.id
                JOIN users u ON ce.student_id = u.id
                LEFT JOIN semesters s ON ce.semester_id = s.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($studentId) {
                $query .= " AND (ce.student_id = ? OR u.user_id = ?)";
                $params[] = $studentId;
                $params[] = $studentId;
            }
            
            if ($courseId) {
                $query .= " AND ce.course_id = ?";
                $params[] = $courseId;
            }
            
            if ($semesterId) {
                $query .= " AND ce.semester_id = ?";
                $params[] = $semesterId;
            }
            
            $query .= " ORDER BY ce.enrollment_date DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $enrollments = $stmt->fetchAll();
            
            // Add attendance stats for each enrollment
            foreach ($enrollments as &$enrollment) {
                $stmt = $db->prepare("
                    SELECT 
                        COUNT(la.id) as total_lectures,
                        SUM(CASE WHEN la.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended
                    FROM lectures l
                    LEFT JOIN lecture_attendance la ON l.id = la.lecture_id AND la.student_id = ?
                    WHERE l.course_id = ? AND l.status = 'completed'
                ");
                $stmt->execute([$enrollment['student_id'], $enrollment['course_id']]);
                $stats = $stmt->fetch();
                
                $enrollment['total_lectures'] = $stats['total_lectures'];
                $enrollment['attended_lectures'] = $stats['attended'];
                $enrollment['attendance_percentage'] = calculateAttendancePercentage(
                    $stats['attended'],
                    $stats['total_lectures']
                );
            }
            
            successResponse('Enrollments retrieved', $enrollments);
            break;
            
        case 'POST':
            // Enroll student in course
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['student_id']) || empty($input['course_id'])) {
                errorResponse('Student ID and Course ID are required');
            }
            
            // Get student
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$input['student_id'], $input['student_id']]);
            $student = $stmt->fetch();
            
            if (!$student) {
                errorResponse('Student not found');
            }
            
            // Get course
            $stmt = $db->prepare("SELECT id FROM courses WHERE id = ?");
            $stmt->execute([$input['course_id']]);
            if (!$stmt->fetch()) {
                errorResponse('Course not found');
            }
            
            // Get current semester if not provided
            $semesterId = $input['semester_id'] ?? null;
            if (!$semesterId) {
                $semester = getCurrentSemester();
                $semesterId = $semester['id'] ?? null;
            }
            
            // Check if already enrolled
            $stmt = $db->prepare("
                SELECT id FROM course_enrollment 
                WHERE student_id = ? AND course_id = ? AND status = 'active'
            ");
            $stmt->execute([$student['id'], $input['course_id']]);
            
            if ($stmt->fetch()) {
                errorResponse('Student already enrolled in this course');
            }
            
            // Enroll student
            $stmt = $db->prepare("
                INSERT INTO course_enrollment (student_id, course_id, semester_id, status)
                VALUES (?, ?, ?, 'active')
            ");
            $stmt->execute([$student['id'], $input['course_id'], $semesterId]);
            
            $enrollmentId = $db->lastInsertId();
            
            logActivity($student['id'], 'course_enrolled', "Enrolled in course ID: " . $input['course_id']);
            
            createNotification(
                $student['id'],
                'Course Enrollment',
                'You have been enrolled in a new course.',
                'success',
                'medium'
            );
            
            successResponse('Enrollment successful', ['id' => $enrollmentId]);
            break;
            
        case 'DELETE':
            // Drop enrollment
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Enrollment ID is required');
            }
            
            // Update status to dropped instead of deleting
            $stmt = $db->prepare("UPDATE course_enrollment SET status = 'dropped' WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            successResponse('Enrollment dropped successfully');
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

