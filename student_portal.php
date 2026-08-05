<?php
/**
 * Student Portal
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';
require_once 'config/database.php';
require_once 'config/helpers.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Attendance System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li><a href="face_recognition.php"><i class="fas fa-camera"></i> Mark Attendance</a></li>

                    <li><a href="/attendance3/logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Page Title -->
            <div class="page-title">
                <h1><i class="fas fa-user-graduate"></i> Student Portal</h1>
                <p>View your attendance records and manage leave requests</p>
            </div>

            <!-- Student ID Input -->
            <div class="content-card" style="max-width: 600px; margin: 0 auto 2rem;">
                <div style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label class="form-label">
                            <i class="fas fa-id-card"></i> Enter Your Student ID
                        </label>
                        <input type="text" class="form-control" id="studentId" placeholder="e.g., STU2024001">
                    </div>
                    <button id="loadDataBtn" class="btn btn-primary">
                        <i class="fas fa-search"></i> Load Data
                    </button>
                </div>
            </div>

            <!-- Student Data Container -->
            <div id="studentDataContainer" style="display: none;">
                <!-- Student Profile Card -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user"></i> Student Profile
                        </h2>
                    </div>
                    <div id="studentProfile"></div>
                </div>

                <!-- Attendance Overview -->
                <div class="stats-grid">
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div>
                                <p class="stat-title">Overall Attendance</p>
                                <h2 class="stat-value" id="attendancePercentage">0%</h2>
                                <p class="stat-change" id="attendanceStatus"></p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <p class="stat-title">Total Days</p>
                                <h2 class="stat-value" id="totalDays">0</h2>
                                <p class="stat-change">This Semester</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div>
                                <p class="stat-title">Late Arrivals</p>
                                <h2 class="stat-value" id="lateDays">0</h2>
                                <p class="stat-change">Times Late</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div>
                                <p class="stat-title">Absences</p>
                                <h2 class="stat-value" id="absentDays">0</h2>
                                <p class="stat-change">Days Absent</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <!-- Attendance Chart -->
                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chart-pie"></i> Attendance Breakdown
                            </h2>
                        </div>
                        <canvas id="attendanceChart"></canvas>
                    </div>

                    <!-- Course-wise Attendance -->
                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-book"></i> Course-wise Attendance
                            </h2>
                        </div>
                        <div id="courseAttendance"></div>
                    </div>
                </div>

                <!-- Recent Attendance Records -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history"></i> Recent Attendance Records
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                    <th>Late By</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceRecords"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Leave Requests Section -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-alt"></i> Leave Requests
                        </h2>
                        <button class="btn btn-primary" onclick="openLeaveModal()">
                            <i class="fas fa-plus"></i> Apply for Leave
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="leaveRequests"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leave Application Modal -->
            <div id="leaveModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Apply for Leave</h2>
                        <span class="close-modal" onclick="closeLeaveModal()">&times;</span>
                    </div>
                    <form id="leaveForm">
                        <div class="form-group">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" id="leaveType" required>
                                <option value="">Select Type</option>
                                <option value="sick">Sick Leave</option>
                                <option value="personal">Personal</option>
                                <option value="emergency">Emergency</option>
                                <option value="medical">Medical</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentStudentId = null;
        let attendanceChart = null;

        document.getElementById('loadDataBtn').addEventListener('click', loadStudentData);
        document.getElementById('leaveForm').addEventListener('submit', submitLeaveRequest);

        async function loadStudentData() {
            const studentId = document.getElementById('studentId').value.trim();
            if (!studentId) {
                alert('Please enter a student ID');
                return;
            }

            currentStudentId = studentId;

            try {
                const response = await fetch(`api/stats.php?user_id=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    displayStudentProfile(data.user);
                    displayAttendanceStats(data.attendance);
                    displayRecentAttendance(data.recent_attendance);
                    displayCourses(data.courses);
                    displayLeaveRequests(data.leave_requests);
                    createAttendanceChart(data.attendance);
                    
                    document.getElementById('studentDataContainer').style.display = 'block';
                } else {
                    alert('Student not found: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load student data');
            }
        }

        function displayStudentProfile(user) {
            document.getElementById('studentProfile').innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div><strong>Student ID:</strong> ${user.user_id}</div>
                    <div><strong>Name:</strong> ${user.name}</div>
                    <div><strong>Email:</strong> ${user.email}</div>
                    <div><strong>Department:</strong> ${user.department}</div>
                    <div><strong>Course:</strong> ${user.course}</div>
                    <div><strong>Semester:</strong> ${user.semester}</div>
                </div>
            `;
        }

        function displayAttendanceStats(attendance) {
            document.getElementById('attendancePercentage').textContent = attendance.percentage + '%';
            document.getElementById('totalDays').textContent = attendance.total_days;
            document.getElementById('lateDays').textContent = attendance.late;
            document.getElementById('absentDays').textContent = attendance.absent;

            const statusDiv = document.getElementById('attendanceStatus');
            const percentage = attendance.percentage;
            
            if (percentage >= 85) {
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Excellent';
                statusDiv.className = 'stat-change positive';
            } else if (percentage >= 75) {
                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Warning';
                statusDiv.className = 'stat-change';
                statusDiv.style.color = '#f59e0b';
            } else {
                statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> Critical';
                statusDiv.className = 'stat-change negative';
            }

            // Update stat card colors
            const statCard = document.querySelector('.stat-card.success');
            if (percentage >= 85) {
                statCard.className = 'stat-card success';
            } else if (percentage >= 75) {
                statCard.className = 'stat-card warning';
            } else {
                statCard.className = 'stat-card danger';
            }
        }

        function displayRecentAttendance(records) {
            const tbody = document.getElementById('attendanceRecords');
            tbody.innerHTML = '';

            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No attendance records found</td></tr>';
                return;
            }

            records.forEach(record => {
                const date = new Date(record.date);
                const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                const statusClass = record.status === 'present' ? 'success' : 
                                  record.status === 'late' ? 'warning' : 
                                  record.status === 'excused' ? 'info' : 'danger';

                tbody.innerHTML += `
                    <tr>
                        <td>${record.date}</td>
                        <td>${dayName}</td>
                        <td>${record.time_in || '-'}</td>
                        <td>${record.time_out || '-'}</td>
                        <td><span class="badge badge-${statusClass}">${record.status}</span></td>
                        <td>${record.is_late ? record.minutes_late + ' min' : '-'}</td>
                    </tr>
                `;
            });
        }

        function displayCourses(courses) {
            const container = document.getElementById('courseAttendance');
            container.innerHTML = '';

            if (courses.length === 0) {
                container.innerHTML = '<p style="text-align: center; padding: 2rem;">No courses enrolled</p>';
                return;
            }

            courses.forEach(course => {
                const percentage = course.attendance_percentage || 0;
                const totalLectures = course.total_lectures || 0;
                const attendedLectures = course.attended_lectures || 0;
                const colorClass = percentage >= 85 ? 'success' : percentage >= 75 ? 'warning' : 'danger';
                
                container.innerHTML += `
                    <div style="margin-bottom: 1.5rem;">
                        <div class="progress-label">
                            <span><strong>${course.course_code}</strong></span>
                            <span><strong>${percentage.toFixed(1)}%</strong> (${attendedLectures}/${totalLectures})</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill ${colorClass}" style="width: ${percentage}%"></div>
                        </div>
                        <small style="color: #6b7280;">${course.course_name} | ${course.credits} Credits</small>
                    </div>
                `;
            });
        }

        function displayLeaveRequests(requests) {
            const tbody = document.getElementById('leaveRequests');
            tbody.innerHTML = '';

            if (requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No leave requests</td></tr>';
                return;
            }

            requests.forEach(request => {
                const statusClass = request.status === 'approved' ? 'success' : 
                                  request.status === 'pending' ? 'warning' : 'danger';
                const days = Math.ceil((new Date(request.end_date) - new Date(request.start_date)) / (1000 * 60 * 60 * 24)) + 1;

                tbody.innerHTML += `
                    <tr>
                        <td>${request.leave_type}</td>
                        <td>${request.start_date}</td>
                        <td>${request.end_date}</td>
                        <td>${days}</td>
                        <td><span class="badge badge-${statusClass}">${request.status}</span></td>
                        <td>${request.reason.substring(0, 50)}...</td>
                    </tr>
                `;
            });
        }

        function createAttendanceChart(attendance) {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            if (attendanceChart) {
                attendanceChart.destroy();
            }

            attendanceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Late', 'Absent', 'Excused'],
                    datasets: [{
                        data: [attendance.present, attendance.late, attendance.absent, attendance.excused],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function openLeaveModal() {
            if (!currentStudentId) {
                alert('Please load your student data first');
                return;
            }
            document.getElementById('leaveModal').classList.add('active');
        }

        function closeLeaveModal() {
            document.getElementById('leaveModal').classList.remove('active');
        }

        async function submitLeaveRequest(e) {
            e.preventDefault();

            const data = {
                user_id: currentStudentId,
                leave_type: document.getElementById('leaveType').value,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                reason: document.getElementById('reason').value
            };

            try {
                const response = await fetch('api/leave_requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Leave request submitted successfully!');
                    closeLeaveModal();
                    document.getElementById('leaveForm').reset();
                    loadStudentData();
                } else {
                    alert('Failed to submit: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to submit leave request');
            }
        }
    </script>
</body>
</html>

