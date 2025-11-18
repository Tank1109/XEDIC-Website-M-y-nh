<?php
/**
 * Facebook Login API Handler
 * Processes Facebook sign-in authentication via Firebase
 * Supports multiple Facebook accounts
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
    
    $facebook_uid = filter_var($input['uid'], FILTER_UNSAFE_RAW);
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $displayName = filter_var($input['displayName'], FILTER_UNSAFE_RAW) ?? '';
    $photoURL = filter_var($input['photoURL'], FILTER_VALIDATE_URL) ?? '';
    $token = filter_var($input['token'], FILTER_UNSAFE_RAW) ?? '';
    $providerId = filter_var($input['providerId'], FILTER_UNSAFE_RAW) ?? 'facebook.com';
    
    if (!$email) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email không hợp lệ'
        ]);
        exit;
    }
    
    // Validate Facebook UID
    if (empty($facebook_uid) || strlen($facebook_uid) < 3) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'UID Facebook không hợp lệ'
        ]);
        exit;
    }
    
    error_log("Facebook Login Attempt - Email: {$email}, Facebook UID: {$facebook_uid}");
    
    // CASE 1: Check if user exists by facebook_uid (most accurate match)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE facebook_uid = ? AND is_active = 1");
    $stmt->execute([$facebook_uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // User found by facebook_uid - this is an existing Facebook account
        error_log("User found by facebook_uid: User ID {$user['id']}");
        
        $userId = $user['id'];
        $role = $user['role'];
        $username = $user['username'];
        
        // Update email if changed (in case user updated their Facebook email)
        if ($user['email'] !== $email) {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $userId]);
            error_log("Updated email for user {$userId}: {$user['email']} → {$email}");
        }
    } else {
        // CASE 2: Check if email exists
        // If email exists, just link the Facebook account to it
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // Email exists - link this Facebook account to existing user
            error_log("Linking Facebook UID to existing user: User ID {$existingUser['id']}");
            
            $userId = $existingUser['id'];
            $role = $existingUser['role'];
            $username = $existingUser['username'];
            
            // Update facebook_uid for this user (link the account)
            $stmt = $pdo->prepare("UPDATE users SET facebook_uid = ? WHERE id = ?");
            $stmt->execute([$facebook_uid, $userId]);
            
            error_log("Linked Facebook UID {$facebook_uid} to user {$userId}");
        } else {
            // CASE 3: Create new user - completely new Facebook account
            error_log("Creating new user from Facebook - Email: {$email}");
            
            $username = $email;
            $fullName = $displayName ?: explode('@', $email)[0];
            
            // Check if username (email) already exists as username
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                // Generate unique username
                $username = explode('@', $email)[0] . '_' . substr(uniqid(), -5);
                error_log("Generated unique username: {$username}");
            }
            
            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, full_name, facebook_uid, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            
            // Generate random password (user won't use password for Facebook login)
            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
            
            $stmt->execute([
                $username,
                $email,
                $randomPassword,
                $fullName,
                $facebook_uid
            ]);
            
            $userId = $pdo->lastInsertId();
            $role = 'customer'; // Default role for new users
            
            error_log("New user created - ID: {$userId}, Email: {$email}, Facebook UID: {$facebook_uid}");
        }
    }
    
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = $displayName ?: explode('@', $email)[0];
    $_SESSION['loggedin'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['facebook_login'] = true;
    $_SESSION['facebook_uid'] = $facebook_uid;
    
    // Optionally store photo URL
    if ($photoURL) {
        $_SESSION['photo_url'] = $photoURL;
    }
    
    // Log successful login
    error_log("Facebook login successful - User ID: {$userId}, Email: {$email}, Role: {$role}");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user_id' => $userId,
        'role' => $role,
        'email' => $email,
        'username' => $username
    ]);
    
} catch (PDOException $e) {
    error_log('Facebook Login Database Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu. Vui lòng thử lại!'
    ]);
} catch (Exception $e) {
    error_log('Facebook Login Unexpected Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi. Vui lòng thử lại!'
    ]);
}
?>
