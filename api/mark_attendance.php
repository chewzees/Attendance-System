<?php
/**
 * Mark Attendance API with Face Recognition
 * Endpoint: POST /api/mark_attendance.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiLogin();


require_once '../config/database.php';
require_once '../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['face_descriptor'])) {
        errorResponse('Face descriptor is required');
    }
    
    $faceDescriptor = $input['face_descriptor'];
    
    // Validate face descriptor
    if (!validateFaceDescriptor($faceDescriptor)) {
        errorResponse('Invalid face descriptor format');
    }
    
    $db = getDB();
    
    // Get threshold from settings
    $threshold = getSetting('face_recognition_threshold', 0.6);
    
    // Get all active users with face descriptors
    $stmt = $db->query("
        SELECT id, user_id, name, department, course, 
               face_descriptor, face_descriptor_2, face_descriptor_3
        FROM users 
        WHERE status = 'active' 
        AND face_descriptor IS NOT NULL
    ");
    
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        errorResponse('No registered users found');
    }
    
    // Find matching user
    $bestMatch = null;
    $bestDistance = PHP_FLOAT_MAX;
    $bestSimilarity = 0;
    
    foreach ($users as $user) {
        $descriptors = [$user['face_descriptor']];
        
        if (!empty($user['face_descriptor_2'])) {
            $descriptors[] = $user['face_descriptor_2'];
        }
        if (!empty($user['face_descriptor_3'])) {
            $descriptors[] = $user['face_descriptor_3'];
        }
        
        foreach ($descriptors as $descriptor) {
            // Calculate Euclidean distance
            $distance = euclideanDistance($faceDescriptor, $descriptor);
            
            // Calculate cosine similarity
            $similarity = cosineSimilarity($faceDescriptor, $descriptor);
            
            // Combined metric (lower distance + higher similarity is better)
            if ($distance < $threshold && $similarity > 0.4) {
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestSimilarity = $similarity;
                    $bestMatch = $user;
                }
            }
        }
    }
    
    if ($bestMatch === null) {
        errorResponse('Face not recognized. Please register first or try again.');
    }
    
    // Check if attendance already marked today
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$bestMatch['id'], $today]);
    
    if ($stmt->fetch()) {
        errorResponse('Attendance already marked for today');
    }
    
    // Mark attendance with department-specific times
    $currentTime = date('H:i:s');
    
    // Get department schedule (uses department-specific times)
    // Use default department if null
    $department = $bestMatch['department'] ?? 'Computer Science';
    $schedule = getDepartmentSchedule($department);
    $startTime = $schedule['start_time'];
    $lateThreshold = $schedule['late_threshold_minutes'];
    
    $isLate = isLateDepartment($currentTime, $department);
    $minutesLate = $isLate ? calculateMinutesLateDepartment($currentTime, $department) : 0;
    
    // Determine status
    $status = 'present';
    if ($isLate && $minutesLate > $lateThreshold) {
        $status = 'late';
    }
    
    // Insert attendance record
    $stmt = $db->prepare("
        INSERT INTO attendance (user_id, date, time_in, status, is_late, minutes_late)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $bestMatch['id'],
        $today,
        $currentTime,
        $status,
        $isLate ? 1 : 0,
        $minutesLate
    ]);
    
    // Log activity
    logActivity($bestMatch['id'], 'attendance_marked', "Attendance marked at $currentTime - Status: $status");
    
    // Create notification
    $notificationMessage = $status === 'late' 
        ? "You were marked late by $minutesLate minutes today."
        : "Your attendance has been marked successfully.";
    
    createNotification(
        $bestMatch['id'],
        'Attendance Marked',
        $notificationMessage,
        $status === 'late' ? 'warning' : 'success',
        'medium'
    );
    
    // Calculate current attendance percentage
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days
        FROM attendance 
        WHERE user_id = ?
    ");
    $stmt->execute([$bestMatch['id']]);
    $stats = $stmt->fetch();
    
    $attendancePercentage = calculateAttendancePercentage(
        $stats['present_days'],
        $stats['total_days']
    );
    
    // Check warning levels
    $warningLevel = getWarningLevel($attendancePercentage);
    if ($warningLevel > 0) {
        $warningMessages = [
            1 => 'Your attendance is below 85%. Please maintain regularity.',
            2 => 'Warning: Your attendance is below 80%. Risk of shortage.',
            3 => 'Critical: Your attendance is below 75%. Immediate action required.'
        ];
        
        createNotification(
            $bestMatch['id'],
            'Attendance Warning',
            $warningMessages[$warningLevel],
            'warning',
            'high'
        );
    }
    
    // Calculate confidence safely
    $confidence = 0;
    if ($bestDistance < 1) {
        $confidence = round((1 - $bestDistance) * 100, 2);
    } else {
        // If distance is high, use similarity as confidence
        $confidence = round($bestSimilarity * 100, 2);
    }
    
    successResponse('Attendance marked successfully', [
        'user_id' => $bestMatch['user_id'],
        'name' => $bestMatch['name'],
        'department' => $bestMatch['department'] ?? null,
        'course' => $bestMatch['course'] ?? null,
        'time' => formatTime($currentTime),
        'status' => $status,
        'is_late' => $isLate,
        'minutes_late' => $minutesLate,
        'confidence' => max(0, min(100, $confidence)), // Clamp between 0-100
        'similarity' => round($bestSimilarity * 100, 2),
        'attendance_percentage' => $attendancePercentage
    ]);
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

