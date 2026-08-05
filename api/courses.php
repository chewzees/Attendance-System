<?php
/**
 * Courses API
 * Endpoint: GET/POST/PUT/DELETE /api/courses.php
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
            // Get courses
            $id = $_GET['id'] ?? null;
            $department = $_GET['department'] ?? null;
            $semester = $_GET['semester'] ?? null;
            
            if ($id) {
                // Get single course
                $stmt = $db->prepare("
                    SELECT c.*, u.name as professor_name,
                           (SELECT COUNT(*) FROM course_enrollment WHERE course_id = c.id AND status = 'active') as student_count
                    FROM courses c
                    LEFT JOIN users u ON c.professor_id = u.id
                    WHERE c.id = ?
                ");
                $stmt->execute([$id]);
                $course = $stmt->fetch();
                
                if (!$course) {
                    errorResponse('Course not found');
                }
                
                successResponse('Course retrieved', $course);
            } else {
                // Get all courses
                $query = "
                    SELECT c.*, u.name as professor_name,
                           (SELECT COUNT(*) FROM course_enrollment WHERE course_id = c.id AND status = 'active') as student_count
                    FROM courses c
                    LEFT JOIN users u ON c.professor_id = u.id
                    WHERE 1=1
                ";
                $params = [];
                
                if ($department) {
                    $query .= " AND c.department = ?";
                    $params[] = $department;
                }
                
                if ($semester) {
                    $query .= " AND c.semester = ?";
                    $params[] = $semester;
                }
                
                $query .= " ORDER BY c.course_code";
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $courses = $stmt->fetchAll();
                
                successResponse('Courses retrieved', $courses);
            }
            break;
            
        case 'POST':
            // Create new course
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['course_code', 'course_name', 'department', 'semester'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            // Check if course code exists
            $stmt = $db->prepare("SELECT id FROM courses WHERE course_code = ?");
            $stmt->execute([$input['course_code']]);
            if ($stmt->fetch()) {
                errorResponse('Course code already exists');
            }
            
            $stmt = $db->prepare("
                INSERT INTO courses (course_code, course_name, department, semester, credits, professor_id, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $stmt->execute([
                sanitize($input['course_code']),
                sanitize($input['course_name']),
                sanitize($input['department']),
                (int)$input['semester'],
                (int)($input['credits'] ?? 3),
                (int)($input['professor_id'] ?? 0) ?: null,
                sanitize($input['description'] ?? '')
            ]);
            
            successResponse('Course created successfully', ['id' => $db->lastInsertId()]);
            break;
            
        case 'PUT':
            // Update course
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Course ID is required');
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['course_name', 'department', 'semester', 'credits', 'professor_id', 'description', 'status'];
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
            
            $query = "UPDATE courses SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            successResponse('Course updated successfully');
            break;
            
        case 'DELETE':
            // Delete course (HARD DELETE - permanently removes from database)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Course ID is required');
            }
            
            // Get course info before deletion
            $stmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
            $stmt->execute([$input['id']]);
            $course = $stmt->fetch();
            
            if (!$course) {
                errorResponse('Course not found');
            }
            
            // HARD DELETE - Permanently remove from database
            // This will CASCADE delete related records (enrollments, lectures, etc.)
            $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            successResponse('Course permanently deleted from database');
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

