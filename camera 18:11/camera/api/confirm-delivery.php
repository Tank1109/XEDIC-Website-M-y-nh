<?php
/**
 * Confirm Delivery API
 * Khi đơn hàng được giao, cập nhật trạng thái thanh toán từ pending thành paid
 */

session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ']);
        exit;
    }
    
    $order_id = $data['order_id'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Kiểm tra xem đơn hàng có tồn tại không
        $check_stmt = $db->prepare("SELECT * FROM payments WHERE order_id = :order_id");
        $check_stmt->bindParam(':order_id', $order_id);
        $check_stmt->execute();
        $payment = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }
        
        // Cập nhật trạng thái từ pending thành paid
        if ($payment['status'] === 'pending') {
            $update_stmt = $db->prepare("
                UPDATE payments 
                SET status = 'paid', updated_at = NOW() 
                WHERE order_id = :order_id
            ");
            $update_stmt->bindParam(':order_id', $order_id);
            
            if ($update_stmt->execute()) {
                // Log thay đổi
                $log = date('Y-m-d H:i:s') . " - Đã xác nhận giao hàng: {$order_id} (" . number_format($payment['amount'], 0, ',', '.') . " VNĐ)\n";
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Xác nhận thành công! Doanh thu đã được cộng: ' . number_format($payment['amount'], 0, ',', '.') . ' VNĐ',
                    'amount' => $payment['amount']
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật dữ liệu']);
            }
        } elseif ($payment['status'] === 'paid' || $payment['status'] === 'confirmed') {
            echo json_encode([
                'success' => true, 
                'message' => 'Đơn hàng đã được xác nhận thanh toán: ' . number_format($payment['amount'], 0, ',', '.') . ' VNĐ',
                'amount' => $payment['amount']
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật đơn hàng với trạng thái: ' . $payment['status']]);
        }
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>
