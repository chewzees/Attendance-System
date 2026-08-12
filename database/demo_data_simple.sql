-- ============================================
-- Demo Data for College Attendance System
-- Simple version - Run this after schema.sql
-- ============================================

USE `attendance_system`;

-- Clear existing demo data (optional - comment out if you want to keep existing data)
-- DELETE FROM attendance_logs;
-- DELETE FROM lecture_attendance;
-- DELETE FROM lectures;
-- DELETE FROM notifications;
-- DELETE FROM leave_requests;
-- DELETE FROM attendance;
-- DELETE FROM course_enrollment;
-- DELETE FROM courses;
-- DELETE FROM users WHERE role_id IN (1, 2);

-- ============================================
-- Insert Demo Semesters
-- ============================================
INSERT IGNORE INTO `semesters` (`semester_name`, `start_date`, `end_date`, `is_current`, `status`) VALUES
('Fall 2024', '2024-09-01', '2024-12-20', 1, 'active'),
('Spring 2024', '2024-01-15', '2024-05-15', 0, 'completed');

-- ============================================
-- Insert Demo Professors (Role ID = 2)
-- ============================================
INSERT IGNORE INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `phone`, `status`) VALUES
('PROF001', 'Dr. Sarah Johnson', 'sarah.johnson@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Computer Science', '555-0101', 'active'),
('PROF002', 'Prof. Michael Chen', 'michael.chen@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Electronics', '555-0102', 'active'),
('PROF003', 'Dr. Emily Davis', 'emily.davis@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Mechanical', '555-0103', 'active'),
('PROF004', 'Prof. James Wilson', 'james.wilson@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Civil', '555-0104', 'active'),
('PROF005', 'Dr. Lisa Anderson', 'lisa.anderson@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Electrical', '555-0105', 'active');

