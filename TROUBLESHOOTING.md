# 🔧 Troubleshooting Guide - Internal Server Error

## ⚠️ You're seeing: "Internal Server Error"

This means something went wrong before PHP could run properly. Let's fix it step by step!

---

## 🎯 Quick Fix Steps (Try these in order)

### ✅ Step 1: Check XAMPP Services

1. Open **XAMPP Control Panel**
2. Make sure these are RUNNING (green):
   - ✓ **Apache** (should show "Running")
   - ✓ **MySQL** (should show "Running")

**If they're not running:**
- Click **Start** button next to Apache
- Click **Start** button next to MySQL
- Wait 5-10 seconds

### ✅ Step 2: Test Basic PHP

**Open in browser:**
```
http://localhost/attendance2/test.php
```

**What you should see:**
- PHP configuration page with lots of information
- If you see this, PHP is working! ✓

**If you see error:**
- Apache might not be running
- Go back to Step 1

### ✅ Step 3: Setup Database

**IMPORTANT: You must create the database first!**

1. Open: http://localhost/phpmyadmin
2. Click **"Import"** tab at the top
3. Click **"Choose File"** button
4. Navigate to: `C:\xamppp\htdocs\attendance2\database\schema.sql`
5. Click **"Go"** button at bottom
6. Wait for success message

**Alternatively, manual creation:**
1. Open: http://localhost/phpmyadmin
2. Click **"SQL"** tab
3. Copy this command:
```sql
CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
4. Click **"Go"**
5. Now import the schema.sql file as above

### ✅ Step 4: Test Database Connection

**Open in browser:**
```
http://localhost/attendance2/index_test.php
```

**What you should see:**
- ✓ PHP is working!
- ✓ Config file loaded successfully!
- ✓ Database connection successful!
- ✓ Database query successful! Users count: 1

**If you see errors, read the error message carefully**

### ✅ Step 5: Access Main System

**If Step 4 worked, open:**
```
http://localhost/attendance2/index.php
```

**Or just:**
```
http://localhost/attendance2/
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "Database connection failed"

**Cause:** Database doesn't exist yet

**Solution:**
```
1. Go to http://localhost/phpmyadmin
2. Import database/schema.sql
3. Refresh the page
```

### Issue 2: "Access denied for user 'root'@'localhost'"

**Cause:** Wrong database credentials

**Solution:**
1. Open `config/database.php`
2. Check these lines:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for XAMPP default
define('DB_NAME', 'attendance_system');
```
3. Make sure DB_PASS is empty (default XAMPP has no password)

### Issue 3: "Table 'attendance_system.users' doesn't exist"

**Cause:** Database created but tables not imported

**Solution:**
```
1. Go to http://localhost/phpmyadmin
2. Click on "attendance_system" database (left side)
3. Click "Import" tab
4. Choose database/schema.sql
5. Click "Go"
```

### Issue 4: Blank white page

**Cause:** PHP error with error display off

**Solution:**
1. Open `index.php`
2. Add these lines at the very top (after `<?php`):
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
3. Refresh the page
4. Now you'll see the actual error

### Issue 5: "Parse error" or "Syntax error"

**Cause:** PHP file has a syntax error

**Solution:**
- Look at the error message
- It will tell you the file name and line number
- Open that file and fix the syntax error

### Issue 6: Apache won't start in XAMPP

**Cause:** Port 80 is already in use (Skype, IIS, etc.)

**Solution:**

**Option A: Change Apache port**
1. XAMPP Control Panel → Apache → Config → httpd.conf
2. Find: `Listen 80`
3. Change to: `Listen 8080`
4. Save and restart Apache
5. Access as: `http://localhost:8080/attendance2/`

**Option B: Stop conflicting program**
1. Close Skype or other programs using port 80
2. Restart Apache

### Issue 7: MySQL won't start in XAMPP

**Cause:** Port 3306 in use or service conflict

**Solution:**
1. Open Task Manager (Ctrl+Shift+Esc)
2. End any "mysqld.exe" processes
3. Try starting MySQL again
4. If still fails, restart computer

---

## 📋 Verification Checklist

Run through this checklist:

- [ ] XAMPP installed correctly
- [ ] Apache is running (green in XAMPP)
- [ ] MySQL is running (green in XAMPP)
- [ ] Can access http://localhost/
- [ ] Can access http://localhost/phpmyadmin
- [ ] Database "attendance_system" exists
- [ ] Database has 12 tables (users, courses, etc.)
- [ ] http://localhost/attendance2/test.php shows PHP info
- [ ] http://localhost/attendance2/index_test.php shows success
- [ ] http://localhost/attendance2/ loads the dashboard

---

## 🔍 Check Apache Error Log

If nothing above works, check the error log:

**Location:**
```
C:\xampp\apache\logs\error.log
```

**Open this file and look for the LAST error (bottom of file)**

Common errors you might see:

1. **"PHP Fatal error"** - Shows exact file and line with error
2. **"Syntax error"** - PHP code has a typo
3. **"Call to undefined function"** - Missing PHP extension
4. **"Class not found"** - File not loaded properly

---

## 🆘 Still Not Working?

### Quick Debug Process:

1. **Test PHP:**
   ```
   http://localhost/attendance2/test.php
   ```
   Should show PHP info page

2. **Test Database:**
   ```
   http://localhost/attendance2/index_test.php
   ```
   Should show connection success

3. **Check Error Log:**
   ```
   C:\xampp\apache\logs\error.log
   ```
   Look at the last few lines

4. **Enable PHP Errors:**
   Add to top of index.php:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Most Common Solution:

**90% of the time, the issue is:**
- ✗ MySQL not running in XAMPP
- ✗ Database not imported yet

**Fix:**
1. Start MySQL in XAMPP
2. Import database/schema.sql in phpMyAdmin
3. Refresh the page

---

## ✅ Success Indicators

When everything is working, you should see:

1. **Dashboard loads** with purple gradient background
2. **Statistics cards** showing numbers (might be 0 if no data)
3. **Navigation menu** at top
4. **No error messages**

---

## 📞 Need More Help?

1. Check the error message carefully
2. Google the specific error message
3. Check Apache error log: `C:\xampp\apache\logs\error.log`
4. Make sure all XAMPP services are running

---

## 🎯 Quick Command Reference

| Action | URL |
|--------|-----|
| **Test PHP** | http://localhost/attendance2/test.php |
| **Test Connection** | http://localhost/attendance2/index_test.php |
| **Main Dashboard** | http://localhost/attendance2/ |
| **phpMyAdmin** | http://localhost/phpmyadmin |
| **XAMPP Dashboard** | http://localhost/dashboard/ |

---

**Most issues are fixed by ensuring MySQL is running and the database is imported!** 

Good luck! 🚀

