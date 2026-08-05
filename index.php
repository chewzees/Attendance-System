<?php
/**
 * Main Dashboard
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';
require_once 'config/database.php';
require_once 'config/helpers.php';
requireLogin();


// Get system stats
try {
    $db = getDB();
    $today = date('Y-m-d');
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active' AND role_id = 1");
    $totalStudents = $stmt->fetch()['total'];
    
    // Today's attendance
    $stmt = $db->prepare("SELECT COUNT(*) as total, 
                          SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                          SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                          FROM attendance WHERE date = ?");
    $stmt->execute([$today]);
    $todayStats = $stmt->fetch();
    
    // Active courses
    $stmt = $db->query("SELECT COUNT(*) as total FROM courses WHERE status = 'active'");
    $activeCourses = $stmt->fetch()['total'];
    
    // Pending leaves
    $stmt = $db->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
    $pendingLeaves = $stmt->fetch()['total'];
    
    // Attendance percentage today
    $todayPercentage = $totalStudents > 0 ? round(($todayStats['total'] / $totalStudents) * 100, 2) : 0;
    
    // Recent activities
    $stmt = $db->query("
        SELECT al.action, al.details, al.created_at, u.name, u.user_id
        FROM attendance_logs al
        JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();
    
    // Top courses with enrollment
    $stmt = $db->query("
        SELECT c.course_code, c.course_name, c.department,
               COUNT(ce.id) as student_count
        FROM courses c
        LEFT JOIN course_enrollment ce ON c.id = ce.course_id AND ce.status = 'active'
        WHERE c.status = 'active'
        GROUP BY c.id
        ORDER BY student_count DESC
        LIMIT 5
    ");
    $topCourses = $stmt->fetchAll();
    
} catch (Exception $e) {
    $totalStudents = 0;
    $todayStats = ['total' => 0, 'present' => 0, 'late' => 0];
    $activeCourses = 0;
    $pendingLeaves = 0;
    $todayPercentage = 0;
    $recentActivities = [];
    $topCourses = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - College Attendance System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <li><a href="face_recognition.php"><i class="fas fa-camera"></i> Mark Attendance</a></li>

                    <li><a href="/attendance3/logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Dashboard -->
    <main class="dashboard">
        <div class="container">
            <!-- Page Title -->
            <div class="page-title">
                <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
                <p>Real-time attendance tracking and analytics</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Total Students -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <p class="stat-title">Total Students</p>
                            <h2 class="stat-value"><?php echo number_format($totalStudents); ?></h2>
                            <p class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> Active Users
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Today's Attendance -->
                <div class="stat-card success">
                    <div class="stat-header">
                        <div>
                            <p class="stat-title">Today's Attendance</p>
                            <h2 class="stat-value"><?php echo $todayStats['total']; ?></h2>
                            <p class="stat-change positive">
                                <i class="fas fa-percentage"></i> <?php echo $todayPercentage; ?>%
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <!-- Active Courses -->
                <div class="stat-card warning">
                    <div class="stat-header">
                        <div>
                            <p class="stat-title">Active Courses</p>
                            <h2 class="stat-value"><?php echo $activeCourses; ?></h2>
                            <p class="stat-change">
                                <i class="fas fa-book"></i> This Semester
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Leaves -->
                <div class="stat-card danger">
                    <div class="stat-header">
                        <div>
                            <p class="stat-title">Pending Leaves</p>
                            <h2 class="stat-value"><?php echo $pendingLeaves; ?></h2>
                            <p class="stat-change">
                                <i class="fas fa-clock"></i> Awaiting Review
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Recent Activities -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history"></i> Recent Activities
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <?php if (!empty($recentActivities)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recentActivities, 0, 5) as $activity): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($activity['name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($activity['user_id']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                            <td><?php echo date('h:i A', strtotime($activity['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; padding: 2rem; color: #6b7280;">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Top Courses -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-star"></i> Top Courses
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <?php if (!empty($topCourses)): ?>
                            <?php foreach ($topCourses as $course): ?>
                                <div style="margin-bottom: 1.5rem;">
                                    <div class="progress-label">
                                        <span><strong><?php echo htmlspecialchars($course['course_code']); ?></strong> - <?php echo htmlspecialchars($course['course_name']); ?></span>
                                        <span><strong><?php echo $course['student_count']; ?></strong> students</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill success" style="width: <?php echo min(100, ($course['student_count'] / max($totalStudents, 1)) * 100); ?>%"></div>
                                    </div>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($course['department']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; padding: 2rem; color: #6b7280;">No courses available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Attendance Charts Section -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Attendance State Chart (Pie/Doughnut) -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie"></i> Attendance Status Breakdown
                        </h2>
                        <div class="chart-controls">
                            <button class="btn btn-sm active" onclick="loadAttendanceChart('today')" id="chartToday">Today</button>
                            <button class="btn btn-sm" onclick="loadAttendanceChart('week')" id="chartWeek">Week</button>
                            <button class="btn btn-sm" onclick="loadAttendanceChart('month')" id="chartMonth">Month</button>
                            <button class="btn btn-sm" onclick="loadAttendanceChart('all')" id="chartAll">All Time</button>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <canvas id="attendanceStateChart"></canvas>
                    </div>
                </div>

                <!-- Attendance Trend Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-line"></i> Attendance Trend
                        </h2>
                        <div class="chart-controls">
                            <button class="btn btn-sm active" onclick="loadTrendChart('week')" id="trendWeek">Last 7 Days</button>
                            <button class="btn btn-sm" onclick="loadTrendChart('month')" id="trendMonth">Last 30 Days</button>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Department-wise Attendance Chart -->
            <div class="content-card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-chart-bar"></i> Department-wise Attendance
                    </h2>
                </div>
                <div style="padding: 1rem;">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="face_recognition.php" class="btn btn-primary">
                        <i class="fas fa-camera"></i> Mark Attendance
                    </a>
                    <a href="register.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Register Student
                    </a>
                    <a href="student_portal.php" class="btn btn-outline">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                    <a href="admin.php" class="btn btn-warning">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>

            <!-- System Information -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i> System Information
                    </h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <p><strong>System Version:</strong> <?php echo getSetting('system_version', '2.0'); ?></p>
                        <p><strong>Recognition Model:</strong> <?php echo getSetting('face_recognition_model', 'SSD MobileNetV1'); ?></p>
                    </div>
                    <div>
                        <p><strong>Current Date:</strong> <?php echo date('l, F d, Y'); ?></p>
                        <p><strong>Academic Year:</strong> <?php echo getCurrentAcademicYear(); ?></p>
                    </div>
                    <div>
                        <p><strong>Attendance Threshold:</strong> <?php echo getSetting('minimum_attendance_percentage', 75); ?>%</p>
                        <p><strong>Late Threshold:</strong> <?php echo getSetting('late_threshold_minutes', 15); ?> minutes</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/utils.js"></script>
    
    <script>
        // Chart instances
        let attendanceStateChart = null;
        let attendanceTrendChart = null;
        let departmentChart = null;
        
        // Chart colors
        const chartColors = {
            present: '#10b981',
            late: '#f59e0b',
            absent: '#ef4444',
            excused: '#6366f1',
            background: 'rgba(99, 102, 241, 0.1)'
        };

        // Load attendance state chart (Pie/Doughnut)
        async function loadAttendanceChart(period = 'today') {
            try {
                const response = await fetch(`api/stats.php`, { credentials: 'same-origin' });
                if (!response.ok) { throw new Error(`HTTP ${response.status}`); }
                const result = await response.json();
                
                if (!result.success) {
                    console.error('Failed to load stats:', result.error);
                    return;
                }

                let data = {
                    present: 0,
                    late: 0,
                    absent: 0,
                    excused: 0
                };

                if (period === 'today') {
                    data = result.data.today_attendance || {};
                } else if (period === 'week') {
                    // Sum up weekly data
                    const weekData = result.data.weekly_trend || [];
                    weekData.forEach(day => {
                        data.present += parseInt(day.present || 0);
                        data.late += parseInt(day.late || 0);
                        data.absent += parseInt(day.absent || 0);
                    });
                } else if (period === 'month') {
                    // Sum up monthly data
                    const monthData = result.data.monthly_trend || [];
                    monthData.forEach(day => {
                        data.present += parseInt(day.present || 0);
                        data.late += parseInt(day.late || 0);
                        data.absent += parseInt(day.absent || 0);
                    });
                } else {
                    // All time - use status breakdown
                    const breakdown = result.data.status_breakdown || [];
                    breakdown.forEach(item => {
                        if (item.status === 'present') data.present = parseInt(item.count || 0);
                        if (item.status === 'late') data.late = parseInt(item.count || 0);
                        if (item.status === 'absent') data.absent = parseInt(item.count || 0);
                        if (item.status === 'excused') data.excused = parseInt(item.count || 0);
                    });
                }

                // Update button states
                const buttonIds = {
                    'today': 'chartToday',
                    'week': 'chartWeek',
                    'month': 'chartMonth',
                    'all': 'chartAll'
                };
                document.querySelectorAll('.chart-controls button').forEach(btn => btn.classList.remove('active'));
                const activeBtn = document.getElementById(buttonIds[period]);
                if (activeBtn) activeBtn.classList.add('active');

                const ctx = document.getElementById('attendanceStateChart').getContext('2d');
                
                if (attendanceStateChart) {
                    attendanceStateChart.destroy();
                }

                attendanceStateChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Late', 'Absent', 'Excused'],
                        datasets: [{
                            data: [
                                data.present || 0,
                                data.late || 0,
                                data.absent || 0,
                                data.excused || 0
                            ],
                            backgroundColor: [
                                chartColors.present,
                                chartColors.late,
                                chartColors.absent,
                                chartColors.excused
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error loading attendance chart:', error);
                toast.error('Failed to load attendance chart');
            }
        }

        // Load attendance trend chart (Line)
        async function loadTrendChart(period = 'week') {
            try {
                const response = await fetch(`api/stats.php`, { credentials: 'same-origin' });
                if (!response.ok) { throw new Error(`HTTP ${response.status}`); }
                const result = await response.json();
                
                if (!result.success) {
                    console.error('Failed to load stats:', result.error);
                    return;
                }

                const trendData = period === 'week' 
                    ? result.data.weekly_trend || []
                    : result.data.monthly_trend || [];

                const labels = trendData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });

                // Update button states
                document.querySelectorAll('#trendWeek, #trendMonth').forEach(btn => btn.classList.remove('active'));
                document.getElementById('trend' + period.charAt(0).toUpperCase() + period.slice(1))?.classList.add('active');

                const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
                
                if (attendanceTrendChart) {
                    attendanceTrendChart.destroy();
                }

                attendanceTrendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Present',
                                data: trendData.map(item => parseInt(item.present || 0)),
                                borderColor: chartColors.present,
                                backgroundColor: chartColors.present + '20',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Late',
                                data: trendData.map(item => parseInt(item.late || 0)),
                                borderColor: chartColors.late,
                                backgroundColor: chartColors.late + '20',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Absent',
                                data: trendData.map(item => parseInt(item.absent || 0)),
                                borderColor: chartColors.absent,
                                backgroundColor: chartColors.absent + '20',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error loading trend chart:', error);
                toast.error('Failed to load trend chart');
            }
        }

        // Load department-wise chart
        async function loadDepartmentChart() {
            try {
                const response = await fetch(`api/stats.php`, { credentials: 'same-origin' });
                if (!response.ok) { throw new Error(`HTTP ${response.status}`); }
                const result = await response.json();
                
                if (!result.success) {
                    console.error('Failed to load stats:', result.error);
                    return;
                }

                const deptData = result.data.department_stats || [];
                
                const labels = deptData.map(item => item.department || 'Unknown');
                const presentData = deptData.map(item => parseInt(item.present || 0));
                const lateData = deptData.map(item => parseInt(item.late || 0));
                const absentData = deptData.map(item => parseInt(item.absent || 0));

                const ctx = document.getElementById('departmentChart').getContext('2d');
                
                if (departmentChart) {
                    departmentChart.destroy();
                }

                departmentChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Present',
                                data: presentData,
                                backgroundColor: chartColors.present,
                                borderRadius: 6
                            },
                            {
                                label: 'Late',
                                data: lateData,
                                backgroundColor: chartColors.late,
                                borderRadius: 6
                            },
                            {
                                label: 'Absent',
                                data: absentData,
                                backgroundColor: chartColors.absent,
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error loading department chart:', error);
                toast.error('Failed to load department chart');
            }
        }

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAttendanceChart('today');
            loadTrendChart('week');
            loadDepartmentChart();
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
    
    <style>
        .chart-controls {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .chart-controls .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
            border-radius: 6px;
            background: var(--light-color);
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chart-controls .btn-sm:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .chart-controls .btn-sm.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        #attendanceStateChart,
        #attendanceTrendChart,
        #departmentChart {
            max-height: 400px;
        }
    </style>
</body>
</html>

