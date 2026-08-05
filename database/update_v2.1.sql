-- ============================================
-- College Face Recognition Attendance System
-- Update Script v2.1
-- ============================================
-- This script updates the system for auto-generated Student IDs
-- Run this ONLY if you already have the database created

USE `attendance_system`;

-- No database structure changes needed!
-- The 'course' and 'parent_email' fields are already nullable

-- Optional: Update existing students to have standardized IDs
-- WARNING: This will change existing Student IDs
-- Uncomment ONLY if you want to standardize existing IDs

/*
SET @counter = 0;
UPDATE users 
SET user_id = CONCAT('STU', YEAR(created_at), LPAD((@counter := @counter + 1), 3, '0'))
WHERE role_id = 1
ORDER BY created_at;
*/

-- Verify the changes
SELECT 'Database is ready for auto-generated Student IDs!' as Status;
SELECT 'No manual changes required - course and parent_email are already optional' as Note;

-- Show sample of users table structure
DESCRIBE users;

COMMIT;

