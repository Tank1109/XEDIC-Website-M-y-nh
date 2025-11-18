<?php
/**
 * Google Login API Handler
 * Processes Google sign-in authentication
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth.php';

// Set response header
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['uid']) || empty($input['email'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ'
    ]);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    $uid = filter_var($input['uid'], FILTER_UNSAFE_RAW);
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $displayName = filter_var($input['displayName'], FILTER_UNSAFE_RAW) ?? '';
    $photoURL = filter_var($input['photoURL'], FILTER_VALIDATE_URL) ?? '';
    $token = filter_var($input['token'], FILTER_UNSAFE_RAW) ?? '';
    
    if (!$email) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email không hợp lệ'
        ]);
        exit;
    }
    
    // Check if user exists by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // User exists, check if google_uid is set
        if ($user['google_uid'] !== $uid) {
            // Update google_uid
            $stmt = $pdo->prepare("UPDATE users SET google_uid = ? WHERE id = ?");
            $stmt->execute([$uid, $user['id']]);
        }
        
        $userId = $user['id'];
        $role = $user['role'];
        $username = $user['username'];
    } else {
        // Create new user from Google info
        $username = $email;
        // Extract name from email or use displayName
        $fullName = $displayName ?: explode('@', $email)[0];
        
        // Check if username (email) already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            // Generate unique username
            $username = explode('@', $email)[0] . '_' . substr(uniqid(), -5);
        }
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, google_uid, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        // Generate random password (user won't use password for Google login)
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        
        $stmt->execute([
            $username,
            $email,
            $randomPassword,
            $fullName,
            $uid
        ]);
        
        $userId = $pdo->lastInsertId();
        $role = 'customer'; // Default role for new users
    }
    
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = $displayName ?: explode('@', $email)[0];
    $_SESSION['loggedin'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['google_login'] = true;
    
    // Optionally store photo URL
    if ($photoURL) {
        $_SESSION['photo_url'] = $photoURL;
    }
    
    // Log the login
    error_log("Google login successful for user: {$email} (ID: {$userId})");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user_id' => $userId,
        'role' => $role,
        'email' => $email
    ]);
    
} catch (PDOException $e) {
    error_log('Google Login Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi. Vui lòng thử lại!'
    ]);
} catch (Exception $e) {
    error_log('Unexpected Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi. Vui lòng thử lại!'
    ]);
}
?>
