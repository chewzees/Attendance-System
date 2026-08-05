# 📊 Demo Data Installation Guide

## Overview
This guide will help you populate your attendance system database with realistic demo data so you can see the charts and features working with actual data.

---

## 📁 Files

1. **`demo_data_simple.sql`** - Recommended simple version (easier to run)
2. **`demo_data.sql`** - Advanced version with more complex queries

---

## 🚀 Quick Start

### Step 1: Import Database Schema
First, make sure you've imported the main schema:
```sql
-- Import schema.sql first
-- This creates all tables and structure
```

### Step 2: Import Demo Data
```sql
-- Import demo_data_simple.sql
-- This populates tables with demo records
```

### Using phpMyAdmin:
1. Open phpMyAdmin
2. Select `attendance_system` database
3. Click **Import** tab
4. Choose file: `demo_data_simple.sql`
5. Click **Go**

### Using MySQL Command Line:
```bash
mysql -u root -p attendance_system < database/demo_data_simple.sql
```

---

## 📊 What Gets Created

### Users
- **22 Students** across 5 departments:
  - Computer Science: 7 students
  - Electronics: 5 students
  - Mechanical: 4 students
  - Civil: 3 students
  - Electrical: 3 students
- **5 Professors** (one per department)

### Courses
- **16 Courses** across all departments
- Mix of 3rd and 5th semester courses
- Each course assigned to a professor

### Attendance Records
- **~450 attendance records** for the last 30 days
- Weekdays only (Monday-Friday)
- Realistic distribution:
  - 70% Present
  - 20% Late
  - 10% Absent
- Various arrival times

### Other Data
- Course enrollments
- Leave requests (8 requests with various statuses)
- Notifications (25 notifications)
- Attendance logs (100 log entries)
- Lectures (40 lecture records)
- Lecture attendance (200 records)

---

## 🎯 What You'll See

After importing, you'll see:

### Dashboard Charts
- ✅ **Attendance Status Breakdown** - Shows present/late/absent distribution
- ✅ **Attendance Trend** - Shows trends over last 7/30 days
- ✅ **Department Chart** - Compares departments side-by-side

### Statistics
- Total students: 22
- Total professors: 5
- Active courses: 16
- Attendance records: ~450
- Leave requests: 8

---

## 🔍 Testing the Charts

### 1. Attendance Status Chart
- Click "Today", "Week", "Month", "All Time" buttons
- See different time periods
- Hover for percentages

### 2. Trend Chart
- Toggle between "Last 7 Days" and "Last 30 Days"
- See attendance patterns over time
- Multiple lines for Present/Late/Absent

### 3. Department Chart
- Compare all 5 departments
- Stacked bars show Present/Late/Absent
- See which departments have better attendance

---

## 🎨 Demo Data Characteristics

### Attendance Patterns
- **High Attendance Students:** 70-85% attendance rate
- **Average Students:** 60-75% attendance rate
- **Low Attendance Students:** 45-60% attendance rate

### Time Distribution
- **On Time:** 70% arrive at 9:00 AM
- **Late:** 20% arrive between 9:15-9:45 AM
- **Absent:** 10% don't mark attendance

### Department Differences
- Computer Science: Good attendance
- Electronics: Average attendance
- Mechanical: Varying attendance
- Civil: Lower attendance
- Electrical: Good attendance

---

## 🔄 Refresh Data

To refresh with new random data:
```sql
-- Delete existing demo data
DELETE FROM attendance;
DELETE FROM lecture_attendance;
DELETE FROM lectures;
-- ... (keep user and course data)

-- Re-run demo_data_simple.sql
-- (or just the attendance section)
```

---

## ⚠️ Important Notes

1. **Password:** All demo users have password: `password` (hashed)
2. **Dates:** Attendance records are for the **last 30 days**
3. **Weekends:** No attendance records for weekends
4. **Safe:** Uses `INSERT IGNORE` to prevent duplicates
5. **Re-runnable:** Can be run multiple times safely

---

## 🐛 Troubleshooting

### No Data Showing?
1. Check if tables exist
2. Verify data was imported
3. Check database connection
4. Ensure `attendance_system` database is selected

### Charts Not Loading?
1. Check browser console for errors
2. Verify API endpoint: `api/stats.php`
3. Check if data exists in tables
4. Clear browser cache

### Wrong Data?
1. Check date ranges
2. Verify student/course relationships
3. Check enrollment records
4. Verify attendance records have correct dates

---

## 📝 Customization

### Add More Students
```sql
INSERT INTO users (user_id, name, email, password, role_id, department, semester, status)
VALUES ('STU2024023', 'New Student', 'new@student.edu', '$2y$10$...', 1, 'Computer Science', 3, 'active');
```

### Add More Attendance
```sql
INSERT INTO attendance (user_id, date, time_in, status, is_late, minutes_late)
SELECT id, CURDATE(), '09:00:00', 'present', 0, 0
FROM users WHERE role_id = 1;
```

### Change Attendance Patterns
Modify the MOD() calculations in demo_data_simple.sql to change:
- Attendance rates
- Late percentages
- Time distributions

---

## ✅ Verification Queries

Check if data was imported correctly:

```sql
-- Count students
SELECT COUNT(*) as students FROM users WHERE role_id = 1;

-- Count attendance records
SELECT COUNT(*) as records FROM attendance;

-- Check date range
SELECT MIN(date) as earliest, MAX(date) as latest FROM attendance;

-- Check departments
SELECT department, COUNT(*) as students 
FROM users WHERE role_id = 1 
GROUP BY department;

-- Check attendance status distribution
SELECT status, COUNT(*) as count 
FROM attendance 
GROUP BY status;
```

---

## 🎓 Next Steps

After importing demo data:

1. ✅ Open dashboard (`index.php`)
2. ✅ View charts and statistics
3. ✅ Test different time periods
4. ✅ Explore department comparisons
5. ✅ Check student portal
6. ✅ Test admin panel features

---

## 📚 Related Files

- `schema.sql` - Main database schema
- `demo_data_simple.sql` - Demo data (recommended)
- `demo_data.sql` - Advanced demo data
- `ATTENDANCE_CHARTS_GUIDE.md` - Charts documentation

---

**Last Updated:** 2024  
**Status:** ✅ Ready to Use

