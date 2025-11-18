<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../auth/auth.php';
require_once '../classes/Cart.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'not_logged_in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$item_id = $_POST['item_id'] ?? null;

if (!$item_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing item_id']);
    exit;
}

try {
    $cart = new Cart($_SESSION['user_id']);
    $cart->removeItem($item_id);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
} catch (Exception $e) {
    error_log('Remove from Cart Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
