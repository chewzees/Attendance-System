<?php
/**
 * Authentication & Session Management
 * College Face Recognition Attendance System
 */

require_once __DIR__ . '/database.php';

// Start session securely if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// Require user to be logged in; redirect to login if not
function requireLogin($redirectTo = null) {
    startSession();
    if (empty($_SESSION['user_id'])) {
        $redirect = $redirectTo ?? ($_SERVER['PHP_SELF'] ?? '/');
        header('Location: ' . baseUrl('login.php') . '?redirect=' . urlencode($redirect));
        exit;
    }
}

// Require a specific role (or array of roles); show 403 if not allowed
function requireRole($roles) {
    requireLogin();
    $roles = (array)$roles;
    $userRole = strtolower($_SESSION['role'] ?? '');
    $allowed  = array_map('strtolower', $roles);
    if (!in_array($userRole, $allowed, true)) {
        http_response_code(403);
        $css = htmlspecialchars(baseUrl('assets/css/style.css'));
        $home = htmlspecialchars(baseUrl('index.php'));
        echo '<!DOCTYPE html><html><head><title>Access Denied</title>
              <link rel="stylesheet" href="' . $css . '"></head>
              <body><div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:1rem">
              <h2>Access Denied</h2>
              <p>You do not have permission to access this page.</p>
              <a href="' . $home . '" class="btn btn-primary">Go to Dashboard</a>
              </div></body></html>';
        exit;
    }
}

// Require API caller to be authenticated; returns session user data or sends 401
function requireApiLogin() {
    startSession();
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Authentication required. Please log in.']);
        exit;
    }
    return [
        'id'      => $_SESSION['user_id'],
        'user_id' => $_SESSION['user_code'],
        'name'    => $_SESSION['name'],
        'role'    => $_SESSION['role'],
        'role_id' => $_SESSION['role_id'],
    ];
}

// Require API caller to have a specific role
function requireApiRole($roles) {
    $user  = requireApiLogin();
    $roles = (array)$roles;
    $userRole = strtolower($user['role']);
    $allowed  = array_map('strtolower', $roles);
    if (!in_array($userRole, $allowed, true)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Insufficient permissions.']);
        exit;
    }
    return $user;
}

// Log in a user by verifying email+password; returns user array or false
function loginUser($email, $password) {
    require_once __DIR__ . '/database.php';
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.user_id, u.name, u.email, u.password, u.status,
               ur.role_name, u.role_id, u.department
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        return false;
    }

    // Support plain-text legacy passwords (e.g. the seeded admin123) as well as hashed
    $passwordOk = password_verify($password, $user['password'])
               || ($user['password'] === $password);

    if (!$passwordOk) {
        return false;
    }

    // Rehash plain-text passwords on first login
    if ($user['password'] === $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user['id']]);
    }

    return $user;
}

// Create session after successful login
function createSession($user) {
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_code'] = $user['user_id'];
    $_SESSION['name']      = $user['name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role_name'];
    $_SESSION['role_id']   = $user['role_id'];
    $_SESSION['dept']      = $user['department'];
    $_SESSION['login_at']  = time();
}

// Destroy session (logout)
function logoutUser() {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Check if current session user is logged in (non-redirecting)
function isLoggedIn() {
    startSession();
    return !empty($_SESSION['user_id']);
}

// Get current logged-in user data (or null)
function currentUser() {
    startSession();
    if (empty($_SESSION['user_id'])) return null;
    return [
        'id'      => $_SESSION['user_id'],
        'user_id' => $_SESSION['user_code'],
        'name'    => $_SESSION['name'],
        'email'   => $_SESSION['email'],
        'role'    => $_SESSION['role'],
        'role_id' => $_SESSION['role_id'],
        'dept'    => $_SESSION['dept'],
    ];
}

// Helper: set CORS to same origin only (replaces the old wildcard header)
function setCorsHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if ($origin === $allowed || $origin === str_replace('http://', 'https://', $allowed)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
