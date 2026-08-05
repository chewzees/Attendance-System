-- ============================================
-- Department-Specific Attendance Times
-- Update Script v2.2
-- ============================================

USE `attendance_system`;

-- Create table for department schedules
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

-- Insert default schedules for common departments
INSERT INTO `department_schedules` (`department`, `start_time`, `end_time`, `late_threshold_minutes`, `description`) VALUES
('Computer Science', '09:00:00', '17:00:00', 15, 'Computer Science Department Schedule'),
('Electronics', '08:30:00', '16:30:00', 15, 'Electronics Department Schedule'),
('Mechanical', '09:00:00', '17:00:00', 15, 'Mechanical Department Schedule'),
('Civil', '08:00:00', '16:00:00', 15, 'Civil Department Schedule'),
('Electrical', '09:00:00', '17:00:00', 15, 'Electrical Department Schedule')
ON DUPLICATE KEY UPDATE 
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  late_threshold_minutes = VALUES(late_threshold_minutes);

-- Add index for faster lookups
CREATE INDEX idx_department_active ON department_schedules(department, is_active);

-- Verify the changes
SELECT 'Department schedules table created!' as Status;
SELECT * FROM department_schedules;

COMMIT;

-- ============================================
-- How to use:
-- ============================================
-- 1. Run this script in phpMyAdmin
-- 2. Go to Admin Panel → Department Schedules
-- 3. Edit times for each department
-- 4. System will automatically use department times
-- ============================================

