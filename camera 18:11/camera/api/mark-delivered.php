<?php
/**
 * Mark Delivered API
 * Đánh dấu đơn hàng đã giao - khi đó doanh thu sẽ được tính
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
        
        // Kiểm tra nếu đã hủy
        if ($payment['status'] === 'cancelled') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật đơn hàng đã bị hủy']);
            exit;
        }
        
        // Cập nhật trạng thái giao hàng thành "delivered"
        if (($payment['delivery_status'] ?? 'pending') !== 'delivered') {
            $update_stmt = $db->prepare("
                UPDATE payments 
                SET delivery_status = 'delivered', updated_at = NOW() 
                WHERE order_id = :order_id
            ");
            $update_stmt->bindParam(':order_id', $order_id);
            
            if ($update_stmt->execute()) {
                $revenue_text = number_format($payment['amount'], 0, ',', '.');
                echo json_encode([
                    'success' => true, 
                    'message' => '✓ Đã giao hàng thành công! Doanh thu được cộng: ' . $revenue_text . ' VNĐ',
                    'amount' => $payment['amount']
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật dữ liệu']);
            }
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Đơn hàng đã được đánh dấu giao hàng',
                'amount' => $payment['amount']
            ]);
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
