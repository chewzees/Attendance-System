<?php
/**
 * Dashboard Statistics API
 * Endpoint: GET /api/stats.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiLogin();

require_once '../config/database.php';
require_once '../config/helpers.php';

try {
    $db = getDB();
    $today = date('Y-m-d');
    
    // Get user ID from query parameter (optional)
    $userId = $_GET['user_id'] ?? null;
    
    if ($userId) {
        // Get specific user stats
        $stmt = $db->prepare("SELECT id, user_id, name, email, department, course, semester, role_id FROM users WHERE user_id = ? OR id = ?");
        $stmt->execute([$userId, $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('User not found');
        }
        
        // User attendance stats
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
            FROM attendance 
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $attendance = $stmt->fetch();
        
        // Calculate percentage
        $totalPresent = $attendance['present'] + $attendance['late'];
        $percentage = calculateAttendancePercentage($totalPresent, $attendance['total_days']);
        
        // Get recent attendance
        $stmt = $db->prepare("
            SELECT date, time_in, time_out, status, is_late, minutes_late 
            FROM attendance 
            WHERE user_id = ? 
            ORDER BY date DESC 
            LIMIT 10
        ");
        $stmt->execute([$user['id']]);
        $recentAttendance = $stmt->fetchAll();
        
        // Get enrolled courses with attendance stats
        $stmt = $db->prepare("
            SELECT c.course_code, c.course_name, c.credits,
                   COUNT(la.id) as total_lectures,
                   SUM(CASE WHEN la.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_lectures
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.id
            LEFT JOIN lectures l ON c.id = l.course_id AND l.status = 'completed'
            LEFT JOIN lecture_attendance la ON l.id = la.lecture_id AND la.student_id = ?
            WHERE ce.student_id = ? AND ce.status = 'active'
            GROUP BY c.id
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $courses = $stmt->fetchAll();
        
        // Calculate attendance percentages
        foreach ($courses as &$course) {
            $course['attendance_percentage'] = calculateAttendancePercentage(
                $course['attended_lectures'],
                $course['total_lectures']
            );
        }
        
        // Get pending leave requests
        $stmt = $db->prepare("
            SELECT id, leave_type, start_date, end_date, status, reason
            FROM leave_requests 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $leaveRequests = $stmt->fetchAll();
        
        // Get unread notifications
        $stmt = $db->prepare("
            SELECT COUNT(*) as unread_count
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user['id']]);
        $notifications = $stmt->fetch();
        
        successResponse('User statistics retrieved', [
            'user' => $user,
            'attendance' => [
                'total_days' => $attendance['total_days'],
                'present' => $attendance['present'],
                'late' => $attendance['late'],
                'absent' => $attendance['absent'],
                'excused' => $attendance['excused'],
                'percentage' => $percentage,
                'status_color' => getAttendanceColor($percentage),
                'warning_level' => getWarningLevel($percentage)
            ],
            'recent_attendance' => $recentAttendance,
            'courses' => $courses,
            'leave_requests' => $leaveRequests,
            'unread_notifications' => $notifications['unread_count']
        ]);
    } else {
        // Get system-wide stats
        
        // Total users by role
        $stmt = $db->query("
            SELECT ur.role_name, COUNT(u.id) as count
            FROM users u
            JOIN user_roles ur ON u.role_id = ur.id
            WHERE u.status = 'active'
            GROUP BY ur.role_name
        ");
        $usersByRole = $stmt->fetchAll();
        
        // Today's attendance
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_marked,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
            FROM attendance 
            WHERE date = ?
        ");
        $stmt->execute([$today]);
        $todayAttendance = $stmt->fetch();
        
        // Total students
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role_id = 1 AND status = 'active'");
        $totalStudents = $stmt->fetch()['total'];
        
        // Active courses
        $stmt = $db->query("SELECT COUNT(*) as total FROM courses WHERE status = 'active'");
        $activeCourses = $stmt->fetch()['total'];
        
        // Pending leave requests
        $stmt = $db->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
        $pendingLeaves = $stmt->fetch()['total'];
        
        // Attendance defaulters (below 75%)
        $stmt = $db->query("
            SELECT * FROM (
                SELECT u.id, u.user_id, u.name, u.department, u.course,
                       COUNT(a.id) as total_days,
                       SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days
                FROM users u
                LEFT JOIN attendance a ON u.id = a.user_id
                WHERE u.role_id = 1 AND u.status = 'active'
                GROUP BY u.id
            ) AS student_stats
            WHERE total_days = 0 OR (present_days / total_days * 100) < 75
            ORDER BY CASE WHEN total_days = 0 THEN 0 ELSE (present_days / total_days * 100) END ASC
            LIMIT 10
        ");
        $defaulters = $stmt->fetchAll();
        
        // Calculate percentages for defaulters
        foreach ($defaulters as &$defaulter) {
            $defaulter['percentage'] = calculateAttendancePercentage(
                $defaulter['present_days'],
                $defaulter['total_days']
            );
        }
        
        // Recent activities
        $stmt = $db->query("
            SELECT al.action, al.details, al.created_at, u.name as user_name
            FROM attendance_logs al
            JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 20
        ");
        $recentActivities = $stmt->fetchAll();
        
        // Courses with student count
        $stmt = $db->query("
            SELECT c.course_code, c.course_name, c.department,
                   COUNT(ce.id) as student_count
            FROM courses c
            LEFT JOIN course_enrollment ce ON c.id = ce.course_id AND ce.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY student_count DESC
            LIMIT 10
        ");
        $topCourses = $stmt->fetchAll();
        
        // Weekly attendance trend (last 7 days)
        $stmt = $db->query("
            SELECT 
                date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
            FROM attendance
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY date
            ORDER BY date ASC
        ");
        $weeklyTrend = $stmt->fetchAll();
        
        // Monthly attendance trend (last 30 days)
        $stmt = $db->query("
            SELECT 
                DATE_FORMAT(date, '%Y-%m-%d') as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
            FROM attendance
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY date
            ORDER BY date ASC
        ");
        $monthlyTrend = $stmt->fetchAll();
        
        // Department-wise attendance stats
        $stmt = $db->query("
            SELECT 
                u.department,
                COUNT(a.id) as total_records,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                COUNT(DISTINCT u.id) as total_students
            FROM users u
            LEFT JOIN attendance a ON u.id = a.user_id
            WHERE u.role_id = 1 AND u.status = 'active'
            GROUP BY u.department
            ORDER BY total_records DESC
        ");
        $departmentStats = $stmt->fetchAll();
        
        // Overall attendance status breakdown
        $stmt = $db->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM attendance
            GROUP BY status
        ");
        $statusBreakdown = $stmt->fetchAll();
        
        successResponse('System statistics retrieved', [
            'overview' => [
                'total_students' => $totalStudents,
                'active_courses' => $activeCourses,
                'pending_leaves' => $pendingLeaves,
                'today_attendance' => $todayAttendance['total_marked']
            ],
            'users_by_role' => $usersByRole,
            'today_attendance' => $todayAttendance,
            'defaulters' => $defaulters,
            'recent_activities' => $recentActivities,
            'top_courses' => $topCourses,
            'weekly_trend' => $weeklyTrend,
            'monthly_trend' => $monthlyTrend,
            'department_stats' => $departmentStats,
            'status_breakdown' => $statusBreakdown
        ]);
    }
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

