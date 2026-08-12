<?php
/**
 * System Check - Verify All Functions Working
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/helpers.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .check-item { padding: 1rem; margin: 0.5rem 0; border-radius: 8px; }
        .check-pass { background: #d1fae5; border-left: 4px solid #10b981; }
        .check-fail { background: #fee2e2; border-left: 4px solid #ef4444; }
        .check-icon { font-size: 1.5rem; margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="container" style="padding: 2rem;">
        <div class="content-card">
            <h1 style="text-align: center; margin-bottom: 2rem;">🔍 System Status Check</h1>
            
            <?php
            $checks = [];
            
            // Check 1: Database Connection
            try {
                $db = getDB();
                $checks[] = ['pass' => true, 'name' => 'Database Connection', 'message' => 'Connected successfully'];
            } catch (Exception $e) {
                $checks[] = ['pass' => false, 'name' => 'Database Connection', 'message' => 'Failed: ' . $e->getMessage()];
            }
            
            // Check 2: Tables Exist
            try {
                $db = getDB();
                $stmt = $db->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $tableCount = count($tables);
                $checks[] = ['pass' => $tableCount >= 13, 'name' => 'Database Tables', 'message' => "$tableCount tables found (need 13)"];
            } catch (Exception $e) {
                $checks[] = ['pass' => false, 'name' => 'Database Tables', 'message' => 'Error: ' . $e->getMessage()];
            }
            
            // Check 3: Department Schedules Table
            try {
                $db = getDB();
                $stmt = $db->query("SELECT COUNT(*) as count FROM department_schedules");
                $result = $stmt->fetch();
                $checks[] = ['pass' => $result['count'] > 0, 'name' => 'Department Schedules', 'message' => $result['count'] . ' schedules configured'];
            } catch (Exception $e) {
                $checks[] = ['pass' => false, 'name' => 'Department Schedules', 'message' => 'Table not found - Run UPDATE_DATABASE_NOW.sql'];
            }
            
            // Check 4: Users Table
            try {
                $db = getDB();
                $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch();
                $checks[] = ['pass' => true, 'name' => 'Users Table', 'message' => $result['count'] . ' users in system'];
            } catch (Exception $e) {
                $checks[] = ['pass' => false, 'name' => 'Users Table', 'message' => 'Error: ' . $e->getMessage()];
            }
            
            // Check 5: Courses Table
            try {
                $db = getDB();
                $stmt = $db->query("SELECT COUNT(*) as count FROM courses");
                $result = $stmt->fetch();
                $checks[] = ['pass' => true, 'name' => 'Courses Table', 'message' => $result['count'] . ' courses in system'];
            } catch (Exception $e) {
                $checks[] = ['pass' => false, 'name' => 'Courses Table', 'message' => 'Error: ' . $e->getMessage()];
            }
            
            // Check 6: API Files
            $apiFiles = ['register.php', 'mark_attendance.php', 'users.php', 'courses.php', 'department_schedules.php'];
            $apiExists = 0;
            foreach ($apiFiles as $file) {
                if (file_exists("api/$file")) $apiExists++;
            }
            $checks[] = ['pass' => $apiExists === count($apiFiles), 'name' => 'API Files', 'message' => "$apiExists/" . count($apiFiles) . " API files found"];
            
            // Check 7: Config Files
            $configFiles = ['database.php', 'helpers.php'];
            $configExists = 0;
            foreach ($configFiles as $file) {
                if (file_exists("config/$file")) $configExists++;
            }
            $checks[] = ['pass' => $configExists === count($configFiles), 'name' => 'Config Files', 'message' => "$configExists/" . count($configFiles) . " config files found"];
            
            // Display Results
            foreach ($checks as $check) {
                $class = $check['pass'] ? 'check-pass' : 'check-fail';
                $icon = $check['pass'] ? '✅' : '❌';
                echo "<div class='check-item $class'>";
                echo "<span class='check-icon'>$icon</span>";
                echo "<strong>{$check['name']}</strong>: {$check['message']}";
                echo "</div>";
            }
            
            // Overall Status
            $passed = array_filter($checks, function($c) { return $c['pass']; });
            $total = count($checks);
            $passCount = count($passed);
            
            echo "<div style='margin-top: 2rem; padding: 2rem; text-align: center; background: " . ($passCount === $total ? '#d1fae5' : '#fef3c7') . "; border-radius: 8px;'>";
            echo "<h2>Overall Status: $passCount/$total Checks Passed</h2>";
            
            if ($passCount === $total) {
                echo "<p style='font-size: 1.2rem; color: #10b981;'>✅ <strong>ALL SYSTEMS OPERATIONAL!</strong></p>";
                echo "<p>Your system is fully functional and ready to use.</p>";
            } else {
                echo "<p style='font-size: 1.2rem; color: #f59e0b;'>⚠️ <strong>ACTION REQUIRED</strong></p>";
                echo "<p>Please check the failed items above and fix them.</p>";
                
                // Check if department_schedules is the issue
                $deptCheck = array_filter($checks, function($c) { return $c['name'] === 'Department Schedules' && !$c['pass']; });
                if (!empty($deptCheck)) {
                    echo "<div style='margin-top: 1rem; padding: 1rem; background: white; border-radius: 8px; text-align: left;'>";
                    echo "<h3>🔧 Quick Fix:</h3>";
                    echo "<ol>";
                    echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
                    echo "<li>Select database: <strong>attendance_system</strong></li>";
                    echo "<li>Click <strong>SQL</strong> tab</li>";
                    echo "<li>Open file: <strong>UPDATE_DATABASE_NOW.sql</strong></li>";
                    echo "<li>Copy all content and paste in SQL box</li>";
                    echo "<li>Click <strong>Go</strong></li>";
                    echo "<li>Refresh this page</li>";
                    echo "</ol>";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // Quick Links
            echo "<div style='margin-top: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;'>";
            echo "<a href='index.php' class='btn btn-primary'>Dashboard</a>";
            echo "<a href='admin.php' class='btn btn-success'>Admin Panel</a>";
            echo "<a href='register.php' class='btn btn-outline'>Register Student</a>";
            echo "<a href='face_recognition.php' class='btn btn-warning'>Mark Attendance</a>";
            echo "</div>";
            
            // System Info
            echo "<div style='margin-top: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 8px;'>";
            echo "<h3>System Information:</h3>";
            echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
            echo "<p><strong>Database:</strong> " . (isset($db) ? 'MySQL Connected' : 'Not Connected') . "</p>";
            echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
            echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            echo "</div>";
            ?>
            
        </div>
    </div>
</body>
</html>

