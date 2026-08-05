<?php
/**
 * Helper Functions
 * College Face Recognition Attendance System
 */

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate unique ID
function generateUniqueId($prefix = '') {
    return $prefix . strtoupper(uniqid());
}

// Calculate attendance percentage
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 2);
}

// Get attendance status color
function getAttendanceColor($percentage) {
    if ($percentage >= 85) return 'success';
    if ($percentage >= 75) return 'warning';
    return 'danger';
}

// Get warning level
function getWarningLevel($percentage) {
    if ($percentage >= 85) return 0;
    if ($percentage >= 80) return 1;
    if ($percentage >= 75) return 2;
    return 3;
}

// Format date
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '-';
    return date($format, $timestamp);
}

// Calculate days between dates
function daysBetween($start, $end) {
    if (empty($start) || empty($end)) return 0;
    try {
        $start = new DateTime($start);
        $end = new DateTime($end);
        return $start->diff($end)->days;
    } catch (Exception $e) {
        return 0;
    }
}

// Check if late
function isLate($timeIn, $startTime = '09:00:00') {
    if (empty($timeIn)) return false;
    $timeInStamp = strtotime($timeIn);
    $startStamp = strtotime($startTime);
    if ($timeInStamp === false || $startStamp === false) return false;
    return $timeInStamp > $startStamp;
}

// Calculate minutes late
function calculateMinutesLate($timeIn, $startTime = '09:00:00') {
    if (empty($timeIn)) return 0;
    $timeInStamp = strtotime($timeIn);
    $startStamp = strtotime($startTime);
    if ($timeInStamp === false || $startStamp === false) return 0;
    $diff = $timeInStamp - $startStamp;
    return max(0, floor($diff / 60));
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Success response
function successResponse($message, $data = null) {
    $response = [
        'success' => true,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

// Error response
function errorResponse($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

// Log activity
function logActivity($userId, $action, $details = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO attendance_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        // Silent fail for logging
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Create notification
function createNotification($userId, $title, $message, $type = 'info', $priority = 'medium', $actionUrl = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, priority, action_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type, $priority, $actionUrl]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

// Get system setting
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if (!$result) return $default;
        
        $value = $result['setting_value'];
        switch ($result['setting_type']) {
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return (bool)$value;
            default:
                return $value;
        }
    } catch (Exception $e) {
        return $default;
    }
}

// Update system setting
function updateSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Check user permission
function hasPermission($userId, $permission) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT ur.permissions 
            FROM users u 
            JOIN user_roles ur ON u.role_id = ur.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        if (!$result) return false;
        
        $permissions = $result['permissions'];
        if ($permissions === 'all') return true;
        
        return strpos($permissions, $permission) !== false;
    } catch (Exception $e) {
        return false;
    }
}

// Send email (placeholder - integrate with actual email service)
function sendEmail($to, $subject, $body) {
    // Implement email sending logic here
    // Can use PHPMailer or similar library
    return mail($to, $subject, $body);
}

// Get current academic year
function getCurrentAcademicYear() {
    $currentYear = date('Y');
    $currentMonth = date('n');
    
    if ($currentMonth >= 7) {
        return $currentYear . '-' . ($currentYear + 1);
    } else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

// Validate face descriptor
function validateFaceDescriptor($descriptor) {
    if (empty($descriptor)) return false;
    
    $data = json_decode($descriptor, true);
    if (!is_array($data)) return false;
    
    // Face descriptor should be 128 dimensions
    return count($data) === 128;
}

// Calculate Euclidean distance
function euclideanDistance($desc1, $desc2) {
    $arr1 = json_decode($desc1, true);
    $arr2 = json_decode($desc2, true);
    
    if (!is_array($arr1) || !is_array($arr2)) return PHP_FLOAT_MAX;
    if (count($arr1) !== count($arr2)) return PHP_FLOAT_MAX;
    
    $sum = 0;
    for ($i = 0; $i < count($arr1); $i++) {
        $diff = $arr1[$i] - $arr2[$i];
        $sum += $diff * $diff;
    }
    
    return sqrt($sum);
}

// Calculate cosine similarity
function cosineSimilarity($desc1, $desc2) {
    $arr1 = json_decode($desc1, true);
    $arr2 = json_decode($desc2, true);
    
    if (!is_array($arr1) || !is_array($arr2)) return 0;
    if (count($arr1) !== count($arr2)) return 0;
    
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;
    
    for ($i = 0; $i < count($arr1); $i++) {
        $dotProduct += $arr1[$i] * $arr2[$i];
        $magnitude1 += $arr1[$i] * $arr1[$i];
        $magnitude2 += $arr2[$i] * $arr2[$i];
    }
    
    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);
    
    if ($magnitude1 == 0 || $magnitude2 == 0) return 0;
    
    return $dotProduct / ($magnitude1 * $magnitude2);
}

// Generate parent access code
function generateParentCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
}

// Format time
function formatTime($time) {
    if (empty($time) || $time === '00:00:00') return '-';
    $timestamp = strtotime($time);
    if ($timestamp === false) return '-';
    return date('h:i A', $timestamp);
}

// Get day name
function getDayName($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '-';
    return date('l', $timestamp);
}

// Check if weekend
function isWeekend($date) {
    if (empty($date)) return false;
    $timestamp = strtotime($date);
    if ($timestamp === false) return false;
    $day = date('N', $timestamp);
    return ($day == 6 || $day == 7); // Saturday or Sunday
}

// Get current semester
function getCurrentSemester() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM semesters WHERE is_current = 1 LIMIT 1");
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// Get department schedule
function getDepartmentSchedule($department) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM department_schedules WHERE department = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$department]);
        $schedule = $stmt->fetch();
        
        if ($schedule) {
            return $schedule;
        }
        
        // Return default system settings if no department-specific schedule
        return [
            'department' => $department,
            'start_time' => getSetting('attendance_start_time', '09:00:00'),
            'end_time' => getSetting('attendance_end_time', '17:00:00'),
            'late_threshold_minutes' => getSetting('late_threshold_minutes', 15),
            'is_default' => true
        ];
    } catch (Exception $e) {
        // Fallback to system defaults
        return [
            'department' => $department,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'late_threshold_minutes' => 15,
            'is_default' => true
        ];
    }
}

// Check if late based on department schedule
function isLateDepartment($timeIn, $department) {
    $schedule = getDepartmentSchedule($department);
    return strtotime($timeIn) > strtotime($schedule['start_time']);
}

// Calculate minutes late based on department schedule
function calculateMinutesLateDepartment($timeIn, $department) {
    $schedule = getDepartmentSchedule($department);
    $diff = strtotime($timeIn) - strtotime($schedule['start_time']);
    return max(0, floor($diff / 60));
}
?>

