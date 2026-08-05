# 🧪 Testing Guide - Admin Features

## ✅ Features to Test

### **1. Edit User Function** ✨ NEW

#### **How to Test:**
1. **Open Admin Panel:**
   ```
   http://localhost/attendance2/admin.php
   ```

2. **Go to "Users" tab**

3. **Click the Edit button (pencil icon)** on any user

4. **What happens:**
   - ✅ Modal opens with "Edit User" title
   - ✅ Form is pre-filled with user's current data
   - ✅ User ID field is read-only (can't change)
   - ✅ All other fields are editable

5. **Make changes:**
   - Change name, email, department, etc.
   - Change password (or leave blank to keep current)
   - Change status (active/inactive/suspended)

6. **Click "Update User"**

7. **Expected Result:**
   - ✅ "User updated successfully!" message
   - ✅ Modal closes
   - ✅ User list refreshes with new data
   - ✅ Changes are saved in database

---

### **2. Delete User Function** ✨ NEW

#### **How to Test:**
1. **Open Admin Panel → Users tab**

2. **Click the Delete button (trash icon)** on any user

3. **What happens:**
   - ✅ Confirmation dialog appears: "Are you sure you want to delete this user?"

4. **Click "OK" to confirm**

5. **Expected Result:**
   - ✅ "User deactivated successfully!" message
   - ✅ User status changes to "inactive"
   - ✅ User disappears from active users list (filter by status to see)
   - ✅ User data is preserved (soft delete)

---

### **3. Department-Specific Times** ✨ NEW

#### **Setup:**
1. **Import the update script:**
   - Go to: http://localhost/phpmyadmin
   - Select `attendance_system` database
   - Click "SQL" tab
   - Open: `database/update_department_times.sql`
   - Copy all content
   - Paste and click "Go"

2. **Verify table created:**
   - You should see `department_schedules` table
   - 5 departments pre-configured

#### **How It Works:**
- Each department now has its own:
  - **Start Time** (when attendance begins)
  - **Late Threshold** (minutes grace period)
  
#### **Test:**
1. **Register students in different departments:**
   ```
   Student 1: Computer Science (starts 9:00 AM)
   Student 2: Electronics (starts 8:30 AM)
   Student 3: Civil (starts 8:00 AM)
   ```

2. **Mark attendance at 8:45 AM:**
   - CS student → ✅ On time (before 9:00)
   - Electronics → ⚠️ Late (after 8:30)
   - Civil → ⚠️ Late (after 8:00)

3. **Check the results:**
   - Each student judged by their department's schedule
   - Late minutes calculated from their dept start time

---

## 📋 Complete Testing Checklist

### **Admin Panel - Users Tab:**
- [ ] ✅ Can view list of all users
- [ ] ✅ Can filter by role (Student/Professor/Admin)
- [ ] ✅ Can search users
- [ ] ✅ "Add User" button opens modal
- [ ] ✅ Can create new user successfully
- [ ] ✅ "Edit" button opens modal with data
- [ ] ✅ Can update user information
- [ ] ✅ Can change user password
- [ ] ✅ Can change user status
- [ ] ✅ "Delete" button shows confirmation
- [ ] ✅ Delete sets user to inactive
- [ ] ✅ Changes persist after page refresh

### **Department Schedules:**
- [ ] ✅ Table `department_schedules` exists
- [ ] ✅ 5 departments pre-configured
- [ ] ✅ Each dept has start_time, end_time
- [ ] ✅ Attendance uses dept-specific times
- [ ] ✅ Late detection works per department
- [ ] ✅ Different depts = different late times

### **Auto-Generated Student IDs:**
- [ ] ✅ Registration form shows "Auto-Generated"
- [ ] ✅ Student ID field is read-only
- [ ] ✅ First student gets STU2024001
- [ ] ✅ Second student gets STU2024002
- [ ] ✅ IDs increment correctly
- [ ] ✅ Year changes with calendar year

---

## 🎯 Quick Test Scenarios

### **Scenario 1: Edit Student Information**
```
1. Admin Panel → Users → Find student STU2024001
2. Click Edit button
3. Change name from "John Doe" to "John Smith"
4. Change department from "CS" to "Electronics"
5. Click Update User
6. Verify: Name and department updated
```

### **Scenario 2: Deactivate User**
```
1. Admin Panel → Users → Select any user
2. Click Delete button
3. Confirm deletion
4. Verify: User status = inactive
5. Filter by "All" status to see user still exists
6. User can't login anymore (inactive)
```

### **Scenario 3: Department Time Test**
```
1. Register CS student (starts 9:00 AM)
2. Register Electronics student (starts 8:30 AM)
3. Both mark attendance at 8:45 AM
4. CS student: ON TIME ✅
5. Electronics student: LATE ⚠️
6. Check attendance records to verify
```

### **Scenario 4: Update User Password**
```
1. Admin Panel → Users → Edit any user
2. Enter new password in password field
3. Click Update User
4. User can now login with new password
5. Leave blank to keep old password
```

---

## 🔍 How to Verify Changes

### **Check Database:**
```sql
-- View all users
SELECT user_id, name, email, status, department FROM users ORDER BY created_at DESC;

-- View department schedules
SELECT * FROM department_schedules;

-- View recent attendance with dept times
SELECT a.*, u.department, ds.start_time, ds.late_threshold_minutes
FROM attendance a
JOIN users u ON a.user_id = u.id
LEFT JOIN department_schedules ds ON u.department = ds.department
ORDER BY a.created_at DESC LIMIT 10;
```

### **Check Browser Console:**
- Press F12 to open Developer Tools
- Look for any errors in Console tab
- Network tab shows API requests/responses

---

## 🐛 Known Behaviors

### **User Status:**
- ❗ "Delete" doesn't actually delete (it's a soft delete)
- ✅ User becomes "inactive" but data is preserved
- ✅ Can reactivate by editing and changing status back

### **Department Times:**
- ❗ If department not in `department_schedules`, uses system default
- ✅ System default: 9:00 AM start, 15 min late threshold
- ✅ Can add new departments via SQL or future admin UI

### **Auto-Generated IDs:**
- ❗ Format: STU{YEAR}{NUMBER}
- ✅ Numbers reset each year (STU2024001 → STU2025001)
- ✅ Gaps in numbers if users deleted (intentional)

---

## 📊 Test Results Template

Use this to track your testing:

```
Date: _______________
Tester: _______________

Feature: Edit User
Status: [ ] Pass  [ ] Fail
Notes: _______________________________

Feature: Delete User
Status: [ ] Pass  [ ] Fail
Notes: _______________________________

Feature: Department Times
Status: [ ] Pass  [ ] Fail
Notes: _______________________________

Feature: Auto Student IDs
Status: [ ] Pass  [ ] Fail
Notes: _______________________________

Overall System Status: [ ] Ready  [ ] Needs Work
```

---

## 🆘 Troubleshooting

### **Edit button doesn't work:**
- Check browser console for errors
- Verify `api/users.php` exists and is accessible
- Test API directly: `http://localhost/attendance2/api/users.php?id=1`

### **Delete confirmation not showing:**
- Check if JavaScript is enabled
- Clear browser cache
- Try different browser

### **Department times not working:**
- Run `database/update_department_times.sql`
- Verify `department_schedules` table exists
- Check if department names match exactly

---

## ✅ Success Criteria

System is ready when:
- ✅ All users can be edited
- ✅ All users can be deleted (deactivated)
- ✅ Changes persist after refresh
- ✅ Department times work correctly
- ✅ No errors in browser console
- ✅ No errors in Apache error log

---

**Happy Testing! 🚀**

