<?php
/**
 * Admin Configuration
 */

// Base admin URL
define('ADMIN_URL', '/admin');
define('ADMIN_PATH', __DIR__);
define('ADMIN_ROOT', dirname(__DIR__));

// Session timeout (in minutes)
define('ADMIN_SESSION_TIMEOUT', 60);

// Check if user is logged in as admin
function requireAdminLogin() {
    session_start();
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
    
    // Check session timeout
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > (ADMIN_SESSION_TIMEOUT * 60)) {
        session_destroy();
        header('Location: ' . ADMIN_URL . '/login.php?timeout=1');
        exit;
    }
}

?>
