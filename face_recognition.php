<?php
/**
 * Face Recognition Attendance Marking
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition - Attendance System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .video-wrapper {
            position: relative;
            max-width: 640px;
            margin: 0 auto;
        }
        
        #video {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
        
        .detection-box {
            position: absolute;
            border: 3px solid #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
            border-radius: 8px;
        }
        
        .detection-label {
            position: absolute;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
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
                <h1><i class="fas fa-camera"></i> Face Recognition Attendance</h1>
                <p>Look at the camera to mark your attendance</p>
            </div>

            <!-- Face Recognition Container -->
            <div class="face-recognition-container">
                <div class="video-wrapper">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="canvas"></canvas>
                </div>

                <!-- Status Messages -->
                <div id="status" class="recognition-status info">
                    <i class="fas fa-info-circle"></i> Initializing camera...
                </div>

                <!-- Controls -->
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <button id="startBtn" class="btn btn-primary">
                        <i class="fas fa-play"></i> Start Recognition
                    </button>
                    <button id="stopBtn" class="btn btn-danger" style="display: none;">
                        <i class="fas fa-stop"></i> Stop
                    </button>
                    <button id="batchModeBtn" class="btn btn-outline">
                        <i class="fas fa-users"></i> Batch Mode
                    </button>
                </div>

                <!-- Recognition Results -->
                <div id="results" style="margin-top: 2rem;"></div>
            </div>

            <!-- Instructions -->
            <div class="content-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i> Instructions
                    </h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <h4><i class="fas fa-check-circle" style="color: var(--success-color);"></i> Position Yourself</h4>
                        <p>Face the camera directly and ensure your face is clearly visible.</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-lightbulb" style="color: var(--warning-color);"></i> Good Lighting</h4>
                        <p>Make sure you have adequate lighting on your face.</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-glasses" style="color: var(--info-color);"></i> Remove Obstructions</h4>
                        <p>Remove masks, sunglasses, or anything covering your face.</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-smile" style="color: var(--primary-color);"></i> Stay Still</h4>
                        <p>Keep your head still for a moment while being recognized.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- face-api.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        let video, canvas, ctx;
        let isRecognizing = false;
        let isBatchMode = false;
        let recognizedToday = new Set();
        let modelsLoaded = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', async function() {
            video = document.getElementById('video');
            canvas = document.getElementById('canvas');
            ctx = canvas.getContext('2d');
            
            await loadModels();
            await startVideo();
            
            document.getElementById('startBtn').addEventListener('click', startRecognition);
            document.getElementById('stopBtn').addEventListener('click', stopRecognition);
            document.getElementById('batchModeBtn').addEventListener('click', toggleBatchMode);
        });

        // Load face-api models
        async function loadModels() {
            updateStatus('info', 'Loading face recognition models...');
            
            try {
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model')
                ]);
                
                modelsLoaded = true;
                updateStatus('success', 'Models loaded successfully! Ready to recognize faces.');
            } catch (error) {
                console.error('Error loading models:', error);
                updateStatus('error', 'Failed to load face recognition models. Please refresh the page.');
            }
        }

        // Start video stream
        async function startVideo() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: 640, 
                        height: 480,
                        facingMode: 'user'
                    } 
                });
                
                video.srcObject = stream;
                
                video.addEventListener('loadedmetadata', () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                });
                
                updateStatus('success', 'Camera ready! Click "Start Recognition" to begin.');
            } catch (error) {
                console.error('Error starting video:', error);
                updateStatus('error', 'Failed to access camera. Please grant camera permissions.');
            }
        }

        // Start recognition
        function startRecognition() {
            if (!modelsLoaded) {
                updateStatus('error', 'Models are still loading. Please wait...');
                return;
            }
            
            isRecognizing = true;
            document.getElementById('startBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'inline-flex';
            updateStatus('info', 'Recognition active. Look at the camera...');
            
            recognizeLoop();
        }

        // Stop recognition
        function stopRecognition() {
            isRecognizing = false;
            document.getElementById('startBtn').style.display = 'inline-flex';
            document.getElementById('stopBtn').style.display = 'none';
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            updateStatus('info', 'Recognition stopped.');
        }

        // Toggle batch mode
        function toggleBatchMode() {
            isBatchMode = !isBatchMode;
            const btn = document.getElementById('batchModeBtn');
            
            if (isBatchMode) {
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="fas fa-check"></i> Batch Mode Active';
                updateStatus('info', 'Batch mode enabled. Multiple students can be recognized continuously.');
            } else {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline');
                btn.innerHTML = '<i class="fas fa-users"></i> Batch Mode';
                updateStatus('info', 'Batch mode disabled.');
            }
        }

        // Main recognition loop
        async function recognizeLoop() {
            if (!isRecognizing) return;
            
            try {
                // Detect faces
                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptors();
                
                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                if (detections.length > 0) {
                    updateStatus('info', `${detections.length} face(s) detected. Identifying...`);
                    
                    // Process first face
                    const detection = detections[0];
                    const descriptor = Array.from(detection.descriptor);
                    
                    // Draw detection box
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#10b981';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    
                    // Send to API for recognition
                    await recognizeFace(descriptor);
                    
                } else {
                    updateStatus('info', 'No face detected. Please position yourself in front of the camera.');
                }
                
            } catch (error) {
                console.error('Recognition error:', error);
                updateStatus('error', 'Recognition error: ' + error.message);
            }
            
            // Continue loop
            if (isRecognizing) {
                setTimeout(recognizeLoop, isBatchMode ? 2000 : 1000);
            }
        }

        // Recognize face via API
        async function recognizeFace(descriptor) {
            try {
                const response = await fetch('api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        face_descriptor: JSON.stringify(descriptor)
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    
                    // Check if already recognized today in batch mode
                    if (isBatchMode && recognizedToday.has(data.user_id)) {
                        updateStatus('info', `${data.name} already marked today. Scanning for next student...`);
                        return;
                    }
                    
                    // Add to recognized list
                    recognizedToday.add(data.user_id);
                    
                    // Show success
                    updateStatus('success', 
                        `✓ Attendance marked for ${data.name} (${data.user_id})<br>` +
                        `Status: ${data.status.toUpperCase()} | ` +
                        `Time: ${data.time} | ` +
                        `Confidence: ${data.confidence}%`
                    );
                    
                    // Add to results
                    addResult(data);
                    
                    // Play success sound (optional)
                    playSound('success');
                    
                    // Stop if not in batch mode
                    if (!isBatchMode) {
                        setTimeout(() => {
                            stopRecognition();
                        }, 3000);
                    }
                    
                } else {
                    updateStatus('error', result.error || 'Face not recognized. Please register first.');
                }
                
            } catch (error) {
                console.error('API error:', error);
                updateStatus('error', 'Connection error. Please check your internet connection.');
            }
        }

        // Add result to display
        function addResult(data) {
            const resultsDiv = document.getElementById('results');
            const resultCard = document.createElement('div');
            resultCard.className = 'content-card';
            resultCard.style.animation = 'fadeInUp 0.5s ease';
            
            const statusClass = data.status === 'late' ? 'warning' : 'success';
            
            resultCard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3><i class="fas fa-user-check"></i> ${data.name}</h3>
                        <p><strong>ID:</strong> ${data.user_id} | <strong>Dept:</strong> ${data.department}</p>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-${statusClass}">${data.status}</span>
                        <p><strong>${data.time}</strong></p>
                        <small>Confidence: ${data.confidence}%</small>
                    </div>
                </div>
            `;
            
            resultsDiv.insertBefore(resultCard, resultsDiv.firstChild);
            
            // Keep only last 5 results
            while (resultsDiv.children.length > 5) {
                resultsDiv.removeChild(resultsDiv.lastChild);
            }
        }

        // Update status message
        function updateStatus(type, message) {
            const statusDiv = document.getElementById('status');
            statusDiv.className = `recognition-status ${type}`;
            
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'info': 'info-circle',
                'warning': 'exclamation-triangle'
            };
            
            statusDiv.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${message}`;
        }

        // Play sound notification
        function playSound(type) {
            try {
                // Create audio context for beep sounds
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                // Different frequencies for different notification types
                if (type === 'success') {
                    oscillator.frequency.value = 800; // High beep for success
                    gainNode.gain.value = 0.3;
                    oscillator.start();
                    oscillator.stop(audioContext.currentTime + 0.2);
                } else if (type === 'error') {
                    oscillator.frequency.value = 200; // Low beep for error
                    gainNode.gain.value = 0.3;
                    oscillator.start();
                    oscillator.stop(audioContext.currentTime + 0.3);
                }
            } catch (e) {
                // Silent fail if audio not supported
                console.log('Audio notification not supported');
            }
        }
    </script>
</body>
</html>

