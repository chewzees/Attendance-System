-- ============================================
-- College Face Recognition Attendance System
-- Complete Install SQL (schema + seed + demo)
-- Project: attendance2.5
-- ============================================
-- Shared-hosting safe: NO CREATE/DROP DATABASE.
--
-- HOW TO IMPORT (phpMyAdmin):
--   1. Create a database in cPanel (or use an existing one)
--   2. Click that database name in the left sidebar
--   3. Import tab → choose this file → Go
--
-- Then set config/database.php to match:
--   DB_USER, DB_PASS, DB_NAME (your real database name)
--
-- Default logins (password for all demo users: admin123)
--   Admin:     ADMIN001  / admin@college.edu
--   Professor: PROF001
--   Student:   STU2024001
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

START TRANSACTION;

-- ============================================
-- 1. User Roles
-- ============================================
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text,
  `permissions` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `user_roles` (`role_name`, `description`, `permissions`) VALUES
('Student', 'Regular student user', 'view_attendance,mark_attendance,apply_leave'),
('Professor', 'Faculty member', 'view_attendance,mark_attendance,manage_lectures,approve_leave,view_reports'),
('Admin', 'System administrator', 'all'),
('HOD', 'Head of Department', 'all_department');

-- ============================================
-- 2. Users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `department` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `face_descriptor` text,
  `face_descriptor_2` text,
  `face_descriptor_3` text,
  `phone` varchar(20) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `parent_code` varchar(10) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `department` (`department`),
  KEY `status` (`status`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Courses
-- ============================================
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `semester` int(11) NOT NULL,
  `credits` int(11) DEFAULT 3,
  `professor_id` int(11) DEFAULT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`),
  KEY `professor_id` (`professor_id`),
  KEY `department` (`department`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Semesters
-- ============================================
CREATE TABLE IF NOT EXISTS `semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `status` enum('upcoming','active','completed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Course Enrollment
-- ============================================
CREATE TABLE IF NOT EXISTS `course_enrollment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','dropped','completed') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_course` (`student_id`,`course_id`,`semester_id`),
  KEY `course_id` (`course_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `course_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollment_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollment_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Lectures
-- ============================================
CREATE TABLE IF NOT EXISTS `lectures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lecture_date` date NOT NULL,
  `lecture_time` time NOT NULL,
  `duration` int(11) DEFAULT 60,
  `room_number` varchar(50) DEFAULT NULL,
  `lecture_type` enum('theory','practical','tutorial','seminar') DEFAULT 'theory',
  `topic` varchar(255) DEFAULT NULL,
  `description` text,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `attendance_marked` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `lecture_date` (`lecture_date`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lectures_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Attendance (Daily)
-- ============================================
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'present',
  `is_late` tinyint(1) DEFAULT 0,
  `minutes_late` int(11) DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `remarks` text,
  `marked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_date` (`user_id`,`date`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `marked_by` (`marked_by`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. Lecture Attendance
-- ============================================
CREATE TABLE IF NOT EXISTS `lecture_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lecture_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'absent',
  `time_marked` timestamp NULL DEFAULT NULL,
  `is_late` tinyint(1) DEFAULT 0,
  `minutes_late` int(11) DEFAULT 0,
  `remarks` text,
  `marked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lecture_student` (`lecture_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `status` (`status`),
  KEY `marked_by` (`marked_by`),
  CONSTRAINT `lecture_attendance_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lecture_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lecture_attendance_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. Leave Requests
-- ============================================
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `leave_type` enum('sick','personal','emergency','medical','other') DEFAULT 'personal',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `supporting_document` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `review_comments` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. Notifications
-- ============================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `priority` (`priority`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. Attendance Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. System Settings
-- ============================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(50) DEFAULT 'string',
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('attendance_start_time', '09:00:00', 'time', 'Default attendance start time'),
('attendance_end_time', '17:00:00', 'time', 'Default attendance end time'),
('late_threshold_minutes', '15', 'integer', 'Minutes after which attendance is marked as late'),
('minimum_attendance_percentage', '75', 'integer', 'Minimum required attendance percentage'),
('warning_level_1', '85', 'integer', 'First warning level percentage'),
('warning_level_2', '80', 'integer', 'Second warning level percentage'),
('warning_level_3', '75', 'integer', 'Third warning level percentage'),
('face_recognition_threshold', '0.6', 'float', 'Face recognition similarity threshold'),
('face_recognition_model', 'ssd_mobilenetv1', 'string', 'Face detection model'),
('enable_email_notifications', '1', 'boolean', 'Enable email notifications'),
('enable_parent_notifications', '1', 'boolean', 'Enable parent email notifications'),
('system_name', 'College Face Recognition Attendance System', 'string', 'System name'),
('system_version', '2.5', 'string', 'System version');

-- ============================================
-- 13. Department Schedules
-- ============================================
CREATE TABLE IF NOT EXISTS `department_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(100) NOT NULL,
  `start_time` time NOT NULL DEFAULT '09:00:00',
  `end_time` time NOT NULL DEFAULT '17:00:00',
  `late_threshold_minutes` int(11) NOT NULL DEFAULT 15,
  `description` text,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department` (`department`),
  KEY `idx_department_active` (`department`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `department_schedules` (`department`, `start_time`, `end_time`, `late_threshold_minutes`, `description`) VALUES
('Computer Science', '09:00:00', '17:00:00', 15, 'Computer Science Department Schedule'),
('Electronics', '08:30:00', '16:30:00', 15, 'Electronics Department Schedule'),
('Mechanical', '09:00:00', '17:00:00', 15, 'Mechanical Engineering Department Schedule'),
('Civil', '08:00:00', '16:00:00', 15, 'Civil Engineering Department Schedule'),
('Electrical', '09:00:00', '17:00:00', 15, 'Electrical Engineering Department Schedule');

-- Extra indexes (create only if missing)
SET @db := DATABASE();
SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_attendance_user_date` ON `attendance` (`user_id`, `date`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='attendance' AND index_name='idx_attendance_user_date');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_lecture_attendance_lecture` ON `lecture_attendance` (`lecture_id`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='lecture_attendance' AND index_name='idx_lecture_attendance_lecture');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_lecture_attendance_student` ON `lecture_attendance` (`student_id`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='lecture_attendance' AND index_name='idx_lecture_attendance_student');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_notifications_user_read` ON `notifications` (`user_id`, `is_read`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='notifications' AND index_name='idx_notifications_user_read');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_leave_requests_status` ON `leave_requests` (`status`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='leave_requests' AND index_name='idx_leave_requests_status');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_courses_department` ON `courses` (`department`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='courses' AND index_name='idx_courses_department');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(COUNT(*)=0,
    'CREATE INDEX `idx_lectures_date` ON `lectures` (`lecture_date`)',
    'SELECT 1')
  FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='lectures' AND index_name='idx_lectures_date');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================
-- Seed users (password for ALL: admin123)
-- ============================================
INSERT IGNORE INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `status`) VALUES
('ADMIN001', 'System Administrator', 'admin@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 3, 'Administration', 'active');

INSERT IGNORE INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `phone`, `status`) VALUES
('PROF001', 'Dr. Sarah Johnson', 'sarah.johnson@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 2, 'Computer Science', '555-0101', 'active'),
('PROF002', 'Prof. Michael Chen', 'michael.chen@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 2, 'Electronics', '555-0102', 'active'),
('PROF003', 'Dr. Emily Davis', 'emily.davis@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 2, 'Mechanical', '555-0103', 'active'),
('PROF004', 'Prof. James Wilson', 'james.wilson@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 2, 'Civil', '555-0104', 'active'),
('PROF005', 'Dr. Lisa Anderson', 'lisa.anderson@college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 2, 'Electrical', '555-0105', 'active');

INSERT IGNORE INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `semester`, `phone`, `parent_phone`, `parent_code`, `status`) VALUES
('STU2024001', 'John Smith', 'john.smith@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 3, '555-1001', '555-2001', 'ABC123', 'active'),
('STU2024002', 'Emma Brown', 'emma.brown@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 3, '555-1002', '555-2002', 'DEF456', 'active'),
('STU2024003', 'Michael Johnson', 'michael.johnson@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 3, '555-1003', '555-2003', 'GHI789', 'active'),
('STU2024004', 'Sophia Williams', 'sophia.williams@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 3, '555-1004', '555-2004', 'JKL012', 'active'),
('STU2024005', 'David Miller', 'david.miller@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 3, '555-1005', '555-2005', 'MNO345', 'active'),
('STU2024006', 'Olivia Davis', 'olivia.davis@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 5, '555-1006', '555-2006', 'PQR678', 'active'),
('STU2024007', 'Daniel Garcia', 'daniel.garcia@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Computer Science', 5, '555-1007', '555-2007', 'STU901', 'active'),
('STU2024008', 'Jessica Martinez', 'jessica.martinez@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electronics', 3, '555-1008', '555-2008', 'VWX234', 'active'),
('STU2024009', 'Christopher Lee', 'christopher.lee@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electronics', 3, '555-1009', '555-2009', 'YZA567', 'active'),
('STU2024010', 'Isabella Rodriguez', 'isabella.rodriguez@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electronics', 3, '555-1010', '555-2010', 'BCD890', 'active'),
('STU2024011', 'Matthew Taylor', 'matthew.taylor@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electronics', 5, '555-1011', '555-2011', 'EFG123', 'active'),
('STU2024012', 'Ava Anderson', 'ava.anderson@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electronics', 5, '555-1012', '555-2012', 'HIJ456', 'active'),
('STU2024013', 'Andrew Thomas', 'andrew.thomas@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Mechanical', 3, '555-1013', '555-2013', 'KLM789', 'active'),
('STU2024014', 'Mia Jackson', 'mia.jackson@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Mechanical', 3, '555-1014', '555-2014', 'NOP012', 'active'),
('STU2024015', 'Joseph White', 'joseph.white@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Mechanical', 3, '555-1015', '555-2015', 'QRS345', 'active'),
('STU2024016', 'Charlotte Harris', 'charlotte.harris@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Mechanical', 5, '555-1016', '555-2016', 'TUV678', 'active'),
('STU2024017', 'Robert Martin', 'robert.martin@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Civil', 3, '555-1017', '555-2017', 'WXY901', 'active'),
('STU2024018', 'Amelia Thompson', 'amelia.thompson@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Civil', 3, '555-1018', '555-2018', 'ZAB234', 'active'),
('STU2024019', 'William Garcia', 'william.garcia@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Civil', 5, '555-1019', '555-2019', 'CDE567', 'active'),
('STU2024020', 'Harper Martinez', 'harper.martinez@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electrical', 3, '555-1020', '555-2020', 'FGH890', 'active'),
('STU2024021', 'Benjamin Robinson', 'benjamin.robinson@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electrical', 3, '555-1021', '555-2021', 'IJK123', 'active'),
('STU2024022', 'Evelyn Clark', 'evelyn.clark@student.college.edu', '$2y$10$w2iJFJWuTg/OejBNbJu9YOmIfDCgRE3cBXmgGf7MBTCVeHmWtfKOu', 1, 'Electrical', 5, '555-1022', '555-2022', 'LMN456', 'active');

-- ============================================
-- Semesters
-- ============================================
INSERT INTO `semesters` (`semester_name`, `start_date`, `end_date`, `is_current`, `status`)
SELECT 'Fall 2024', '2024-09-01', '2024-12-20', 1, 'active'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `semesters` WHERE semester_name = 'Fall 2024');
INSERT INTO `semesters` (`semester_name`, `start_date`, `end_date`, `is_current`, `status`)
SELECT 'Spring 2024', '2024-01-15', '2024-05-15', 0, 'completed'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `semesters` WHERE semester_name = 'Spring 2024');

-- ============================================
-- Courses
-- ============================================
INSERT IGNORE INTO `courses` (`course_code`, `course_name`, `department`, `semester`, `credits`, `professor_id`, `description`, `status`) VALUES
('CS301', 'Database Management Systems', 'Computer Science', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Introduction to database concepts and SQL', 'active'),
('CS302', 'Web Development', 'Computer Science', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Full-stack web development with modern frameworks', 'active'),
('CS501', 'Machine Learning', 'Computer Science', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Advanced machine learning algorithms', 'active'),
('CS502', 'Cloud Computing', 'Computer Science', 5, 3, (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1), 'Cloud architecture and deployment', 'active'),
('EC301', 'Digital Electronics', 'Electronics', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Digital circuit design and analysis', 'active'),
('EC302', 'Microcontrollers', 'Electronics', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Embedded systems programming', 'active'),
('EC501', 'VLSI Design', 'Electronics', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF002' LIMIT 1), 'Very Large Scale Integration design', 'active'),
('ME301', 'Thermodynamics', 'Mechanical', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Energy and heat transfer principles', 'active'),
('ME302', 'Machine Design', 'Mechanical', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Mechanical component design', 'active'),
('ME501', 'Robotics', 'Mechanical', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF003' LIMIT 1), 'Robotic systems and automation', 'active'),
('CE301', 'Structural Analysis', 'Civil', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Analysis of structures', 'active'),
('CE302', 'Concrete Technology', 'Civil', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Concrete materials and construction', 'active'),
('CE501', 'Bridge Engineering', 'Civil', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF004' LIMIT 1), 'Bridge design and construction', 'active'),
('EE301', 'Power Systems', 'Electrical', 3, 3, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Electrical power generation and distribution', 'active'),
('EE302', 'Control Systems', 'Electrical', 3, 4, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Control theory and applications', 'active'),
('EE501', 'Renewable Energy', 'Electrical', 5, 4, (SELECT id FROM users WHERE user_id = 'PROF005' LIMIT 1), 'Solar and wind energy systems', 'active');

-- ============================================
-- Enrollments (students → matching dept/semester courses)
-- ============================================
INSERT IGNORE INTO `course_enrollment` (`student_id`, `course_id`, `semester_id`, `enrollment_date`, `status`)
SELECT
    u.id,
    c.id,
    (SELECT id FROM semesters WHERE is_current = 1 LIMIT 1),
    '2024-09-01',
    'active'
FROM users u
JOIN courses c
  ON u.department = c.department
 AND u.semester = c.semester
WHERE u.role_id = 1
  AND u.status = 'active'
  AND c.status = 'active';

-- ============================================
-- Sample daily attendance (last 14 weekdays)
-- ============================================
INSERT IGNORE INTO `attendance` (`user_id`, `date`, `time_in`, `time_out`, `status`, `is_late`, `minutes_late`)
SELECT
    u.id,
    d.att_date,
    CASE
        WHEN MOD(u.id + DAY(d.att_date), 10) < 8 THEN '08:55:00'
        ELSE '09:18:00'
    END,
    CASE
        WHEN MOD(u.id + DAY(d.att_date), 10) < 8 THEN '17:00:00'
        ELSE '17:05:00'
    END,
    CASE
        WHEN MOD(u.id + DAY(d.att_date), 10) < 8 THEN 'present'
        ELSE 'late'
    END,
    CASE
        WHEN MOD(u.id + DAY(d.att_date), 10) < 8 THEN 0
        ELSE 1
    END,
    CASE
        WHEN MOD(u.id + DAY(d.att_date), 10) < 8 THEN 0
        ELSE 18
    END
FROM users u
CROSS JOIN (
    SELECT DATE_SUB(CURDATE(), INTERVAL n DAY) AS att_date
    FROM (
        SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
        UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
        UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
    ) nums
) d
WHERE u.role_id = 1
  AND u.status = 'active'
  AND DAYOFWEEK(d.att_date) BETWEEN 2 AND 6
  AND MOD(u.id + DAY(d.att_date), 11) <> 0;

-- ============================================
-- Sample leave requests
-- ============================================
INSERT IGNORE INTO `leave_requests` (`user_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `reviewed_by`, `review_date`, `review_comments`)
SELECT
    u.id,
    CASE MOD(u.id, 3) WHEN 0 THEN 'sick' WHEN 1 THEN 'personal' ELSE 'emergency' END,
    DATE_SUB(CURDATE(), INTERVAL 3 DAY),
    DATE_SUB(CURDATE(), INTERVAL 1 DAY),
    'Demo leave request for testing',
    CASE MOD(u.id, 3) WHEN 0 THEN 'pending' WHEN 1 THEN 'approved' ELSE 'rejected' END,
    CASE WHEN MOD(u.id, 3) = 0 THEN NULL ELSE (SELECT id FROM users WHERE user_id = 'PROF001' LIMIT 1) END,
    CASE WHEN MOD(u.id, 3) = 0 THEN NULL ELSE NOW() END,
    CASE WHEN MOD(u.id, 3) = 0 THEN NULL ELSE 'Reviewed (demo)' END
FROM users u
WHERE u.role_id = 1
  AND NOT EXISTS (SELECT 1 FROM `leave_requests` LIMIT 1)
LIMIT 8;

-- ============================================
-- Sample lectures + lecture attendance
-- ============================================
INSERT IGNORE INTO `lectures` (`course_id`, `lecture_date`, `lecture_time`, `duration`, `room_number`, `lecture_type`, `topic`, `status`, `attendance_marked`, `created_by`)
SELECT
    c.id,
    DATE_SUB(CURDATE(), INTERVAL MOD(c.id * 2, 10) DAY),
    '10:00:00',
    60,
    CONCAT('R', 100 + MOD(c.id, 20)),
    CASE MOD(c.id, 4)
        WHEN 0 THEN 'theory'
        WHEN 1 THEN 'practical'
        WHEN 2 THEN 'tutorial'
        ELSE 'seminar'
    END,
    CONCAT('Intro to ', c.course_name),
    CASE WHEN MOD(c.id, 3) = 0 THEN 'scheduled' ELSE 'completed' END,
    CASE WHEN MOD(c.id, 3) = 0 THEN 0 ELSE 1 END,
    c.professor_id
FROM courses c
WHERE NOT EXISTS (SELECT 1 FROM `lectures` LIMIT 1);

INSERT IGNORE INTO `lecture_attendance` (`lecture_id`, `student_id`, `status`, `time_marked`, `is_late`, `minutes_late`)
SELECT
    l.id,
    ce.student_id,
    CASE
        WHEN MOD(l.id + ce.student_id, 10) < 7 THEN 'present'
        WHEN MOD(l.id + ce.student_id, 10) < 9 THEN 'late'
        ELSE 'absent'
    END,
    CONCAT(l.lecture_date, ' ', l.lecture_time),
    CASE WHEN MOD(l.id + ce.student_id, 10) BETWEEN 7 AND 8 THEN 1 ELSE 0 END,
    CASE WHEN MOD(l.id + ce.student_id, 10) BETWEEN 7 AND 8 THEN 12 ELSE 0 END
FROM lectures l
JOIN course_enrollment ce ON ce.course_id = l.course_id AND ce.status = 'active'
WHERE l.status = 'completed';

-- ============================================
-- Sample notifications + logs
-- ============================================
INSERT IGNORE INTO `notifications` (`user_id`, `title`, `message`, `type`, `priority`, `is_read`)
SELECT
    u.id,
    'Welcome to Attendance System',
    CONCAT('Hello ', u.name, ', your account is ready.'),
    'info',
    'medium',
    0
FROM users u
WHERE u.role_id = 1
  AND NOT EXISTS (SELECT 1 FROM `notifications` WHERE title = 'Welcome to Attendance System' LIMIT 1)
LIMIT 15;

INSERT IGNORE INTO `attendance_logs` (`user_id`, `action`, `details`, `ip_address`, `user_agent`)
SELECT
    u.id,
    'user_login',
    CONCAT('Demo login for ', u.name),
    '127.0.0.1',
    'Demo Browser'
FROM users u
WHERE u.status = 'active'
  AND NOT EXISTS (SELECT 1 FROM `attendance_logs` WHERE details LIKE 'Demo login for %' LIMIT 1)
LIMIT 30;

COMMIT;

SELECT 'Merge install complete: attendance_system ready (existing data kept).' AS message;
SELECT COUNT(*) AS tables_created
FROM information_schema.tables
WHERE table_schema = 'attendance_system';
SELECT user_id, name, email, role_id FROM users ORDER BY role_id, user_id LIMIT 10;
