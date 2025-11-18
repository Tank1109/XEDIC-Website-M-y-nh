<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../classes/ShippingInfo.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $phone = trim($_POST['phone'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validate inputs
        $errors = ShippingInfo::validate($phone, $province, $address);
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors),
                'errors' => $errors
            ]);
            exit;
        }

        // Save shipping information
        $shipping = new ShippingInfo($user_id);
        $result = $shipping->save($phone, $province, $address);

        // Calculate shipping fee
        $shippingFee = $shipping->getShippingFee($province);
        
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'shippingFee' => $shippingFee,
            'isFreeShipping' => $shipping->isFreeShipping($province)
        ]);
    } else {
        // GET request - return provinces for dropdown
        echo json_encode([
            'success' => true,
            'provinces' => ShippingInfo::getProvinces()
        ]);
    }
} catch (Exception $e) {
    error_log('Shipping API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống'
    ]);
}
?>
