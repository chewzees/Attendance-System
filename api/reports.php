<?php
/**
 * Reports and Analytics API
 * Endpoint: GET /api/reports.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiRole(['admin', 'hod', 'professor']);

require_once '../config/database.php';
require_once '../config/helpers.php';

try {
    $db = getDB();
    
    $reportType = $_GET['type'] ?? 'summary';
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-t');
    $department = $_GET['department'] ?? null;
    $courseId = $_GET['course_id'] ?? null;
    
    switch ($reportType) {
        case 'summary':
            // Overall attendance summary
            $query = "
                SELECT 
                    COUNT(DISTINCT a.user_id) as total_students,
                    COUNT(DISTINCT a.date) as total_days,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                WHERE a.date BETWEEN ? AND ?
            ";
            $params = [$startDate, $endDate];
            
            if ($department) {
                $query .= " AND u.department = ?";
                $params[] = $department;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $summary = $stmt->fetch();
            
            $totalPresent = $summary['present_count'] + $summary['late_count'];
            $summary['attendance_percentage'] = calculateAttendancePercentage(
                $totalPresent,
                $summary['total_records']
            );
            
            successResponse('Summary report generated', $summary);
            break;
            
        case 'student':
            // Individual student report
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                errorResponse('User ID is required for student report');
            }
            
            // Get student info
            $stmt = $db->prepare("SELECT id, user_id, name, email, department, course, semester FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$userId, $userId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                errorResponse('Student not found');
            }
            
            // Attendance records
            $stmt = $db->prepare("
                SELECT * FROM attendance 
                WHERE user_id = ? AND date BETWEEN ? AND ?
                ORDER BY date DESC
            ");
            $stmt->execute([$student['id'], $startDate, $endDate]);
            $records = $stmt->fetchAll();
            
            // Statistics
            $stats = [
                'total' => count($records),
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'excused' => 0
            ];
            
            foreach ($records as $record) {
                $stats[$record['status']]++;
            }
            
            $totalPresent = $stats['present'] + $stats['late'];
            $stats['percentage'] = calculateAttendancePercentage($totalPresent, $stats['total']);
            
            // Course-wise attendance
            $stmt = $db->prepare("
                SELECT c.course_code, c.course_name,
                       COUNT(la.id) as total_lectures,
                       SUM(CASE WHEN la.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended
                FROM course_enrollment ce
                JOIN courses c ON ce.course_id = c.id
                LEFT JOIN lectures l ON c.id = l.course_id
                LEFT JOIN lecture_attendance la ON l.id = la.lecture_id AND la.student_id = ?
                WHERE ce.student_id = ? AND ce.status = 'active'
                GROUP BY c.id
            ");
            $stmt->execute([$student['id'], $student['id']]);
            $courseStats = $stmt->fetchAll();
            
            foreach ($courseStats as &$course) {
                $course['percentage'] = calculateAttendancePercentage(
                    $course['attended'],
                    $course['total_lectures']
                );
            }
            
            successResponse('Student report generated', [
                'student' => $student,
                'statistics' => $stats,
                'records' => $records,
                'course_wise' => $courseStats
            ]);
            break;
            
        case 'defaulters':
            // Students with low attendance
            $threshold = (int)($_GET['threshold'] ?? 75);
            
            $query = "
                SELECT * FROM (
                    SELECT u.id, u.user_id, u.name, u.email, u.department, u.course, u.semester,
                           COUNT(a.id) as total_days,
                           SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days
                    FROM users u
                    LEFT JOIN attendance a ON u.id = a.user_id AND a.date BETWEEN ? AND ?
                    WHERE u.role_id = 1 AND u.status = 'active'
            ";
            $params = [$startDate, $endDate];
            
            if ($department) {
                $query .= " AND u.department = ?";
                $params[] = $department;
            }
            
            $query .= "
                    GROUP BY u.id
                ) AS student_stats
                WHERE total_days = 0 OR (present_days / total_days * 100) < ?
                ORDER BY CASE WHEN total_days = 0 THEN 0 ELSE (present_days / total_days * 100) END ASC
            ";
            $params[] = $threshold;
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $defaulters = $stmt->fetchAll();
            
            foreach ($defaulters as &$defaulter) {
                $defaulter['percentage'] = calculateAttendancePercentage(
                    $defaulter['present_days'],
                    $defaulter['total_days']
                );
                $defaulter['warning_level'] = getWarningLevel($defaulter['percentage']);
            }
            
            successResponse('Defaulters report generated', $defaulters);
            break;
            
        case 'course':
            // Course-wise attendance report
            if (!$courseId) {
                errorResponse('Course ID is required for course report');
            }
            
            // Get course info
            $stmt = $db->prepare("
                SELECT c.*, u.name as professor_name
                FROM courses c
                LEFT JOIN users u ON c.professor_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
            
            if (!$course) {
                errorResponse('Course not found');
            }
            
            // Get enrolled students with attendance
            $stmt = $db->prepare("
                SELECT u.user_id, u.name, u.email,
                       COUNT(la.id) as total_lectures,
                       SUM(CASE WHEN la.status = 'present' THEN 1 ELSE 0 END) as present,
                       SUM(CASE WHEN la.status = 'late' THEN 1 ELSE 0 END) as late,
                       SUM(CASE WHEN la.status = 'absent' THEN 1 ELSE 0 END) as absent
                FROM course_enrollment ce
                JOIN users u ON ce.student_id = u.id
                LEFT JOIN lectures l ON ce.course_id = l.course_id
                LEFT JOIN lecture_attendance la ON l.id = la.lecture_id AND la.student_id = u.id
                WHERE ce.course_id = ? AND ce.status = 'active'
                GROUP BY u.id
                ORDER BY u.name
            ");
            $stmt->execute([$courseId]);
            $students = $stmt->fetchAll();
            
            foreach ($students as &$student) {
                $totalPresent = $student['present'] + $student['late'];
                $student['percentage'] = calculateAttendancePercentage(
                    $totalPresent,
                    $student['total_lectures']
                );
            }
            
            successResponse('Course report generated', [
                'course' => $course,
                'students' => $students
            ]);
            break;
            
        case 'department':
            // Department-wise summary
            $query = "
                SELECT u.department,
                       COUNT(DISTINCT u.id) as total_students,
                       COUNT(a.id) as total_attendance,
                       SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as present_count
                FROM users u
                LEFT JOIN attendance a ON u.id = a.user_id AND a.date BETWEEN ? AND ?
                WHERE u.role_id = 1 AND u.status = 'active'
                GROUP BY u.department
                ORDER BY u.department
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $departments = $stmt->fetchAll();
            
            foreach ($departments as &$dept) {
                $dept['percentage'] = calculateAttendancePercentage(
                    $dept['present_count'],
                    $dept['total_attendance']
                );
            }
            
            successResponse('Department report generated', $departments);
            break;
            
        case 'daily':
            // Daily attendance report
            $date = $_GET['date'] ?? date('Y-m-d');
            
            $query = "
                SELECT a.*, u.user_id, u.name, u.department, u.course
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                WHERE a.date = ?
            ";
            $params = [$date];
            
            if ($department) {
                $query .= " AND u.department = ?";
                $params[] = $department;
            }
            
            $query .= " ORDER BY a.time_in";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            // Summary for the day
            $summary = [
                'total' => count($records),
                'present' => 0,
                'late' => 0,
                'absent' => 0
            ];
            
            foreach ($records as $record) {
                $summary[$record['status']]++;
            }
            
            successResponse('Daily report generated', [
                'date' => $date,
                'summary' => $summary,
                'records' => $records
            ]);
            break;
            
        case 'export':
            // Export data as CSV
            $exportType = $_GET['export_type'] ?? 'attendance';
            
            if ($exportType === 'attendance') {
                $query = "
                    SELECT a.date, a.time_in, a.time_out, a.status, a.is_late, a.minutes_late,
                           u.user_id, u.name, u.email, u.department, u.course, u.semester
                    FROM attendance a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.date BETWEEN ? AND ?
                    ORDER BY a.date DESC, a.time_in
                ";
                
                $stmt = $db->prepare($query);
                $stmt->execute([$startDate, $endDate]);
                $data = $stmt->fetchAll();
                
                successResponse('Export data generated', [
                    'type' => 'attendance',
                    'records' => $data,
                    'count' => count($data)
                ]);
            }
            break;
            
        default:
            errorResponse('Invalid report type');
    }
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

