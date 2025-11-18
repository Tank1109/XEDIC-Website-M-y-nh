<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/TransferPayment.php';
require_once __DIR__ . '/../classes/Payment.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    error_log('Confirm Transfer - No session user_id');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get order ID
$orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : null;
$userId = $_SESSION['user_id'];

// Debug log
error_log('Confirm Transfer Request - Order ID: ' . ($orderId ?? 'NULL') . ' | User ID: ' . $userId . ' | POST: ' . json_encode($_POST));

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID is required', 'debug' => $_POST]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get transfer payment info
    $transferPayment = new TransferPayment($database);
    $transferPayment->setUserId($userId);
    
    $transferRequest = $transferPayment->getTransferRequest($orderId);
    
    if (!$transferRequest) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Transfer request not found']);
        exit;
    }

    // Confirm transfer
    $result = $transferPayment->confirmTransfer($transferRequest['id']);
    
    if ($result['success']) {
        // Update payment status
        $sql = "UPDATE payments SET status = :status, updated_at = NOW() WHERE order_id = :order_id";
        $stmt = $db->prepare($sql);
        $status = 'completed';
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'order_id' => $orderId
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }

} catch (Exception $e) {
    error_log('Confirm Transfer Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
