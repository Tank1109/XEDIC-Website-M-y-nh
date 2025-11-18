<?php
/**
 * Payment Class - Quản lý các phương thức thanh toán
 */
class Payment {
    const PAYMENT_METHODS = [
        'vnpay' => [
            'id' => 'vnpay',
            'name' => 'VNPay Chuyển khoản',
            'description' => 'Chuyển khoản qua VNPay (Quét mã QR)',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#003399',
            'fee' => 0,
            'type' => 'transfer'
        ],
        'momo' => [
            'id' => 'momo',
            'name' => 'Momo Chuyển khoản',
            'description' => 'Chuyển khoản qua Momo (Quét mã QR)',
            'icon' => 'fas fa-wallet',
            'color' => '#EF3751',
            'fee' => 0,
            'type' => 'transfer'
        ],
        'cod' => [
            'id' => 'cod',
            'name' => 'Thanh toán khi nhận hàng',
            'description' => 'Thanh toán tiền mặt khi nhận sản phẩm',
            'icon' => 'fas fa-money-bill-wave',
            'color' => '#10B981',
            'fee' => 0,
            'type' => 'cod'
        ]
    ];

    private $method;
    private $amount;
    private $orderId;
    private $userId;
    private $db;

    /**
     * Constructor
     */
    public function __construct($db = null) {
        if ($db === null) {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $this->db = $database;
        } else {
            $this->db = $db;
        }
    }

    /**
     * Lấy danh sách phương thức thanh toán
     */
    public static function getPaymentMethods() {
        return self::PAYMENT_METHODS;
    }

    /**
     * Lấy chi tiết phương thức thanh toán
     */
    public static function getPaymentMethod($method) {
        return self::PAYMENT_METHODS[$method] ?? null;
    }

    /**
     * Set phương thức thanh toán
     */
    public function setMethod($method) {
        if (isset(self::PAYMENT_METHODS[$method])) {
            $this->method = $method;
            return true;
        }
        return false;
    }

    /**
     * Set số tiền thanh toán
     */
    public function setAmount($amount) {
        $this->amount = (float) $amount;
        return true;
    }

    /**
     * Set ID đơn hàng
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
        return true;
    }

    /**
     * Set ID người dùng
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        return true;
    }

    /**
     * Tạo thanh toán VNPay
     */
    public function createVNPayPayment() {
        if ($this->method !== 'vnpay' || !$this->amount || !$this->orderId) {
            return false;
        }

        // TODO: Integrate with VNPay API
        // This is a placeholder for VNPay integration
        return [
            'success' => true,
            'url' => 'https://sandbox.vnpayment.vn/paygate/pay',
            'message' => 'Chuyển hướng đến trang thanh toán VNPay'
        ];
    }

    /**
     * Xử lý thanh toán COD (Cash on Delivery)
     * @return array|bool
     */
    public function processCODPayment($shippingInfo = null) {
        if ($this->method !== 'cod' || !$this->userId || !$this->orderId) {
            return false;
        }

        try {
            // Lưu thông tin thanh toán vào database
            $conn = $this->db->getConnection();
            
            // Bắt đầu transaction
            $conn->beginTransaction();
            
            // Lưu payment
            $sql = "INSERT INTO payments (order_id, user_id, method, amount, status, shipping_phone, shipping_address, created_at) 
                    VALUES (:order_id, :user_id, :method, :amount, :status, :shipping_phone, :shipping_address, NOW())";
            
            $stmt = $conn->prepare($sql);
            $status = 'pending';
            $shippingPhone = $shippingInfo['phone'] ?? '';
            $shippingAddress = $shippingInfo['address'] ?? '';
            
            $stmt->bindParam(':order_id', $this->orderId);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':method', $this->method);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':shipping_phone', $shippingPhone);
            $stmt->bindParam(':shipping_address', $shippingAddress);
            
