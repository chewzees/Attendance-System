-- ============================================
-- QUICK UPDATE SCRIPT
-- Run this in phpMyAdmin to add department schedules
-- ============================================

USE `attendance_system`;

-- Create department schedules table
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

-- Insert default schedules
INSERT INTO `department_schedules` (`department`, `start_time`, `end_time`, `late_threshold_minutes`, `description`) VALUES
('Computer Science', '09:00:00', '17:00:00', 15, 'Computer Science Department Schedule'),
('Electronics', '08:30:00', '16:30:00', 15, 'Electronics Department Schedule'),
('Mechanical', '09:00:00', '17:00:00', 15, 'Mechanical Engineering Department Schedule'),
('Civil', '08:00:00', '16:00:00', 15, 'Civil Engineering Department Schedule'),
('Electrical', '09:00:00', '17:00:00', 15, 'Electrical Engineering Department Schedule')
ON DUPLICATE KEY UPDATE 
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  late_threshold_minutes = VALUES(late_threshold_minutes);

-- Add index
CREATE INDEX IF NOT EXISTS idx_department_active ON department_schedules(department, is_active);

-- Verify
SELECT 'UPDATE COMPLETE! Department schedules table created.' as Status;
SELECT * FROM department_schedules;

-- Show all tables (should be 13 now)
SELECT COUNT(*) as Total_Tables FROM information_schema.tables WHERE table_schema = 'attendance_system';

