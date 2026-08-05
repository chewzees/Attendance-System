<?php
require_once 'config/auth.php';
require_once 'config/helpers.php';
$user = currentUser();
if ($user) {
    logActivity($user['id'], 'logout', 'User logged out');
}
logoutUser();
header('Location: login.php');
exit;
