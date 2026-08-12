<?php
/**
 * Admin Panel
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';
require_once 'config/database.php';
require_once 'config/helpers.php';
requireRole(['admin', 'hod']);


// Get system settings
$systemSettings = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM system_settings");
    while ($row = $stmt->fetch()) {
        $systemSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $systemSettings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Attendance System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Utilities -->
    <script src="assets/js/utils.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <span>Attendance System</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="student_portal.php"><i class="fas fa-user"></i> Student Portal</a></li>
                    <li><a href="professor_dashboard.php"><i class="fas fa-chalkboard-teacher"></i> Professor</a></li>
                    <li><a href="admin.php"><i class="fas fa-cog"></i> Admin</a></li>

                    <li><a href="<?php echo htmlspecialchars(baseUrl('logout.php')); ?>"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Page Title -->
            <div class="page-title">
                <h1><i class="fas fa-user-shield"></i> Admin Control Panel</h1>
                <p>System management and analytics</p>
            </div>

            <!-- Tabs -->
            <div class="content-card">
                <div class="tab-bar">
                    <button class="btn btn-primary tab-btn active" data-tab="users">
                        <i class="fas fa-users"></i> Users
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="courses">
                        <i class="fas fa-book"></i> Courses
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="reports">
                        <i class="fas fa-chart-line"></i> Reports
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="settings">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="analytics">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="schedules">
                        <i class="fas fa-clock"></i> Dept Schedules
                    </button>
                </div>

                <!-- Tab Content: Users Management -->
                <div id="usersTab" class="tab-content active">
                    <div class="flex-between mb-3">
                        <h2 class="card-title"><i class="fas fa-users"></i> User Management</h2>
                        <div class="toolbar">
                            <select class="form-select" id="roleFilter" style="width: auto; min-width: 140px;">
                                <option value="">All Roles</option>
                                <option value="Student">Students</option>
                                <option value="Professor">Professors</option>
                                <option value="Admin">Admins</option>
                            </select>
                            <button class="btn btn-success" onclick="openUserModal()">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Courses Management -->
                <div id="coursesTab" class="tab-content">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 class="card-title"><i class="fas fa-book"></i> Course Management</h2>
                        <button class="btn btn-success" onclick="openCourseModal()">
                            <i class="fas fa-plus"></i> Add Course
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Semester</th>
                                    <th>Credits</th>
                                    <th>Professor</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="coursesTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Reports -->
                <div id="reportsTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-chart-line"></i> Advanced Reports
                    </h2>
                    
                    <div class="grid-auto-lg" style="margin-bottom: 2rem;">
                        <div>
                            <label class="form-label">Report Type</label>
                            <select class="form-select" id="reportType">
                                <option value="summary">Summary Report</option>
                                <option value="defaulters">Defaulters List</option>
                                <option value="department">Department Report</option>
                                <option value="daily">Daily Report</option>
                                <option value="export">Export Data</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="reportStartDate" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="reportEndDate" value="<?php echo date('Y-m-t'); ?>">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button class="btn btn-primary" onclick="generateReport()" style="width: 100%;">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </button>
                        </div>
                    </div>

                    <div id="reportOutput"></div>
                </div>

                <!-- Tab Content: System Settings -->
                <div id="settingsTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-cog"></i> System Settings
                    </h2>
                    
                    <form id="settingsForm">
                        <div class="grid-2" style="margin-bottom: 0;">
                            <div>
                                <h3>Attendance Settings</h3>
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" class="form-control" id="attendanceStartTime" 
                                           value="<?php echo $systemSettings['attendance_start_time'] ?? '09:00:00'; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" id="attendanceEndTime" 
                                           value="<?php echo $systemSettings['attendance_end_time'] ?? '17:00:00'; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Late Threshold (minutes)</label>
                                    <input type="number" class="form-control" id="lateThreshold" 
                                           value="<?php echo $systemSettings['late_threshold_minutes'] ?? '15'; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Minimum Attendance (%)</label>
                                    <input type="number" class="form-control" id="minAttendance" 
                                           value="<?php echo $systemSettings['minimum_attendance_percentage'] ?? '75'; ?>">
                                </div>
                            </div>

                            <div>
                                <h3>Face Recognition Settings</h3>
                                <div class="form-group">
                                    <label class="form-label">Recognition Threshold</label>
                                    <input type="number" class="form-control" id="recognitionThreshold" 
                                           step="0.01" min="0" max="1"
                                           value="<?php echo $systemSettings['face_recognition_threshold'] ?? '0.6'; ?>">
                                    <small>Lower = More strict (0.3-0.8 recommended)</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Detection Model</label>
                                    <select class="form-select" id="detectionModel">
                                        <option value="ssd_mobilenetv1" <?php echo ($systemSettings['face_recognition_model'] ?? '') === 'ssd_mobilenetv1' ? 'selected' : ''; ?>>
                                            SSD MobileNetV1 (Recommended)
                                        </option>
                                        <option value="tiny_face_detector">Tiny Face Detector (Faster)</option>
                                    </select>
                                </div>
                                
                                <h3 style="margin-top: 1.5rem;">Notification Settings</h3>
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" id="enableEmail" 
                                               <?php echo ($systemSettings['enable_email_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                        Enable Email Notifications
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" id="enableParentNotif" 
                                               <?php echo ($systemSettings['enable_parent_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                        Enable Parent Notifications
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" style="margin-top: 1.5rem;">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>

                <!-- Tab Content: Analytics -->
                <div id="analyticsTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-chart-bar"></i> System Analytics
                    </h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <p class="stat-title">Total Students</p>
                            <h2 class="stat-value" id="totalStudents">0</h2>
                        </div>
                        <div class="stat-card success">
                            <p class="stat-title">Average Attendance</p>
                            <h2 class="stat-value" id="avgAttendance">0%</h2>
                        </div>
                        <div class="stat-card warning">
                            <p class="stat-title">Total Courses</p>
                            <h2 class="stat-value" id="totalCourses">0</h2>
                        </div>
                        <div class="stat-card danger">
                            <p class="stat-title">Defaulters</p>
                            <h2 class="stat-value" id="totalDefaulters">0</h2>
                        </div>
                    </div>

                    <div class="grid-2" style="margin-top: 2rem; margin-bottom: 0;">
                        <div class="content-card">
                            <h3>Weekly Attendance Trend</h3>
                            <canvas id="weeklyChart"></canvas>
                        </div>
                        <div class="content-card">
                            <h3>Department-wise Distribution</h3>
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Department Schedules -->
                <div id="schedulesTab" class="tab-content">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 class="card-title"><i class="fas fa-clock"></i> Department Schedules</h2>
                        <button class="btn btn-success" onclick="openScheduleModal()">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Late After (min)</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="schedulesTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="userModalTitle">Add New User</h2>
                <span class="close-modal" onclick="closeUserModal()">&times;</span>
            </div>
            <form id="userForm">
                <input type="hidden" id="editUserId" value="">
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" class="form-control" id="newUserId" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="newUserName" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="newUserEmail" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-select" id="newUserRole" required>
                        <option value="1">Student</option>
                        <option value="2">Professor</option>
                        <option value="3">Admin</option>
                        <option value="4">HOD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" class="form-control" id="newUserDept">
                </div>
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <input type="number" class="form-control" id="newUserSemester" min="1" max="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="newUserPhone">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="newUserStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="newUserPassword" placeholder="Enter new password">
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-save"></i> <span id="userFormBtnText">Add User</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Department Schedule Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="scheduleModalTitle">Add Department Schedule</h2>
                <span class="close-modal" onclick="closeScheduleModal()">&times;</span>
            </div>
            <form id="scheduleForm">
                <input type="hidden" id="editScheduleDept" value="">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-select" id="scheduleDept" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Civil">Civil</option>
                        <option value="Electrical">Electrical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="scheduleStartTime" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time</label>
                    <input type="time" class="form-control" id="scheduleEndTime" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Late Threshold (minutes)</label>
                    <input type="number" class="form-control" id="scheduleLateThreshold" min="0" max="60" value="15" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="scheduleDescription" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-save"></i> <span id="scheduleFormBtnText">Save Schedule</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="courseModalTitle">Edit Course</h2>
                <span class="close-modal" onclick="closeCourseModal()">&times;</span>
            </div>
            <form id="courseForm">
                <input type="hidden" id="editCourseId" value="">
                <div class="form-group">
                    <label class="form-label">Course Code</label>
                    <input type="text" class="form-control" id="courseCode" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Name</label>
                    <input type="text" class="form-control" id="courseName" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-select" id="courseDept" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Civil">Civil</option>
                        <option value="Electrical">Electrical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <input type="number" class="form-control" id="courseSemester" min="1" max="8" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Credits</label>
                    <input type="number" class="form-control" id="courseCredits" min="1" max="6" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="courseStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-save"></i> <span id="courseFormBtnText">Update Course</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = this.dataset.tab;
                
                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline');
                });
                this.classList.remove('btn-outline');
                this.classList.add('btn-primary', 'active');
                
                // Update content
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(tab + 'Tab').classList.add('active');
                
                // Load data for the tab
                if (tab === 'users') loadUsers();
                if (tab === 'courses') loadCourses();
                if (tab === 'analytics') loadAnalytics();
                if (tab === 'schedules') loadSchedules();
            });
        });

        // Initial load
        loadUsers();

        // Load users
        async function loadUsers() {
            const role = document.getElementById('roleFilter')?.value || '';
            try {
                const response = await fetch('api/users.php' + (role ? '?role=' + role : ''));
                const result = await response.json();
                
                if (result.success) {
                    displayUsers(result.data);
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        function displayUsers(users) {
            const tbody = document.getElementById('usersTable');
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No users found</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.user_id}</td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td><span class="badge badge-info">${user.role_name}</span></td>
                    <td>${user.department || '-'}</td>
                    <td><span class="badge badge-${user.status === 'active' ? 'success' : 'danger'}">${user.status}</span></td>
                    <td>
                        <button class="btn btn-outline" style="padding: 0.5rem;" onclick="editUser(${user.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" style="padding: 0.5rem;" onclick="deleteUser(${user.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Load courses
        async function loadCourses() {
            try {
                const response = await fetch('api/courses.php');
                const result = await response.json();
                
                if (result.success) {
                    displayCourses(result.data);
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }

        function displayCourses(courses) {
            const tbody = document.getElementById('coursesTable');
            
            if (courses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No courses found</td></tr>';
                return;
            }
            
            tbody.innerHTML = courses.map(course => `
                <tr>
                    <td>${course.course_code}</td>
                    <td>${course.course_name}</td>
                    <td>${course.department}</td>
                    <td>${course.semester}</td>
                    <td>${course.credits}</td>
                    <td>${course.professor_name || 'Unassigned'}</td>
                    <td>${course.student_count}</td>
                    <td><span class="badge badge-${course.status === 'active' ? 'success' : 'danger'}">${course.status}</span></td>
                    <td>
                        <button class="btn btn-outline" style="padding: 0.5rem;" onclick="editCourse(${course.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" style="padding: 0.5rem;" onclick="deleteCourse(${course.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Generate report
        async function generateReport() {
            const type = document.getElementById('reportType').value;
            const startDate = document.getElementById('reportStartDate').value;
            const endDate = document.getElementById('reportEndDate').value;
            
            document.getElementById('reportOutput').innerHTML = '<p style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Generating report...</p>';
            
            try {
                const response = await fetch(`api/reports.php?type=${type}&start_date=${startDate}&end_date=${endDate}`);
                const result = await response.json();
                
                if (result.success) {
                    displayReport(type, result.data);
                } else {
                    document.getElementById('reportOutput').innerHTML = '<p style="color: red;">Error generating report</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('reportOutput').innerHTML = '<p style="color: red;">Failed to generate report</p>';
            }
        }

        function displayReport(type, data) {
            const output = document.getElementById('reportOutput');
            
            if (type === 'summary') {
                output.innerHTML = `
                    <div class="content-card">
                        <h3>Attendance Summary</h3>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <p class="stat-title">Total Records</p>
                                <h2 class="stat-value">${data.total_records || 0}</h2>
                            </div>
                            <div class="stat-card success">
                                <p class="stat-title">Present</p>
                                <h2 class="stat-value">${data.present_count || 0}</h2>
                            </div>
                            <div class="stat-card warning">
                                <p class="stat-title">Late</p>
                                <h2 class="stat-value">${data.late_count || 0}</h2>
                            </div>
                            <div class="stat-card danger">
                                <p class="stat-title">Absent</p>
                                <h2 class="stat-value">${data.absent_count || 0}</h2>
                            </div>
                        </div>
                        <p><strong>Overall Percentage:</strong> ${data.attendance_percentage}%</p>
                    </div>
                `;
            } else if (type === 'defaulters' && Array.isArray(data)) {
                const table = data.map(d => `
                    <tr>
                        <td>${d.user_id}</td>
                        <td>${d.name}</td>
                        <td>${d.department}</td>
                        <td>${d.percentage}%</td>
                        <td><span class="badge badge-danger">Level ${d.warning_level}</span></td>
                    </tr>
                `).join('');
                
                output.innerHTML = `
                    <div class="content-card">
                        <h3>Attendance Defaulters</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Attendance %</th>
                                    <th>Warning</th>
                                </tr>
                            </thead>
                            <tbody>${table}</tbody>
                        </table>
                    </div>
                `;
            }
        }

        // Load analytics
        async function loadAnalytics() {
            try {
                const response = await fetch('api/stats.php');
                const result = await response.json();
                
                if (result.success) {
                    displayAnalytics(result.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayAnalytics(data) {
            document.getElementById('totalStudents').textContent = data.overview?.total_students || 0;
            document.getElementById('totalCourses').textContent = data.overview?.active_courses || 0;
            document.getElementById('totalDefaulters').textContent = data.defaulters?.length || 0;
            
            // Calculate average attendance
            const todayAttendance = data.today_attendance || {};
            const avgAttendance = (todayAttendance.total > 0 && todayAttendance.present) ? 
                Math.round((todayAttendance.present / todayAttendance.total) * 100) : 0;
            document.getElementById('avgAttendance').textContent = avgAttendance + '%';
        }

        // Modal functions
        function openUserModal() {
            // Reset form for adding new user
            document.getElementById('userForm').reset();
            document.getElementById('editUserId').value = '';
            document.getElementById('userModalTitle').textContent = 'Add New User';
            document.getElementById('userFormBtnText').textContent = 'Add User';
            document.getElementById('newUserId').readOnly = false;
            document.getElementById('userModal').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('active');
            document.getElementById('userForm').reset();
        }

        // Edit user function
        async function editUser(id) {
            try {
                // Fetch user data
                const response = await fetch(`api/users.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const user = result.data;
                    
                    // Populate form with user data
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('newUserId').value = user.user_id;
                    document.getElementById('newUserName').value = user.name;
                    document.getElementById('newUserEmail').value = user.email;
                    document.getElementById('newUserRole').value = user.role_id;
                    document.getElementById('newUserDept').value = user.department || '';
                    document.getElementById('newUserSemester').value = user.semester || '';
                    document.getElementById('newUserPhone').value = user.phone || '';
                    document.getElementById('newUserStatus').value = user.status;
                    
                    // Update modal title
                    document.getElementById('userModalTitle').textContent = 'Edit User';
                    document.getElementById('userFormBtnText').textContent = 'Update User';
                    document.getElementById('newUserId').readOnly = true;
                    
                    // Open modal
                    document.getElementById('userModal').classList.add('active');
                } else {
                    toast.error('Error loading user: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                toast.error('Failed to load user data');
            }
        }

        // Delete user function
        async function deleteUser(id) {
            if (!confirm('⚠️ WARNING: This will PERMANENTLY DELETE the user and all their data from the database!\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?')) {
                return;
            }

            // Double confirmation for safety
            if (!confirm('FINAL CONFIRMATION:\n\nThe user and ALL related data (attendance records, enrollments, etc.) will be permanently removed.\n\nClick OK to proceed with permanent deletion.')) {
                return;
            }

            try {
                const response = await fetch('api/users.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    toast.success('User permanently deleted from database!');
                    loadUsers(); // Reload the users list
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                toast.error('Failed to delete user');
            }
        }

        // Form submissions
        document.getElementById('settingsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const settings = {
                attendance_start_time: document.getElementById('attendanceStartTime').value,
                attendance_end_time: document.getElementById('attendanceEndTime').value,
                late_threshold_minutes: document.getElementById('lateThreshold').value,
                minimum_attendance_percentage: document.getElementById('minAttendance').value,
                face_recognition_threshold: document.getElementById('faceThreshold').value,
                enable_email_notifications: document.getElementById('emailNotifications').checked ? '1' : '0',
                enable_parent_notifications: document.getElementById('parentNotifications').checked ? '1' : '0'
            };
            
            try {
                const response = await fetch('api/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(settings)
                });
                
                const result = await response.json();
                if (result.success) {
                    toast.success('Settings saved successfully!');
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                toast.error('Failed to save settings');
            }
        });

        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const editUserId = document.getElementById('editUserId').value;
            const isEdit = editUserId !== '';
            
            const data = {
                user_id: document.getElementById('newUserId').value,
                name: document.getElementById('newUserName').value,
                email: document.getElementById('newUserEmail').value,
                role_id: document.getElementById('newUserRole').value,
                department: document.getElementById('newUserDept').value,
                semester: document.getElementById('newUserSemester').value,
                phone: document.getElementById('newUserPhone').value,
                status: document.getElementById('newUserStatus').value
            };
            
            // Add password only if provided
            const password = document.getElementById('newUserPassword').value;
            if (password) {
                data.password = password;
            }
            
            // If editing, add the ID
            if (isEdit) {
                data.id = editUserId;
            }

            try {
                const response = await fetch('api/users.php', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    toast.success(isEdit ? 'User updated successfully!' : 'User added successfully!');
                    closeUserModal();
                    this.reset();
                    loadUsers();
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                toast.error(isEdit ? 'Failed to update user' : 'Failed to add user');
            }
        });

        // Filter handler
        document.getElementById('roleFilter')?.addEventListener('change', loadUsers);

        // ===================================
        // DEPARTMENT SCHEDULES FUNCTIONS
        // ===================================
        
        async function loadSchedules() {
            try {
                const response = await fetch('api/department_schedules.php');
                const result = await response.json();
                
                if (result.success) {
                    displaySchedules(result.data);
                }
            } catch (error) {
                console.error('Error loading schedules:', error);
            }
        }

        function displaySchedules(schedules) {
            const tbody = document.getElementById('schedulesTable');
            
            if (schedules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No schedules configured</td></tr>';
                return;
            }
            
            tbody.innerHTML = schedules.map(schedule => `
                <tr>
                    <td><strong>${schedule.department}</strong></td>
                    <td>${schedule.start_time}</td>
                    <td>${schedule.end_time}</td>
                    <td>${schedule.late_threshold_minutes}</td>
                    <td>${schedule.description || '-'}</td>
                    <td><span class="badge badge-${schedule.is_active ? 'success' : 'danger'}">${schedule.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-outline" style="padding: 0.5rem;" onclick="editSchedule('${schedule.department}')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function openScheduleModal() {
            document.getElementById('scheduleForm').reset();
            document.getElementById('editScheduleDept').value = '';
            document.getElementById('scheduleModalTitle').textContent = 'Add Department Schedule';
            document.getElementById('scheduleFormBtnText').textContent = 'Save Schedule';
            document.getElementById('scheduleDept').disabled = false;
            document.getElementById('scheduleModal').classList.add('active');
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('active');
        }

        async function editSchedule(department) {
            try {
                const response = await fetch(`api/department_schedules.php?department=${encodeURIComponent(department)}`);
                const result = await response.json();
                
                if (result.success) {
                    const schedule = result.data;
                    
                    document.getElementById('editScheduleDept').value = department;
                    document.getElementById('scheduleDept').value = department;
                    document.getElementById('scheduleStartTime').value = schedule.start_time;
                    document.getElementById('scheduleEndTime').value = schedule.end_time;
                    document.getElementById('scheduleLateThreshold').value = schedule.late_threshold_minutes;
                    document.getElementById('scheduleDescription').value = schedule.description || '';
                    
                    document.getElementById('scheduleModalTitle').textContent = 'Edit Department Schedule';
                    document.getElementById('scheduleFormBtnText').textContent = 'Update Schedule';
                    document.getElementById('scheduleDept').disabled = true;
                    
                    document.getElementById('scheduleModal').classList.add('active');
                } else {
                    toast.error('Error loading schedule: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                toast.error('Failed to load schedule');
            }
        }

        document.getElementById('scheduleForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const editDept = document.getElementById('editScheduleDept').value;
            const isEdit = editDept !== '';
            
            const data = {
                department: document.getElementById('scheduleDept').value,
                start_time: document.getElementById('scheduleStartTime').value,
                end_time: document.getElementById('scheduleEndTime').value,
                late_threshold_minutes: document.getElementById('scheduleLateThreshold').value,
                description: document.getElementById('scheduleDescription').value
            };

            try {
                const response = await fetch('api/department_schedules.php', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    toast.success(isEdit ? 'Schedule updated successfully!' : 'Schedule created successfully!');
                    closeScheduleModal();
                    this.reset();
                    loadSchedules();
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert(isEdit ? 'Failed to update schedule' : 'Failed to create schedule');
            }
        });

        // ===================================
        // COURSE EDIT/DELETE FUNCTIONS
        // ===================================

        async function editCourse(id) {
            try {
                const response = await fetch(`api/courses.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const course = result.data;
                    
                    document.getElementById('editCourseId').value = course.id;
                    document.getElementById('courseCode').value = course.course_code;
                    document.getElementById('courseName').value = course.course_name;
                    document.getElementById('courseDept').value = course.department;
                    document.getElementById('courseSemester').value = course.semester;
                    document.getElementById('courseCredits').value = course.credits;
                    document.getElementById('courseStatus').value = course.status;
                    
                    document.getElementById('courseModalTitle').textContent = 'Edit Course';
                    document.getElementById('courseFormBtnText').textContent = 'Update Course';
                    document.getElementById('courseCode').readOnly = true;
                    
                    document.getElementById('courseModal').classList.add('active');
                } else {
                    alert('Error loading course: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load course data');
            }
        }

        function openCourseModal() {
            document.getElementById('courseForm').reset();
            document.getElementById('editCourseId').value = '';
            document.getElementById('courseModalTitle').textContent = 'Add Course';
            document.getElementById('courseFormBtnText').textContent = 'Create Course';
            document.getElementById('courseCode').readOnly = false;
            document.getElementById('courseModal').classList.add('active');
        }

        function closeCourseModal() {
            document.getElementById('courseModal').classList.remove('active');
        }

        document.getElementById('courseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const editCourseId = document.getElementById('editCourseId').value;
            const isEdit = editCourseId !== '';
            
            const data = {
                course_code: document.getElementById('courseCode').value,
                course_name: document.getElementById('courseName').value,
                department: document.getElementById('courseDept').value,
                semester: document.getElementById('courseSemester').value,
                credits: document.getElementById('courseCredits').value,
                status: document.getElementById('courseStatus').value
            };
            
            if (isEdit) {
                data.id = editCourseId;
            }

            try {
                const response = await fetch('api/courses.php', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    alert(isEdit ? 'Course updated successfully!' : 'Course created successfully!');
                    closeCourseModal();
                    this.reset();
                    loadCourses();
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert(isEdit ? 'Failed to update course' : 'Failed to create course');
            }
        });

        async function deleteCourse(id) {
            if (!confirm('⚠️ WARNING: This will PERMANENTLY DELETE the course and all related data!\n\nThis action CANNOT be undone!\n\nAre you sure?')) {
                return;
            }

            try {
                const response = await fetch('api/courses.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('✅ Course permanently deleted from database!');
                    loadCourses();
                } else {
                    toast.error('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete course');
            }
        }
    </script>
</body>
</html>

