<?php
/**
 * User Management API
 * Endpoint: GET/POST/PUT/DELETE /api/users.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiRole(['admin', 'hod']);


require_once '../config/database.php';
require_once '../config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            // Get users
            $id = $_GET['id'] ?? null;
            $role = $_GET['role'] ?? null;
            $department = $_GET['department'] ?? null;
            $search = $_GET['search'] ?? null;
            
            if ($id) {
                // Get single user
                $stmt = $db->prepare("
                    SELECT u.*, ur.role_name 
                    FROM users u
                    JOIN user_roles ur ON u.role_id = ur.id
                    WHERE u.id = ? OR u.user_id = ?
                ");
                $stmt->execute([$id, $id]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    errorResponse('User not found');
                }
                
                // Remove sensitive data
                unset($user['password']);
                unset($user['face_descriptor']);
                unset($user['face_descriptor_2']);
                unset($user['face_descriptor_3']);
                
                successResponse('User retrieved', $user);
            } else {
                // Get all users with filters
                $query = "
                    SELECT u.id, u.user_id, u.name, u.email, u.department, u.course, 
                           u.semester, u.phone, u.status, u.created_at, ur.role_name
                    FROM users u
                    JOIN user_roles ur ON u.role_id = ur.id
                    WHERE 1=1
                ";
                $params = [];
                
                if ($role) {
                    $query .= " AND ur.role_name = ?";
                    $params[] = $role;
                }
                
                if ($department) {
                    $query .= " AND u.department = ?";
                    $params[] = $department;
                }
                
                if ($search) {
                    $query .= " AND (u.name LIKE ? OR u.user_id LIKE ? OR u.email LIKE ?)";
                    $searchTerm = "%$search%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $query .= " ORDER BY u.created_at DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $users = $stmt->fetchAll();
                
                successResponse('Users retrieved', $users);
            }
            break;
            
        case 'POST':
            // Create new user
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['name', 'email', 'user_id', 'role_id'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id FROM users WHERE user_id = ? OR email = ?");
            $stmt->execute([$input['user_id'], $input['email']]);
            if ($stmt->fetch()) {
                errorResponse('User ID or email already exists');
            }
            
            // Hash password
            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : null;
            
            $stmt = $db->prepare("
                INSERT INTO users (user_id, name, email, password, role_id, department, course, semester, phone, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $stmt->execute([
                sanitize($input['user_id']),
                sanitize($input['name']),
                sanitize($input['email']),
                $password,
                (int)$input['role_id'],
                sanitize($input['department'] ?? ''),
                sanitize($input['course'] ?? ''),
                (int)($input['semester'] ?? 0),
                sanitize($input['phone'] ?? '')
            ]);
            
            $newId = $db->lastInsertId();
            logActivity($newId, 'user_created', 'New user created');
            
            successResponse('User created successfully', ['id' => $newId]);
            break;
            
        case 'PUT':
            // Update user
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('User ID is required');
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['name', 'email', 'department', 'course', 'semester', 'phone', 'status', 'role_id'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = sanitize($input[$field]);
                }
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update');
            }
            
            // Update password if provided
            if (!empty($input['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            $params[] = $input['id'];
            
            $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            logActivity($input['id'], 'user_updated', 'User information updated');
            
            successResponse('User updated successfully');
            break;
            
        case 'DELETE':
            // Delete user (HARD DELETE - permanently removes from database)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('User ID is required');
            }
            
            // Get user info before deletion for logging
            $stmt = $db->prepare("SELECT user_id, name FROM users WHERE id = ?");
            $stmt->execute([$input['id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                errorResponse('User not found');
            }
            
            // Log the deletion before removing
            logActivity($input['id'], 'user_deleted_permanently', 'User permanently deleted: ' . $user['name']);
            
            // HARD DELETE - Actually remove from database
            // This will CASCADE delete related records (attendance, enrollments, etc.)
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            successResponse('User deleted permanently from database');
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