            if (!$stmt->execute()) {
                $conn->rollBack();
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi lưu thông tin thanh toán'
                ];
            }
            
            $paymentId = $conn->lastInsertId();
            
            // Lưu chi tiết sản phẩm vào order_items
            require_once __DIR__ . '/Cart.php';
            $cart = new Cart($this->userId);
            $cartItems = $cart->getItems();
            
            if (!empty($cartItems)) {
                $insertItems = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
                                VALUES (:order_id, :product_id, :product_name, :product_price, :quantity, :subtotal)";
                $stmtItems = $conn->prepare($insertItems);
                
                foreach ($cartItems as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    
                    $stmtItems->bindParam(':order_id', $paymentId);
                    $stmtItems->bindParam(':product_id', $item['product_id']);
                    $stmtItems->bindParam(':product_name', $item['name']);
                    $stmtItems->bindParam(':product_price', $item['price']);
                    $stmtItems->bindParam(':quantity', $item['quantity']);
                    $stmtItems->bindParam(':subtotal', $subtotal);
                    
                    if (!$stmtItems->execute()) {
                        $conn->rollBack();
                        return [
                            'success' => false,
                            'message' => 'Có lỗi xảy ra khi lưu chi tiết sản phẩm'
                        ];
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Đơn hàng của bạn đã được tạo. Vui lòng thanh toán khi nhận hàng.',
                'order_id' => $this->orderId
            ];
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy lịch sử thanh toán
     */
    public function getPaymentHistory($userId, $limit = 10) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM payments WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($paymentId, $status) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE payments SET status = :status, updated_at = NOW() WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $paymentId);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Tạo payment record cho chuyển khoản
     * @param array|null $shippingInfo
     * @return array
     */
    public function createTransferPayment($shippingInfo = null) {
        try {
            $conn = $this->db->getConnection();
            
            // Bắt đầu transaction
            $conn->beginTransaction();
            
            $sql = "INSERT INTO payments 
                    (order_id, user_id, method, amount, status, shipping_phone, shipping_address, created_at) 
                    VALUES (:order_id, :user_id, :method, :amount, :status, :shipping_phone, :shipping_address, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            $status = 'pending';
            $shippingPhone = $shippingInfo['phone'] ?? '';
            $shippingAddress = $shippingInfo['address'] ?? '';
            
            $stmt->bindParam(':order_id', $this->orderId);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':method', $this->method);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':shipping_phone', $shippingPhone);
            $stmt->bindParam(':shipping_address', $shippingAddress);
            
            if (!$stmt->execute()) {
                $conn->rollBack();
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo đơn hàng'
                ];
            }
            
            $paymentId = $conn->lastInsertId();
            
            // Lưu chi tiết sản phẩm vào order_items
            require_once __DIR__ . '/Cart.php';
            $cart = new Cart($this->userId);
            $cartItems = $cart->getItems();
            
            if (!empty($cartItems)) {
                $insertItems = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
                                VALUES (:order_id, :product_id, :product_name, :product_price, :quantity, :subtotal)";
                $stmtItems = $conn->prepare($insertItems);
                
                foreach ($cartItems as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    
                    $stmtItems->bindParam(':order_id', $paymentId);
                    $stmtItems->bindParam(':product_id', $item['product_id']);
                    $stmtItems->bindParam(':product_name', $item['name']);
                    $stmtItems->bindParam(':product_price', $item['price']);
                    $stmtItems->bindParam(':quantity', $item['quantity']);
                    $stmtItems->bindParam(':subtotal', $subtotal);
                    
                    if (!$stmtItems->execute()) {
                        $conn->rollBack();
                        return [
                            'success' => false,
                            'message' => 'Có lỗi xảy ra khi lưu chi tiết sản phẩm'
                        ];
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Đơn hàng đã được tạo, đợi xác nhận thanh toán',
                'order_id' => $this->orderId
            ];
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log('createTransferPayment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin thanh toán
     */
    public function getPaymentInfo($paymentId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM payments WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $paymentId);
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Kiểm tra xem có bảng payments không, nếu không thì tạo
     */
    public static function createPaymentTable($db) {
        try {
            $conn = $db->getConnection();
            
            $sql = "CREATE TABLE IF NOT EXISTS payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id VARCHAR(50) NOT NULL UNIQUE,
                user_id INT NOT NULL,
                method VARCHAR(20) NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                status VARCHAR(20) DEFAULT 'pending',
                vnpay_transaction_no VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_method (method),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            return $conn->exec($sql);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
