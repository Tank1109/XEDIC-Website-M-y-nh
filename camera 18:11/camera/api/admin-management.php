<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Truy cập không được phép']);
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    addAdmin($db);
} elseif ($action === 'edit') {
    editAdmin($db);
} elseif ($action === 'delete') {
    deleteAdmin($db);
} elseif ($action === 'toggle-status') {
    toggleStatus($db);
} elseif ($action === 'get') {
    getAdmin($db);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
}

function addAdmin($db) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (!$username || !$email || !$full_name || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        return;
    }
    
    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
        return;
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert admin
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, full_name, role, is_active, created_at)
        VALUES (?, ?, ?, ?, 'admin', 1, NOW())
    ");
    
    if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
        echo json_encode(['success' => true, 'message' => 'Thêm admin thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm admin']);
    }
}

function editAdmin($db) {
    $id = intval($_POST['id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Admin không tồn tại']);
        return;
    }
    
    // Validation
    if (!$email || !$full_name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    // Check if email exists (exclude current admin)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
        return;
    }
    
    // Update admin
    if ($password) {
        // If password is provided, update it
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
            return;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            UPDATE users 
            SET email = ?, full_name = ?, password = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$email, $full_name, $hashed_password, $id]);
    } else {
        // Update without password
        $stmt = $db->prepare("
            UPDATE users 
            SET email = ?, full_name = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$email, $full_name, $id]);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật admin thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật admin']);
    }
}

function deleteAdmin($db) {
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }
    
    // Prevent deleting current admin
    if ($id === $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản của bạn']);
        return;
    }
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Admin không tồn tại']);
        return;
    }
    
    // Delete admin
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => 'Xóa admin thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa admin']);
    }
}

function toggleStatus($db) {
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }
    
    // Prevent toggling current admin
    if ($id === $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không thể vô hiệu hóa tài khoản của bạn']);
        return;
    }
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT is_active FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Admin không tồn tại']);
        return;
    }
    
    // Toggle status
    $new_status = $admin['is_active'] ? 0 : 1;
    $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $id])) {
        echo json_encode([
            'success' => true, 
            'message' => $new_status ? 'Kích hoạt admin thành công' : 'Vô hiệu hóa admin thành công',
            'is_active' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái']);
    }
}

function getAdmin($db) {
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }
    
    $stmt = $db->prepare("SELECT id, username, email, full_name FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo json_encode(['success' => true, 'admin' => $admin]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Admin không tồn tại']);
    }
}
