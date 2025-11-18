<?php
require_once '../config/database.php';
require_once '../auth/auth.php';
require_once '../classes/Product.php';
require_once '../classes/Cart.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'not_logged_in']);
        exit;
    }

    // Check if product_id is provided
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Get product information
    $productModel = new Product();
    $product = $productModel->getProductById($product_id);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // Check if product is in stock
    if ($product['stock'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm hiện hết hàng']);
        exit;
    }

    // Add to cart using Cart class
    $cart = new Cart($user_id);
    $result = $cart->addItem($product_id, $quantity);

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'product_name' => $product['name'],
        'product_price' => $product['price']
    ]);

} catch (Exception $e) {
    error_log('Add to Cart Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại!']);
}
?>

