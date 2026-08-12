<?php
/**
 * Login Page
 * College Face Recognition Attendance System
 */
require_once 'config/auth.php';

// Already logged in — redirect to appropriate dashboard
if (isLoggedIn()) {
    $user = currentUser();
    $role = strtolower($user['role']);
    if ($role === 'admin' || $role === 'hod') {
        header('Location: admin.php'); exit;
    } elseif ($role === 'professor') {
        header('Location: professor_dashboard.php'); exit;
    } else {
        header('Location: student_portal.php'); exit;
    }
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $user = loginUser($email, $password);
        if ($user) {
            createSession($user);
            require_once 'config/helpers.php';
            logActivity($user['id'], 'login', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            // Redirect to requested page or role default
            $role = strtolower($user['role_name']);
            if (!empty($redirect) && strpos($redirect, BASE_PATH . '/') === 0) {
                header('Location: ' . $redirect); exit;
            }
            if ($role === 'admin' || $role === 'hod') {
                header('Location: admin.php'); exit;
            } elseif ($role === 'professor') {
                header('Location: professor_dashboard.php'); exit;
            } else {
                header('Location: student_portal.php'); exit;
            }
        } else {
            $error = 'Invalid email or password, or your account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - College Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="mark"><i class="fas fa-graduation-cap"></i></div>
            <h1>Attendance System</h1>
            <p>College Face Recognition</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="demo-roles">
            <p class="demo-roles-label">Quick fill demo account</p>
            <div class="demo-roles-grid">
                <button type="button" class="demo-role-btn" data-email="admin@college.edu" data-password="admin123">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin</span>
                </button>
                <button type="button" class="demo-role-btn" data-email="sarah.johnson@college.edu" data-password="admin123">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Professor</span>
                </button>
                <button type="button" class="demo-role-btn" data-email="john.smith@student.college.edu" data-password="admin123">
                    <i class="fas fa-user-graduate"></i>
                    <span>Student</span>
                </button>
            </div>
        </div>

        <form method="POST" action="login.php<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@college.edu"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        </form>

        <div class="login-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <style>
        /* Critical auth layout — keeps login usable even if style.css fails to load */
        * { box-sizing: border-box; }
        body.auth-page {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            font-family: "DM Sans", "Segoe UI", sans-serif;
            color: #ffffff;
            background: #0e0e0e;
        }
        body.auth-page::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.55;
            background-image:
                linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 64px 64px;
        }
        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            background: #141414;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 0;
            padding: 2.25rem 1.85rem;
            box-shadow: none;
        }
        .auth-logo { text-align: center; margin-bottom: 1.5rem; }
        .auth-logo .mark {
            width: 3rem; height: 3rem; margin: 0 auto 0.85rem;
            border-radius: 0; display: grid; place-items: center;
            color: #0e0e0e; font-size: 1.25rem;
            background: #ffffff;
        }
        .auth-logo h1 {
            margin: 0;
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: 2rem; font-weight: 600; color: #ffffff;
        }
        .auth-logo p { margin: 0.25rem 0 0; color: #8a8a8a; font-size: 0.9rem; }
        .auth-card .form-group { margin-bottom: 1rem; }
        .auth-card .form-group label {
            display: block; font-size: 0.82rem; font-weight: 600;
            margin-bottom: 0.4rem; color: #d4d4d4;
        }
        .auth-card .form-group input {
            display: block; width: 100%;
            padding: 0.72rem 0.9rem;
            border: 1px solid rgba(255,255,255,0.18); border-radius: 0;
            font-size: 0.95rem; outline: none; background: #0a0a0a; color: #fff;
            font-family: inherit;
        }
        .auth-card .form-group input:focus {
            border-color: rgba(255,255,255,0.5);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.18);
        }
        .btn-login {
            display: block; width: 100%;
            padding: 0.85rem 1rem; margin-top: 0.35rem;
            background: #ffffff;
            color: #0e0e0e; border: 1px solid #ffffff; border-radius: 0;
            font-size: 0.95rem; font-weight: 700; cursor: pointer;
            font-family: inherit; letter-spacing: 0.04em; text-transform: uppercase;
        }
        .login-footer {
            text-align: center; margin-top: 1.2rem;
            font-size: 0.88rem; color: #8a8a8a;
        }
        .login-footer a { color: #ffffff; text-decoration: underline; font-weight: 600; }
        .demo-roles {
            margin-bottom: 1.25rem;
            padding-bottom: 1.15rem;
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }
        .demo-roles-label {
            text-align: center;
            font-size: 0.72rem;
            font-weight: 600;
            color: #8a8a8a;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.65rem;
        }
        .demo-roles-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
        .demo-role-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            padding: 0.7rem 0.4rem;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 0;
            background: transparent;
            color: #d4d4d4;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
        }
        .demo-role-btn i { font-size: 1rem; color: #ffffff; }
        .demo-role-btn:hover {
            border-color: #ffffff;
            background: rgba(255,255,255,0.05);
        }
        .demo-role-btn.active {
            border-color: #ffffff;
            background: #ffffff;
            color: #0e0e0e;
        }
        .demo-role-btn.active i { color: #0e0e0e; }
    </style>
    <script>
        document.querySelectorAll('.demo-role-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('email').value = btn.dataset.email || '';
                document.getElementById('password').value = btn.dataset.password || '';
                document.querySelectorAll('.demo-role-btn').forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                document.getElementById('password').focus();
            });
        });
    </script>
</body>
</html>
