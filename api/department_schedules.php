<?php
/**
 * Department Schedules API
 * Manage department-specific attendance times
 * Endpoint: GET/POST/PUT /api/department_schedules.php
 */

require_once '../config/auth.php';
header('Content-Type: application/json');
setCorsHeaders();
$authUser = requireApiRole(['admin', 'hod']);


require_once '../config/database.php';
require_once '../config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            // Get department schedules
            $department = $_GET['department'] ?? null;
            
            if ($department) {
                // Get specific department schedule
                $stmt = $db->prepare("SELECT * FROM department_schedules WHERE department = ? AND is_active = 1");
                $stmt->execute([$department]);
                $schedule = $stmt->fetch();
                
                if (!$schedule) {
                    // Return default system settings
                    $schedule = [
                        'department' => $department,
                        'start_time' => getSetting('attendance_start_time', '09:00:00'),
                        'end_time' => getSetting('attendance_end_time', '17:00:00'),
                        'late_threshold_minutes' => getSetting('late_threshold_minutes', 15),
                        'description' => 'Using default system settings',
                        'is_default' => true
                    ];
                }
                
                successResponse('Department schedule retrieved', $schedule);
            } else {
                // Get all department schedules
                $stmt = $db->query("
                    SELECT * FROM department_schedules 
                    WHERE is_active = 1 
                    ORDER BY department
                ");
                $schedules = $stmt->fetchAll();
                
                successResponse('Department schedules retrieved', $schedules);
            }
            break;
            
        case 'POST':
            // Create new department schedule
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['department', 'start_time', 'end_time'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            // Check if department schedule already exists
            $stmt = $db->prepare("SELECT id FROM department_schedules WHERE department = ?");
            $stmt->execute([$input['department']]);
            if ($stmt->fetch()) {
                errorResponse('Department schedule already exists. Use PUT to update.');
            }
            
            // Insert new schedule
            $stmt = $db->prepare("
                INSERT INTO department_schedules 
                (department, start_time, end_time, late_threshold_minutes, description, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                sanitize($input['department']),
                $input['start_time'],
                $input['end_time'],
                (int)($input['late_threshold_minutes'] ?? 15),
                sanitize($input['description'] ?? '')
            ]);
            
            successResponse('Department schedule created', ['id' => $db->lastInsertId()]);
            break;
            
        case 'PUT':
            // Update department schedule
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['department'])) {
                errorResponse('Department name is required');
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['start_time', 'end_time', 'late_threshold_minutes', 'description', 'is_active'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update');
            }
            
            $params[] = $input['department'];
            
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM department_schedules WHERE department = ?");
            $stmt->execute([$input['department']]);
            
            if (!$stmt->fetch()) {
                errorResponse('Department schedule not found. Use POST to create.');
            }
            
            // Update
            $query = "UPDATE department_schedules SET " . implode(', ', $updates) . " WHERE department = ?";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            successResponse('Department schedule updated');
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

