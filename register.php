<?php
/**
 * Student Registration with Face Capture
 * College Face Recognition Attendance System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student - Attendance System</title>
    
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
                <h1><i class="fas fa-user-plus"></i> Student Registration</h1>
                <p>Register new student with face recognition</p>
            </div>

            <!-- Registration Form -->
            <div class="content-card" style="max-width: 900px; margin: 0 auto;">
                <form id="registrationForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Left Column -->
                        <div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i> Student ID (Auto-Generated)
                                </label>
                                <input type="text" class="form-control" id="userId" name="user_id" readonly 
                                       placeholder="Will be auto-generated" style="background-color: #f3f4f6;">
                                <small style="color: #6b7280;">Student ID will be automatically assigned</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Full Name *
                                </label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       placeholder="Enter full name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="student@college.edu">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i> Phone
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Enter phone number">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-building"></i> Department *
                                </label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Mechanical">Mechanical</option>
                                    <option value="Civil">Civil</option>
                                    <option value="Electrical">Electrical</option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Semester *
                                </label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                    <option value="3">Semester 3</option>
                                    <option value="4">Semester 4</option>
                                    <option value="5">Semester 5</option>
                                    <option value="6">Semester 6</option>
                                    <option value="7">Semester 7</option>
                                    <option value="8">Semester 8</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i> Parent Phone
                                </label>
                                <input type="tel" class="form-control" id="parentPhone" name="parent_phone" 
                                       placeholder="Parent phone number">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Password (Optional)
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Leave blank for face-only login">
                            </div>
                        </div>
                    </div>

                    <!-- Face Capture Section -->
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--light-color);">
                        <h3 style="margin-bottom: 1rem;">
                            <i class="fas fa-camera"></i> Face Capture (Required)
                        </h3>

                        <div id="cameraSection">
                            <div class="video-wrapper" style="max-width: 500px; margin: 0 auto;">
                                <video id="video" autoplay muted playsinline style="width: 100%; border-radius: 12px;"></video>
                                <canvas id="canvas" style="display: none;"></canvas>
                            </div>

                            <div id="captureStatus" class="recognition-status info" style="margin-top: 1rem;">
                                <i class="fas fa-info-circle"></i> Position your face in the camera
                            </div>

                            <div style="text-align: center; margin-top: 1rem;">
                                <button type="button" id="captureBtn" class="btn btn-primary">
                                    <i class="fas fa-camera"></i> Capture Face
                                </button>
                                <button type="button" id="recaptureBtn" class="btn btn-warning" style="display: none;">
                                    <i class="fas fa-redo"></i> Retake
                                </button>
                            </div>

                            <div id="capturedFaces" style="margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                <!-- Captured faces will appear here -->
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div style="text-align: center; margin-top: 2rem;">
                        <button type="submit" id="submitBtn" class="btn btn-success" style="padding: 1rem 3rem; font-size: 1.1rem;" disabled>
                            <i class="fas fa-check-circle"></i> Register Student
                        </button>
                    </div>
                </form>
            </div>

            <!-- Registration Success Modal -->
            <div id="successModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Registration Successful!</h2>
                    </div>
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--success-color);"></i>
                        <h3 style="margin: 1rem 0;">Student Registered Successfully</h3>
                        <div id="successDetails"></div>
                        <button class="btn btn-primary" style="margin-top: 2rem;" onclick="registerAnotherStudent()">
                            Register Another Student
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- face-api.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Utilities -->
    <script src="assets/js/utils.js"></script>
    
    <script>
        let video, canvas, ctx;
        let faceDescriptors = [];
        let modelsLoaded = false;
        let currentStream = null; // Store stream globally

        document.addEventListener('DOMContentLoaded', async function() {
            video = document.getElementById('video');
            canvas = document.getElementById('canvas');
            ctx = canvas.getContext('2d');
            
            // Enable auto-save for form
            enableAutoSave('registrationForm', 'registration_form_draft');
            
            // Add real-time form validation
            setupFormValidation();
            
            await loadModels();
            await startCamera();
            
            document.getElementById('captureBtn').addEventListener('click', captureFace);
            document.getElementById('recaptureBtn').addEventListener('click', resetCapture);
            document.getElementById('registrationForm').addEventListener('submit', handleSubmit);
        });

        function setupFormValidation() {
            const emailField = document.getElementById('email');
            const phoneField = document.getElementById('phone');
            const parentPhoneField = document.getElementById('parentPhone');
            
            // Email validation
            emailField.addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showFieldError(this, 'Please enter a valid email address');
                } else {
                    clearFieldError(this);
                }
            });
            
            // Phone validation
            phoneField.addEventListener('blur', function() {
                if (this.value && !validatePhone(this.value)) {
                    showFieldError(this, 'Please enter a valid phone number');
                } else {
                    clearFieldError(this);
                }
            });
            
            // Parent phone validation
            if (parentPhoneField) {
                parentPhoneField.addEventListener('blur', function() {
                    if (this.value && !validatePhone(this.value)) {
                        showFieldError(this, 'Please enter a valid phone number');
                    } else {
                        clearFieldError(this);
                    }
                });
            }
        }

        async function loadModels() {
            updateCaptureStatus('info', 'Loading face recognition models...');
            
            try {
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model')
                ]);
                
                modelsLoaded = true;
                updateCaptureStatus('success', 'Ready to capture face!');
            } catch (error) {
                console.error('Error loading models:', error);
                updateCaptureStatus('error', 'Failed to load face recognition models.');
            }
        }

        async function startCamera() {
            try {
                currentStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480, facingMode: 'user' } 
                });
                video.srcObject = currentStream;
            } catch (error) {
                console.error('Error starting camera:', error);
                updateCaptureStatus('error', 'Failed to access camera.');
            }
        }

        function stopCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
                if (video.srcObject) {
                    video.srcObject = null;
                }
            }
        }

        async function captureFace() {
            if (!modelsLoaded) {
                updateCaptureStatus('error', 'Models are still loading...');
                return;
            }

            updateCaptureStatus('info', 'Detecting face...');

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.SsdMobilenetv1Options())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    updateCaptureStatus('error', 'No face detected. Please try again.');
                    return;
                }

                // Store descriptor
                const descriptor = Array.from(detection.descriptor);
                faceDescriptors.push(descriptor);

                // Capture image
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);
                const imageData = canvas.toDataURL('image/jpeg');

                // Display captured face
                const faceDiv = document.createElement('div');
                faceDiv.style.cssText = 'position: relative; width: 150px;';
                faceDiv.innerHTML = `
                    <img src="${imageData}" style="width: 100%; border-radius: 8px; border: 3px solid var(--success-color);">
                    <span class="badge badge-success" style="position: absolute; top: 5px; right: 5px;">✓ ${faceDescriptors.length}</span>
                `;
                document.getElementById('capturedFaces').appendChild(faceDiv);

                if (faceDescriptors.length >= 1) {
                    updateCaptureStatus('success', `${faceDescriptors.length} face sample(s) captured. You can submit now or capture more for better accuracy.`);
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('recaptureBtn').style.display = 'inline-flex';
                }

                if (faceDescriptors.length >= 3) {
                    document.getElementById('captureBtn').disabled = true;
                    updateCaptureStatus('success', 'Maximum 3 samples captured. Ready to register!');
                }

            } catch (error) {
                console.error('Capture error:', error);
                updateCaptureStatus('error', 'Error capturing face: ' + error.message);
            }
        }

        function resetCapture() {
            faceDescriptors = [];
            document.getElementById('capturedFaces').innerHTML = '';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('recaptureBtn').style.display = 'none';
            document.getElementById('captureBtn').disabled = false;
            updateCaptureStatus('info', 'Capture reset. Please capture your face again.');
        }

        async function handleSubmit(e) {
            e.preventDefault();

            // Validate form fields
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const department = document.getElementById('department').value;
            const semester = document.getElementById('semester').value;
            const password = document.getElementById('password').value;

            if (!name) {
                toast.warning('Please enter your full name');
                document.getElementById('name').focus();
                return;
            }

            if (!email || !validateEmail(email)) {
                toast.warning('Please enter a valid email address');
                document.getElementById('email').focus();
                return;
            }

            if (!phone || !validatePhone(phone)) {
                toast.warning('Please enter a valid phone number');
                document.getElementById('phone').focus();
                return;
            }

            if (!department) {
                toast.warning('Please select a department');
                return;
            }

            if (!semester) {
                toast.warning('Please select a semester');
                return;
            }

            if (!password || password.length < 6) {
                toast.warning('Password must be at least 6 characters long');
                document.getElementById('password').focus();
                return;
            }

            if (faceDescriptors.length === 0) {
                toast.warning('Please capture at least one face sample');
                updateCaptureStatus('error', 'Please capture at least one face sample.');
                return;
            }

            const formData = {
                // user_id will be auto-generated by the API
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                department: document.getElementById('department').value,
                semester: document.getElementById('semester').value,
                parent_phone: document.getElementById('parentPhone').value,
                password: document.getElementById('password').value,
                face_descriptor: JSON.stringify(faceDescriptors[0]),
                face_descriptor_2: faceDescriptors[1] ? JSON.stringify(faceDescriptors[1]) : null,
                face_descriptor_3: faceDescriptors[2] ? JSON.stringify(faceDescriptors[2]) : null
            };

            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
            document.getElementById('submitBtn').disabled = true;

            try {
                const response = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    toast.success('Registration successful!');
                    document.getElementById('successDetails').innerHTML = `
                        <p><strong>Student ID:</strong> ${result.data.user_id}</p>
                        <p><strong>Name:</strong> ${result.data.name}</p>
                        <p><strong>Email:</strong> ${result.data.email}</p>
                        <p><strong>Parent Code:</strong> <span class="badge badge-info">${result.data.parent_code}</span></p>
                    `;
                    document.getElementById('successModal').classList.add('active');
                } else {
                    toast.error('Registration failed: ' + result.error);
                    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check-circle"></i> Register Student';
                    document.getElementById('submitBtn').disabled = false;
                }

            } catch (error) {
                console.error('Registration error:', error);
                toast.error('Registration failed. Please try again.');
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check-circle"></i> Register Student';
                document.getElementById('submitBtn').disabled = false;
            }
        }

        function updateCaptureStatus(type, message) {
            const statusDiv = document.getElementById('captureStatus');
            statusDiv.className = `recognition-status ${type}`;
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'info': 'info-circle'
            };
            statusDiv.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${message}`;
        }

        function registerAnotherStudent() {
            // Close success modal
            document.getElementById('successModal').classList.remove('active');
            
            // Reset form
            document.getElementById('registrationForm').reset();
            
            // Reset face captures
            faceDescriptors = [];
            
            // Clear captured faces display
            document.getElementById('capturedFaces').innerHTML = '';
            
            // Reset UI elements
            document.getElementById('captureBtn').disabled = false;
            document.getElementById('recaptureBtn').style.display = 'none';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check-circle"></i> Register Student';
            
            // Restart camera for next capture
            stopCamera();
            setTimeout(() => {
                startCamera();
            }, 100);
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            updateCaptureStatus('info', 'Ready to register new student');
        }
    </script>
</body>
</html>

