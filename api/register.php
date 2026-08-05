<?php
/**
 * Student Registration API with Face Capture
 * Endpoint: POST /api/register.php
 */

require_once '../config/auth.php';
header('Content-Type: application/json');
setCorsHeaders();


require_once '../config/database.php';
require_once '../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields (removed user_id and course)
    $required = ['name', 'email', 'department', 'semester', 'face_descriptor'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            errorResponse("Missing required field: $field");
        }
    }
    
    // Sanitize inputs
    $name = sanitize($input['name']);
    $email = sanitize($input['email']);
    $department = sanitize($input['department']);
    $semester = (int)$input['semester'];
    $phone = sanitize($input['phone'] ?? '');
    $parentPhone = sanitize($input['parent_phone'] ?? '');
    
    $db = getDB();
    
    // Auto-generate Student ID
    // Format: STU + Year + Sequential Number (e.g., STU20260001)
    $currentYear = date('Y');

    // Get the last student ID for current year
    $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id LIKE ? ORDER BY user_id DESC LIMIT 1");
    $stmt->execute(['STU' . $currentYear . '%']);
    $lastUser = $stmt->fetch();

    if ($lastUser) {
        // Extract the number and increment (last 4 digits now)
        $lastNumber = (int)substr($lastUser['user_id'], -4);
        $newNumber = $lastNumber + 1;
    } else {
        // First student of the year
        $newNumber = 1;
    }

    // Generate new Student ID with 4-digit zero padding (supports up to 9999/year)
    $userId = 'STU' . $currentYear . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
    // Validate email
    if (!validateEmail($email)) {
        errorResponse('Invalid email format');
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        errorResponse('Email already exists');
    }
    
    // Validate face descriptor
    if (!validateFaceDescriptor($input['face_descriptor'])) {
        errorResponse('Invalid face descriptor format');
    }
    
    $faceDescriptor = $input['face_descriptor'];
    
    // Additional face descriptors (optional)
    $faceDescriptor2 = $input['face_descriptor_2'] ?? null;
    $faceDescriptor3 = $input['face_descriptor_3'] ?? null;
    
    // Generate parent code
    $parentCode = generateParentCode();
    
    // Hash password if provided
    $password = null;
    if (!empty($input['password'])) {
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    // Insert new user (removed course and parent_email fields)
    $stmt = $db->prepare("
        INSERT INTO users (
            user_id, name, email, password, role_id, department, semester,
            face_descriptor, face_descriptor_2, face_descriptor_3,
            phone, parent_phone, parent_code, status
        ) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([
        $userId, $name, $email, $password, $department, $semester,
        $faceDescriptor, $faceDescriptor2, $faceDescriptor3,
        $phone, $parentPhone, $parentCode
    ]);
    
    $newUserId = $db->lastInsertId();
    
    // Log activity
    logActivity($newUserId, 'user_registered', "New student registered: $name");
    
    // Create welcome notification
    createNotification(
        $newUserId,
        'Welcome to Attendance System',
        'Your account has been created successfully. You can now mark attendance using face recognition.',
        'success',
        'medium'
    );
    
    // Send email to student (if email system configured)
    if (getSetting('enable_email_notifications', false) && !empty($email)) {
        $subject = "Welcome to College Attendance System";
        $message = "Dear $name,\n\nYour account has been created successfully.\n\nStudent ID: $userId\nDepartment: $department\nSemester: $semester\n\nYou can now mark attendance using face recognition.\n\nBest regards,\nAttendance System";
        sendEmail($email, $subject, $message);
    }
    
    successResponse('Registration successful', [
        'user_id' => $userId,
        'name' => $name,
        'email' => $email,
        'parent_code' => $parentCode
    ]);
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