-- ============================================
-- Insert Demo Students (Role ID = 1)
-- ============================================
-- Computer Science Students
INSERT IGNORE INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `semester`, `phone`, `parent_phone`, `parent_code`, `status`) VALUES
('STU2024001', 'John Smith', 'john.smith@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 3, '555-1001', '555-2001', 'ABC123', 'active'),
('STU2024002', 'Emma Brown', 'emma.brown@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 3, '555-1002', '555-2002', 'DEF456', 'active'),
('STU2024003', 'Michael Johnson', 'michael.johnson@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 3, '555-1003', '555-2003', 'GHI789', 'active'),
('STU2024004', 'Sophia Williams', 'sophia.williams@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 3, '555-1004', '555-2004', 'JKL012', 'active'),
('STU2024005', 'David Miller', 'david.miller@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 3, '555-1005', '555-2005', 'MNO345', 'active'),
('STU2024006', 'Olivia Davis', 'olivia.davis@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 5, '555-1006', '555-2006', 'PQR678', 'active'),
('STU2024007', 'Daniel Garcia', 'daniel.garcia@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', 5, '555-1007', '555-2007', 'STU901', 'active'),

-- Electronics Students
('STU2024008', 'Jessica Martinez', 'jessica.martinez@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electronics', 3, '555-1008', '555-2008', 'VWX234', 'active'),
('STU2024009', 'Christopher Lee', 'christopher.lee@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electronics', 3, '555-1009', '555-2009', 'YZA567', 'active'),
('STU2024010', 'Isabella Rodriguez', 'isabella.rodriguez@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electronics', 3, '555-1010', '555-2010', 'BCD890', 'active'),
('STU2024011', 'Matthew Taylor', 'matthew.taylor@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electronics', 5, '555-1011', '555-2011', 'EFG123', 'active'),
('STU2024012', 'Ava Anderson', 'ava.anderson@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electronics', 5, '555-1012', '555-2012', 'HIJ456', 'active'),

-- Mechanical Students
('STU2024013', 'Andrew Thomas', 'andrew.thomas@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Mechanical', 3, '555-1013', '555-2013', 'KLM789', 'active'),
('STU2024014', 'Mia Jackson', 'mia.jackson@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Mechanical', 3, '555-1014', '555-2014', 'NOP012', 'active'),
('STU2024015', 'Joseph White', 'joseph.white@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Mechanical', 3, '555-1015', '555-2015', 'QRS345', 'active'),
('STU2024016', 'Charlotte Harris', 'charlotte.harris@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Mechanical', 5, '555-1016', '555-2016', 'TUV678', 'active'),

-- Civil Students
('STU2024017', 'Robert Martin', 'robert.martin@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Civil', 3, '555-1017', '555-2017', 'WXY901', 'active'),
('STU2024018', 'Amelia Thompson', 'amelia.thompson@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Civil', 3, '555-1018', '555-2018', 'ZAB234', 'active'),
('STU2024019', 'William Garcia', 'william.garcia@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Civil', 5, '555-1019', '555-2019', 'CDE567', 'active'),

-- Electrical Students
('STU2024020', 'Harper Martinez', 'harper.martinez@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electrical', 3, '555-1020', '555-2020', 'FGH890', 'active'),
('STU2024021', 'Benjamin Robinson', 'benjamin.robinson@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electrical', 3, '555-1021', '555-2021', 'IJK123', 'active'),
('STU2024022', 'Evelyn Clark', 'evelyn.clark@student.college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Electrical', 5, '555-1022', '555-2022', 'LMN456', 'active');

-- ============================================
-- Insert Demo Courses
-- ============================================
INSERT IGNORE INTO `courses` (`course_code`, `course_name`, `department`, `semester`, `credits`, `professor_id`, `description`, `status`) VALUES
-- Computer Science Courses
('CS301', 'Database Management Systems', 'Computer Science', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Introduction to database concepts and SQL', 'active'),
('CS302', 'Web Development', 'Computer Science', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Full-stack web development with modern frameworks', 'active'),
('CS501', 'Machine Learning', 'Computer Science', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Advanced machine learning algorithms', 'active'),
('CS502', 'Cloud Computing', 'Computer Science', 5, 3, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Cloud architecture and deployment', 'active'),

-- Electronics Courses
('EC301', 'Digital Electronics', 'Electronics', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Digital circuit design and analysis', 'active'),
('EC302', 'Microcontrollers', 'Electronics', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Embedded systems programming', 'active'),
('EC501', 'VLSI Design', 'Electronics', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Very Large Scale Integration design', 'active'),

-- Mechanical Courses
('ME301', 'Thermodynamics', 'Mechanical', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Energy and heat transfer principles', 'active'),
('ME302', 'Machine Design', 'Mechanical', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Mechanical component design', 'active'),
('ME501', 'Robotics', 'Mechanical', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Robotic systems and automation', 'active'),

-- Civil Courses
('CE301', 'Structural Analysis', 'Civil', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Analysis of structures', 'active'),
('CE302', 'Concrete Technology', 'Civil', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Concrete materials and construction', 'active'),
('CE501', 'Bridge Engineering', 'Civil', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Bridge design and construction', 'active'),

-- Electrical Courses
('EE301', 'Power Systems', 'Electrical', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Electrical power generation and distribution', 'active'),
('EE302', 'Control Systems', 'Electrical', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Control theory and applications', 'active'),
('EE501', 'Renewable Energy', 'Electrical', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Solar and wind energy systems', 'active');

-- ============================================
-- Insert Demo Course Enrollments
-- ============================================
INSERT IGNORE INTO `course_enrollment` (`student_id`, `course_id`, `semester_id`, `enrollment_date`, `status`)
SELECT 
    u.id as student_id,
    c.id as course_id,
    (SELECT id FROM semesters WHERE is_current = 1 LIMIT 1) as semester_id,
    '2024-09-01' as enrollment_date,
    'active' as status
FROM users u
CROSS JOIN courses c
WHERE u.role_id = 1 
AND u.department = c.department
AND (
    (u.semester = 3 AND c.semester = 3) OR
    (u.semester = 5 AND c.semester = 5)
)
LIMIT 50;

-- ============================================
-- Insert Demo Attendance Records (Last 30 Days)
-- ============================================
-- Generate attendance for weekdays over the last 30 days
INSERT IGNORE INTO `attendance` (`user_id`, `date`, `time_in`, `status`, `is_late`, `minutes_late`)
SELECT 
    u.id,
    dates.date,
    CASE 
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 7 THEN '09:00:00'
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 9 THEN '09:20:00'
        ELSE NULL
    END as time_in,
    CASE 
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 7 THEN 'present'
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 9 THEN 'late'
        ELSE 'absent'
    END as status,
    CASE 
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 7 THEN 0
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 9 THEN 1
        ELSE 0
    END as is_late,
    CASE 
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 7 THEN 0
        WHEN MOD((u.id * 7 + DAY(dates.date)), 10) < 9 THEN MOD((u.id * 3 + DAY(dates.date)), 30) + 1
        ELSE 0
    END as minutes_late
FROM users u
CROSS JOIN (
    SELECT DATE_SUB(CURDATE(), INTERVAL n DAY) as date
    FROM (
        SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
        SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
        SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
    ) numbers
) dates
WHERE u.role_id = 1 
AND u.status = 'active'
AND DAYOFWEEK(dates.date) BETWEEN 2 AND 6  -- Monday to Friday
AND MOD((u.id * 3 + DAY(dates.date)), 10) < 9  -- 90% attendance rate
LIMIT 450;

-- ============================================
-- Insert Demo Leave Requests
-- ============================================
INSERT IGNORE INTO `leave_requests` (`user_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `approved_by`)
SELECT 
    u.id,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'sick'
        WHEN 1 THEN 'personal'
        ELSE 'emergency'
    END,
    DATE_SUB(CURDATE(), INTERVAL MOD(u.id, 10) DAY),
    DATE_SUB(CURDATE(), INTERVAL MOD(u.id, 5) DAY),
    'Demo leave request for testing purposes',
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'pending'
        WHEN 1 THEN 'approved'
        ELSE 'rejected'
    END,
    CASE 
        WHEN MOD(u.id, 2) = 0 THEN (SELECT id FROM users WHERE role_id = 2 LIMIT 1)
        ELSE NULL
    END
FROM users u
WHERE u.role_id = 1
LIMIT 8;

-- ============================================
-- Insert Demo Notifications
-- ============================================
INSERT IGNORE INTO `notifications` (`user_id`, `title`, `message`, `type`, `priority`, `is_read`)
SELECT 
    u.id,
    CASE MOD(u.id, 4)
        WHEN 0 THEN 'Attendance Reminder'
        WHEN 1 THEN 'Leave Request Update'
        WHEN 2 THEN 'Low Attendance Warning'
        ELSE 'System Notification'
    END,
    CASE MOD(u.id, 4)
        WHEN 0 THEN 'Don''t forget to mark your attendance today!'
        WHEN 1 THEN 'Your leave request has been reviewed'
        WHEN 2 THEN 'Your attendance is below the required threshold'
        ELSE 'New system update available'
    END,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'info'
        WHEN 1 THEN 'warning'
        ELSE 'success'
    END,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'low'
        WHEN 1 THEN 'medium'
        ELSE 'high'
    END,
    CASE WHEN MOD(u.id, 3) = 0 THEN 1 ELSE 0 END
FROM users u
WHERE u.role_id = 1
LIMIT 25;

-- ============================================
-- Insert Demo Attendance Logs
-- ============================================
INSERT IGNORE INTO `attendance_logs` (`user_id`, `action`, `details`, `ip_address`, `user_agent`)
SELECT 
    u.id,
    CASE MOD(u.id, 5)
        WHEN 0 THEN 'attendance_marked'
        WHEN 1 THEN 'user_login'
        WHEN 2 THEN 'leave_applied'
        WHEN 3 THEN 'profile_updated'
        ELSE 'attendance_viewed'
    END,
    CONCAT('Demo log entry for ', u.name),
    CONCAT('192.168.1.', MOD(u.id, 255)),
    'Mozilla/5.0 (Demo Browser)'
FROM users u
WHERE u.role_id = 1
LIMIT 100;

-- ============================================
-- Insert Demo Lectures
-- ============================================
INSERT IGNORE INTO `lectures` (`course_id`, `lecture_date`, `lecture_time`, `duration`, `room_number`, `lecture_type`, `topic`, `status`, `professor_id`)
SELECT 
    c.id,
    DATE_SUB(CURDATE(), INTERVAL MOD(c.id * 3, 20) DAY),
    ADDTIME('10:00:00', SEC_TO_TIME(MOD(c.id * 60, 14400))),
    60 + MOD(c.id * 7, 60),
    CONCAT('Room ', 100 + MOD(c.id, 20)),
    CASE MOD(c.id, 3)
        WHEN 0 THEN 'theory'
        WHEN 1 THEN 'lab'
        ELSE 'tutorial'
    END,
    CONCAT('Lecture on ', c.course_name),
    CASE WHEN MOD(c.id, 4) = 0 THEN 'scheduled' ELSE 'completed' END,
    c.professor_id
FROM courses c
LIMIT 40;

-- ============================================
-- Insert Demo Lecture Attendance
-- ============================================
INSERT IGNORE INTO `lecture_attendance` (`lecture_id`, `student_id`, `status`, `time_marked`, `is_late`, `minutes_late`)
SELECT 
    l.id,
    ce.student_id,
    CASE 
        WHEN MOD((l.id * 7 + ce.student_id), 10) < 7 THEN 'present'
        WHEN MOD((l.id * 7 + ce.student_id), 10) < 9 THEN 'late'
        ELSE 'absent'
    END,
    DATE_SUB(NOW(), INTERVAL MOD(l.id, 7) DAY),
    CASE WHEN MOD((l.id * 7 + ce.student_id), 10) < 7 THEN 0 ELSE 1 END,
    CASE WHEN MOD((l.id * 7 + ce.student_id), 10) < 7 THEN 0 ELSE MOD((l.id * 3 + ce.student_id), 30) END
FROM lectures l
JOIN course_enrollment ce ON l.course_id = ce.course_id
WHERE l.status = 'completed'
LIMIT 200;

-- ============================================
-- Update Some Attendance Records with Time Out
-- ============================================
UPDATE attendance 
SET time_out = ADDTIME(time_in, INTERVAL 8 HOUR)
WHERE status IN ('present', 'late')
AND time_in IS NOT NULL
AND MOD(id, 3) != 0;

-- ============================================
-- Summary Query
-- ============================================
SELECT 
    'Demo Data Inserted Successfully!' as message,
    (SELECT COUNT(*) FROM users WHERE role_id = 1) as total_students,
    (SELECT COUNT(*) FROM users WHERE role_id = 2) as total_professors,
    (SELECT COUNT(*) FROM courses) as total_courses,
    (SELECT COUNT(*) FROM attendance) as total_attendance_records,
    (SELECT COUNT(*) FROM leave_requests) as total_leave_requests,
    (SELECT COUNT(*) FROM notifications) as total_notifications;

