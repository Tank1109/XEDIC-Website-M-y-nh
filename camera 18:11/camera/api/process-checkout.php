<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Payment.php';
require_once __DIR__ . '/../classes/ShippingInfo.php';
require_once __DIR__ . '/../classes/Cart.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get request data
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : null;
$userId = $_SESSION['user_id'];

if (!$paymentMethod) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment method is required']);
    exit;
}

try {
    // Initialize database and classes
    $database = new Database();
    $db = $database->getConnection();
    
    // Get cart data
    $cart = new Cart($userId);
    $cartItems = $cart->getItems();
    $subtotal = $cart->getTotal();

    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // Get shipping info
    $shippingInfo = new ShippingInfo($userId);
    $shippingData = $shippingInfo->get();

    if (!$shippingData) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Shipping information is incomplete']);
        exit;
    }

    // Calculate total with shipping
    $shippingFee = $shippingInfo->calculateShippingFee($shippingData['province']);
    $totalAmount = $subtotal + $shippingFee;

    // Generate order ID
    $orderId = 'ORD-' . date('YmdHis') . '-' . uniqid();
    error_log('Generated order ID: ' . $orderId . ' for method: ' . $paymentMethod);

    // Initialize payment
    $payment = new Payment($database);
    $payment->setMethod($paymentMethod);
    $payment->setAmount($totalAmount);
    $payment->setOrderId($orderId);
    $payment->setUserId($userId);

    // Process payment based on method
    if ($paymentMethod === 'vnpay' || $paymentMethod === 'momo') {
        // Transfer payment (VNPay QR or Momo QR)
        // Create payment record with pending status
        error_log('Creating transfer payment for order: ' . $orderId . ', method: ' . $paymentMethod . ', amount: ' . $totalAmount);
        $result = $payment->createTransferPayment($shippingData);
        error_log('Create transfer payment result: ' . json_encode($result));
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Order created, ready for transfer payment'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $result['message'], 'debug' => 'CreateTransferPayment failed']);
        }
    } else if ($paymentMethod === 'cod') {
        // Process COD (Cash on Delivery) payment
        $result = $payment->processCODPayment($shippingData);
        
        if ($result['success']) {
            // Clear cart after successful order
            $cart->clear();

            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'message' => $result['message']
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
    }

} catch (Exception $e) {
    error_log('Checkout Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
