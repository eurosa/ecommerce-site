<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the logout action (optional)
if (isset($_SESSION['admin_username'])) {
    // You can log the logout time if you have a logging system
    $logout_time = date('Y-m-d H:i:s');
    // error_log("Admin logout: {$_SESSION['admin_username']} at $logout_time");
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Prevent caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page with message
header('Location: login.php?logged_out=1');
exit;
?>