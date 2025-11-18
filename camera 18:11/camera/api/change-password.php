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

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// xử lý đổi mật khẩu
if (!$current_password || !$new_password || !$confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
    exit;
}

if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không trùng khớp']);
    exit;
}

if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
    exit;
}

if ($new_password === $current_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải khác với mật khẩu hiện tại']);
    exit;
}

// Get current user's password from database
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
    exit;
}

// Xác minh mật khẩu hiện tại
if (!password_verify($current_password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác']);
    exit;
}

// mã hóa mật khẩu mới
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update mật khẩu
$stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
if ($stmt->execute([$hashed_password, $user_id])) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật mật khẩu thành công']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu']);
}
?>
