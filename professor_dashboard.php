<?php
/**
 * Professor Dashboard
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';
require_once 'config/database.php';
require_once 'config/helpers.php';
requireRole(['admin', 'hod', 'professor']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard - Attendance System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
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
                <h1><i class="fas fa-chalkboard-teacher"></i> Professor Dashboard</h1>
                <p>Manage courses, lectures, and student attendance</p>
            </div>

            <!-- Tabs -->
            <div class="content-card">
                <div class="tab-bar">
                    <button class="btn btn-primary tab-btn active" data-tab="courses">
                        <i class="fas fa-book"></i> My Courses
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="lectures">
                        <i class="fas fa-calendar"></i> Lectures
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="attendance">
                        <i class="fas fa-check-square"></i> Mark Attendance
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="leaves">
                        <i class="fas fa-file-alt"></i> Leave Requests
                    </button>
                    <button class="btn btn-outline tab-btn" data-tab="reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </button>
                </div>

                <!-- Tab Content: My Courses -->
                <div id="coursesTab" class="tab-content active">
                    <div class="flex-between mb-3">
                        <h2 class="card-title"><i class="fas fa-book"></i> My Courses</h2>
                        <button class="btn btn-success" onclick="openCourseModal()">
                            <i class="fas fa-plus"></i> Add Course
                        </button>
                    </div>
                    <div id="coursesList"></div>
                </div>

                <!-- Tab Content: Lectures -->
                <div id="lecturesTab" class="tab-content">
                    <div class="flex-between mb-3">
                        <h2 class="card-title"><i class="fas fa-calendar"></i> Scheduled Lectures</h2>
                        <button class="btn btn-success" onclick="openLectureModal()">
                            <i class="fas fa-plus"></i> Schedule Lecture
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                    <th>Topic</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lecturesTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Mark Attendance -->
                <div id="attendanceTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-check-square"></i> Bulk Attendance Marking
                    </h2>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Select Course</label>
                            <select class="form-select" id="attendanceCourse">
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div id="studentAttendanceList"></div>
                </div>

                <!-- Tab Content: Leave Requests -->
                <div id="leavesTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-file-alt"></i> Pending Leave Requests
                    </h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leaveRequestsTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Content: Reports -->
                <div id="reportsTab" class="tab-content">
                    <h2 class="card-title" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-chart-bar"></i> Course Reports
                    </h2>
                    
                    <div class="form-group" style="max-width: 400px;">
                        <label class="form-label">Select Course for Report</label>
                        <select class="form-select" id="reportCourse" onchange="generateReport()">
                            <option value="">Select Course</option>
                        </select>
                    </div>

                    <div id="reportContent" style="margin-top: 2rem;"></div>
                </div>
            </div>

            <!-- Add Course Modal -->
            <div id="courseModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Add New Course</h2>
                        <span class="close-modal" onclick="closeCourseModal()">&times;</span>
                    </div>
                    <form id="courseForm">
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
                            <input type="number" class="form-control" id="courseCredits" value="3" min="1" max="6">
                        </div>
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-save"></i> Save Course
                        </button>
                    </form>
                </div>
            </div>

            <!-- Schedule Lecture Modal -->
            <div id="lectureModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Schedule Lecture</h2>
                        <span class="close-modal" onclick="closeLectureModal()">&times;</span>
                    </div>
                    <form id="lectureForm">
                        <div class="form-group">
                            <label class="form-label">Course</label>
                            <select class="form-select" id="lectureCourse" required></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="lectureDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" id="lectureTime" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="lectureDuration" value="60" min="30" max="180">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" id="lectureRoom">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lecture Type</label>
                            <select class="form-select" id="lectureType">
                                <option value="theory">Theory</option>
                                <option value="practical">Practical</option>
                                <option value="tutorial">Tutorial</option>
                                <option value="seminar">Seminar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Topic</label>
                            <input type="text" class="form-control" id="lectureTopic">
                        </div>
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-calendar-plus"></i> Schedule Lecture
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

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
                if (tab === 'courses') loadCourses();
                if (tab === 'lectures') loadLectures();
                if (tab === 'leaves') loadLeaveRequests();
            });
        });

        // Initial load
        loadCourses();

        // Load courses
        async function loadCourses() {
            try {
                const response = await fetch('api/courses.php');
                const result = await response.json();
                
                if (result.success) {
                    displayCourses(result.data);
                    populateCourseDropdowns(result.data);
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }

        function displayCourses(courses) {
            const container = document.getElementById('coursesList');
            
            if (courses.length === 0) {
                container.innerHTML = '<p style="text-align: center; padding: 2rem;">No courses found</p>';
                return;
            }
            
            container.innerHTML = courses.map(course => `
                <div class="content-card" style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>${course.course_code} - ${course.course_name}</h3>
                            <p><strong>Department:</strong> ${course.department} | <strong>Semester:</strong> ${course.semester} | <strong>Credits:</strong> ${course.credits}</p>
                            <p><strong>Students Enrolled:</strong> ${course.student_count}</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="viewCourseDetails(${course.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-primary" onclick="manageCourse(${course.id})">
                                <i class="fas fa-cog"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function populateCourseDropdowns(courses) {
            const options = courses.map(c => `<option value="${c.id}">${c.course_code} - ${c.course_name}</option>`).join('');
            document.getElementById('lectureCourse').innerHTML = '<option value="">Select Course</option>' + options;
            document.getElementById('attendanceCourse').innerHTML = '<option value="">Select Course</option>' + options;
            document.getElementById('reportCourse').innerHTML = '<option value="">Select Course</option>' + options;
        }

        // Load lectures
        async function loadLectures() {
            try {
                const response = await fetch('api/lectures.php');
                const result = await response.json();
                
                if (result.success) {
                    displayLectures(result.data);
                }
            } catch (error) {
                console.error('Error loading lectures:', error);
            }
        }

        function displayLectures(lectures) {
            const tbody = document.getElementById('lecturesTable');
            
            if (lectures.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No lectures scheduled</td></tr>';
                return;
            }
            
            tbody.innerHTML = lectures.map(lecture => {
                const statusClass = lecture.status === 'completed' ? 'success' : 
                                  lecture.status === 'scheduled' ? 'info' : 'danger';
                return `
                    <tr>
                        <td>${lecture.course_code}</td>
                        <td>${lecture.lecture_date}</td>
                        <td>${lecture.lecture_time}</td>
                        <td>${lecture.room_number || '-'}</td>
                        <td>${lecture.lecture_type}</td>
                        <td>${lecture.topic || '-'}</td>
                        <td><span class="badge badge-${statusClass}">${lecture.status}</span></td>
                        <td>
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem;" onclick="viewLecture(${lecture.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Load leave requests
        async function loadLeaveRequests() {
            try {
                const response = await fetch('api/leave_requests.php?status=pending');
                const result = await response.json();
                
                if (result.success) {
                    displayLeaveRequests(result.data);
                }
            } catch (error) {
                console.error('Error loading leave requests:', error);
            }
        }

        function displayLeaveRequests(requests) {
            const tbody = document.getElementById('leaveRequestsTable');
            
            if (requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No pending leave requests</td></tr>';
                return;
            }
            
            tbody.innerHTML = requests.map(req => `
                <tr>
                    <td><strong>${req.name}</strong><br><small>${req.user_id}</small></td>
                    <td>${req.leave_type}</td>
                    <td>${req.start_date}</td>
                    <td>${req.end_date}</td>
                    <td>${req.total_days}</td>
                    <td>${req.reason.substring(0, 50)}...</td>
                    <td>
                        <button class="btn btn-success" style="padding: 0.5rem;" onclick="approveLeave(${req.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-danger" style="padding: 0.5rem;" onclick="rejectLeave(${req.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Approve/Reject leave
        async function approveLeave(id) {
            if (!confirm('Approve this leave request?')) return;
            await updateLeaveStatus(id, 'approved');
        }

        async function rejectLeave(id) {
            const comments = prompt('Enter rejection reason:');
            if (!comments) return;
            await updateLeaveStatus(id, 'rejected', comments);
        }

        async function updateLeaveStatus(id, status, comments = '') {
            try {
                const response = await fetch('api/leave_requests.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, status, review_comments: comments, reviewed_by: 1 })
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Leave request ' + status);
                    loadLeaveRequests();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Modal functions
        function openCourseModal() {
            document.getElementById('courseModal').classList.add('active');
        }

        function closeCourseModal() {
            document.getElementById('courseModal').classList.remove('active');
        }

        function openLectureModal() {
            document.getElementById('lectureModal').classList.add('active');
        }

        function closeLectureModal() {
            document.getElementById('lectureModal').classList.remove('active');
        }

        // Form submissions
        document.getElementById('courseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const data = {
                course_code: document.getElementById('courseCode').value,
                course_name: document.getElementById('courseName').value,
                department: document.getElementById('courseDept').value,
                semester: document.getElementById('courseSemester').value,
                credits: document.getElementById('courseCredits').value,
                professor_id: 1 // Should come from logged-in professor
            };

            try {
                const response = await fetch('api/courses.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Course added successfully!');
                    closeCourseModal();
                    this.reset();
                    loadCourses();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to add course');
            }
        });

        document.getElementById('lectureForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const data = {
                course_id: document.getElementById('lectureCourse').value,
                lecture_date: document.getElementById('lectureDate').value,
                lecture_time: document.getElementById('lectureTime').value,
                duration: document.getElementById('lectureDuration').value,
                room_number: document.getElementById('lectureRoom').value,
                lecture_type: document.getElementById('lectureType').value,
                topic: document.getElementById('lectureTopic').value,
                created_by: 1, // Should come from logged-in professor
                auto_create_attendance: true
            };

            try {
                const response = await fetch('api/lectures.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Lecture scheduled successfully!');
                    closeLectureModal();
                    this.reset();
                    loadLectures();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to schedule lecture');
            }
        });

        // Course and Lecture Functions
        async function viewCourseDetails(id) {
            try {
                const response = await fetch(`api/courses.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const course = result.data;
                    const message = `
Course Details:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Code: ${course.course_code}
Name: ${course.course_name}
Department: ${course.department}
Semester: ${course.semester}
Credits: ${course.credits}
Students: ${course.student_count}
Professor: ${course.professor_name || 'Unassigned'}
Status: ${course.status}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                    `;
                    alert(message);
                } else {
                    alert('Error loading course: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load course details');
            }
        }

        function manageCourse(id) {
            // Redirect to admin panel with course selected
            window.location.href = `admin.php?tab=courses&course_id=${id}`;
        }

        async function viewLecture(id) {
            try {
                const response = await fetch(`api/lectures.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const lecture = result.data;
                    const attendance = lecture.attendance || [];
                    const message = `
Lecture Details:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Course: ${lecture.course_code} - ${lecture.course_name}
Date: ${lecture.lecture_date}
Time: ${lecture.lecture_time}
Duration: ${lecture.duration} minutes
Room: ${lecture.room_number || 'Not specified'}
Type: ${lecture.lecture_type}
Topic: ${lecture.topic || 'Not specified'}
Status: ${lecture.status}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Attendance: ${lecture.present_count}/${lecture.total_students} students
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                    `;
                    alert(message);
                } else {
                    alert('Error loading lecture: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load lecture details');
            }
        }

        async function generateReport() {
            const courseId = document.getElementById('reportCourse').value;
            if (!courseId) {
                alert('Please select a course');
                return;
            }
            
            document.getElementById('reportContent').innerHTML = '<p style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Generating report...</p>';
            
            try {
                const response = await fetch(`api/reports.php?type=course&course_id=${courseId}`);
                const result = await response.json();
                
                if (result.success) {
                    displayCourseReport(result.data);
                } else {
                    document.getElementById('reportContent').innerHTML = '<p style="color: red;">Error: ' + result.error + '</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('reportContent').innerHTML = '<p style="color: red;">Failed to generate report</p>';
            }
        }

        function displayCourseReport(data) {
            const course = data.course;
            const students = data.students || [];
            
            let html = `
                <div class="content-card">
                    <h3>${course.course_code} - ${course.course_name}</h3>
                    <p><strong>Department:</strong> ${course.department} | <strong>Semester:</strong> ${course.semester}</p>
                    <p><strong>Professor:</strong> ${course.professor_name || 'Unassigned'}</p>
                    <hr>
                    <h4>Student Attendance (${students.length} students)</h4>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Total Lectures</th>
                                    <th>Present</th>
                                    <th>Late</th>
                                    <th>Absent</th>
                                    <th>Attendance %</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            students.forEach(student => {
                const statusClass = student.percentage >= 85 ? 'success' : student.percentage >= 75 ? 'warning' : 'danger';
                html += `
                    <tr>
                        <td>${student.user_id}</td>
                        <td>${student.name}</td>
                        <td>${student.total_lectures}</td>
                        <td>${student.present}</td>
                        <td>${student.late}</td>
                        <td>${student.absent}</td>
                        <td><span class="badge badge-${statusClass}">${student.percentage.toFixed(1)}%</span></td>
                    </tr>
                `;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('reportContent').innerHTML = html;
        }
    </script>
</body>
</html>

