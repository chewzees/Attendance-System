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
            if (!empty($redirect) && strpos($redirect, '/attendance3/') === 0) {
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg-secondary, #f5f6fa); }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); padding: 2.5rem 2rem; width: 100%; max-width: 420px; }
        .login-logo { text-align: center; margin-bottom: 1.5rem; }
        .login-logo i { font-size: 2.5rem; color: #6366f1; }
        .login-logo h1 { font-size: 1.4rem; font-weight: 600; margin: 0.5rem 0 0; }
        .login-logo p { color: #888; font-size: 0.9rem; margin: 0.25rem 0 0; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.4rem; }
        .form-group input { width: 100%; padding: 0.65rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; outline: none; box-sizing: border-box; transition: border-color 0.2s; }
        .form-group input:focus { border-color: #6366f1; }
        .btn-login { width: 100%; padding: 0.75rem; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 0.5rem; }
        .btn-login:hover { background: #4f51d4; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; padding: 0.7rem 1rem; margin-bottom: 1rem; font-size: 0.9rem; }
        .login-footer { text-align: center; margin-top: 1.2rem; font-size: 0.85rem; color: #888; }
        .login-footer a { color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>Attendance System</h1>
            <p>College Face Recognition</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@college.edu"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        </form>

        <div class="login-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
