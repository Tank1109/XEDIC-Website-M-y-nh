<?php
/**
 * Auth Class - Handles user authentication
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $pdo;
    private $users_table = 'users';

    public function __construct() {
        // Initialize database connection using Database class
        $database = new Database();
        $this->pdo = $database->getConnection();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login user with username and password
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password) {
        try {
            // Prepare statement to prevent SQL injection
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user exists and verify password
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['loggedin'] = true;
                $_SESSION['login_time'] = time();
                
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log('Login Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login user with Google
     * 
     * @param string $email
     * @param string $google_uid
     * @param string $display_name
     * @return bool
     */
    public function loginWithGoogle($email, $google_uid, $display_name = '') {
        try {
            // Check if user exists by email
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // User exists, update google_uid if not already set
                if (empty($user['google_uid'])) {
                    $stmt = $this->pdo->prepare("UPDATE {$this->users_table} SET google_uid = ? WHERE id = ?");
                    $stmt->execute([$google_uid, $user['id']]);
                }
            } else {
                // Create new user
                $username = $email;
                $full_name = $display_name ?: explode('@', $email)[0];
                
                // Check if username already exists
                $stmt = $this->pdo->prepare("SELECT id FROM {$this->users_table} WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $username = explode('@', $email)[0] . '_' . substr(uniqid(), -5);
                }
                
                // Insert new user
                $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("
                    INSERT INTO {$this->users_table} (username, email, password, full_name, google_uid, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                ");
                
                $stmt->execute([$username, $email, $random_password, $full_name, $google_uid]);
                $user_id = $this->pdo->lastInsertId();
                
                // Fetch the newly created user
                $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['loggedin'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['google_login'] = true;
            
            return true;
        } catch(PDOException $e) {
            error_log('Google Login Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login user with Facebook
     * 
     * @param string $email
     * @param string $facebook_uid
     * @param string $display_name
     * @param string $photo_url
     * @return bool
     */
    public function loginWithFacebook($email, $facebook_uid, $display_name = '', $photo_url = '') {
        try {
            // Check if user exists by email
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // User exists, update facebook_uid if not already set
                if (empty($user['facebook_uid'])) {
                    $stmt = $this->pdo->prepare("UPDATE {$this->users_table} SET facebook_uid = ? WHERE id = ?");
                    $stmt->execute([$facebook_uid, $user['id']]);
                }
            } else {
                // Create new user
                $username = $email;
                $full_name = $display_name ?: explode('@', $email)[0];
                
                // Check if username already exists
                $stmt = $this->pdo->prepare("SELECT id FROM {$this->users_table} WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $username = explode('@', $email)[0] . '_' . substr(uniqid(), -5);
                }
                
                // Insert new user
                $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("
                    INSERT INTO {$this->users_table} (username, email, password, full_name, facebook_uid, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                ");
                
                $stmt->execute([$username, $email, $random_password, $full_name, $facebook_uid]);
                $user_id = $this->pdo->lastInsertId();
                
                // Fetch the newly created user
                $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['loggedin'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['facebook_login'] = true;
            
            // Store photo URL if provided
            if ($photo_url) {
                $_SESSION['photo_url'] = $photo_url;
            }
            
            return true;
        } catch(PDOException $e) {
            error_log('Facebook Login Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Register new user
     * 
     * @param array $data User data
     * @return array
     */
    public function register($data) {
        try {
            // Validate input
            $errors = [];
            
            if (empty($data['username'])) {
                $errors['username'] = 'Tên đăng nhập không được để trống';
            }
            
            if (empty($data['email'])) {
                $errors['email'] = 'Email không được để trống';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ';
            }
            
            if (empty($data['password'])) {
                $errors['password'] = 'Mật khẩu không được để trống';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }
            
            if (empty($data['confirm_password']) || $data['password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'Mật khẩu không trùng khớp';
            }
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Check if username already exists
            $stmt = $this->pdo->prepare("SELECT id FROM {$this->users_table} WHERE username = ?");
            $stmt->execute([$data['username']]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Tên đăng nhập đã tồn tại'];
            }
            
            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM {$this->users_table} WHERE email = ?");
            $stmt->execute([$data['email']]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Email đã được đăng ký'];
            }
            
            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
            
            // Insert new user
            $stmt = $this->pdo->prepare("INSERT INTO {$this->users_table} (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashed_password,
                $data['full_name'] ?? $data['username']
            ]);
            
            return ['success' => true, 'message' => 'Đăng ký thành công. Vui lòng đăng nhập!'];
        } catch(PDOException $e) {
            error_log('Registration Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Đã xảy ra lỗi. Vui lòng thử lại!'];
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $user_id
     * @return array|false
     */
    public function getUserById($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->users_table} WHERE id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log('Get User Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    public function updateProfile($user_id, $data) {
        try {
            $allowed_fields = ['full_name', 'phone', 'address'];
            $set_clause = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $set_clause[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($set_clause)) {
                return false;
            }
            
            $values[] = $user_id;
            $query = "UPDATE {$this->users_table} SET " . implode(', ', $set_clause) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($values);
        } catch(PDOException $e) {
            error_log('Update Profile Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change password
     * 
     * @param int $user_id
     * @param string $current_password
     * @param string $new_password
     * @return array
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            $user = $this->getUserById($user_id);
            
            if (!$user) {
                return ['success' => false, 'error' => 'Người dùng không tồn tại'];
            }
            
            if (!password_verify($current_password, $user['password'])) {
                return ['success' => false, 'error' => 'Mật khẩu hiện tại không chính xác'];
            }
            
            if (strlen($new_password) < 6) {
                return ['success' => false, 'error' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE {$this->users_table} SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
        } catch(PDOException $e) {
            error_log('Change Password Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Đã xảy ra lỗi. Vui lòng thử lại!'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        $_SESSION = [];
        
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
        
        session_destroy();
    }

    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Check if current user is owner of resource
     * 
     * @param int $user_id
     * @return bool
     */
    public function isOwner($user_id) {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user_id;
    }
}

/**
 * Helper function to check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Helper function to get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Helper function to get current username
 * 
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Helper function to check if current user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Redirect to home if logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}
?>