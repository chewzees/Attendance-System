-- One-click import for the Attendance System
-- WARNING: Back up any existing `attendance_system` database before importing.
-- To import: phpMyAdmin → Import, or from command line:
--   mysql -u root -p < C:\xamppp\htdocs\attendance2\database\one-click-import-attendance_system.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: attendance_system
CREATE DATABASE IF NOT EXISTS `attendance_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `attendance_system`;

-- ============================================
-- Table 1: User Roles
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

-- Insert default roles
INSERT INTO `user_roles` (`role_name`, `description`, `permissions`) VALUES
('Student', 'Regular student user', 'view_attendance,mark_attendance,apply_leave'),
('Professor', 'Faculty member', 'view_attendance,mark_attendance,manage_lectures,approve_leave,view_reports'),
('Admin', 'System administrator', 'all'),
('HOD', 'Head of Department', 'all_department');

-- ============================================
-- Table 2: Users (Students, Professors, Admins)
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
-- Table 3: Courses
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
-- Table 4: Semesters
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
-- Table 5: Course Enrollment
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
-- Table 6: Lectures
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
-- Table 7: Attendance (Daily)
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
-- Table 8: Lecture Attendance (Per-Lecture)
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
-- Table 9: Leave Requests
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
-- Table 10: Notifications
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
-- Table 11: Attendance Logs (Audit Trail)
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
-- Table 12: System Settings
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

-- Insert default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
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
('system_version', '2.0', 'string', 'System version');

-- ============================================
-- Table 13: Department Schedules
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
  UNIQUE KEY `department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default department schedules
INSERT INTO `department_schedules` (`department`, `start_time`, `end_time`, `late_threshold_minutes`, `description`) VALUES
('Computer Science', '09:00:00', '17:00:00', 15, 'Computer Science Department Schedule'),
('Electronics', '08:30:00', '16:30:00', 15, 'Electronics Department Schedule'),
('Mechanical', '09:00:00', '17:00:00', 15, 'Mechanical Engineering Department Schedule'),
('Civil', '08:00:00', '16:00:00', 15, 'Civil Engineering Department Schedule'),
('Electrical', '09:00:00', '17:00:00', 15, 'Electrical Engineering Department Schedule');

-- Add index for department schedules
CREATE INDEX idx_department_active ON department_schedules(department, is_active);

-- ============================================
-- Create Indexes for Performance
-- ============================================
CREATE INDEX idx_attendance_user_date ON attendance(user_id, date);
CREATE INDEX idx_lecture_attendance_lecture ON lecture_attendance(lecture_id);
CREATE INDEX idx_lecture_attendance_student ON lecture_attendance(student_id);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_leave_requests_status ON leave_requests(status);
CREATE INDEX idx_courses_department ON courses(department);
CREATE INDEX idx_lectures_date ON lectures(lecture_date);

-- ============================================
-- Insert Sample Data (Admin User)
-- ============================================
-- Password: admin123 (hashed with password_hash())
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role_id`, `department`, `status`) VALUES
('ADMIN001', 'System Administrator', 'admin@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Administration', 'active');

COMMIT;

-- End of one-click schema
