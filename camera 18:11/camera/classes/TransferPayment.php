<?php
/**
 * TransferPayment Class - Quản lý thanh toán chuyển khoản
 * Hỗ trợ VNPay và Momo (mô phỏng giáo dục)
 */
class TransferPayment {
    
    // Thông tin tài khoản (mô phỏng)
    private static $accounts = [
        'vnpay' => [
            'bank' => 'VNPay',
            'bankCode' => 'VNPAY',
            'accountNumber' => '0123456789',
            'accountName' => 'XEDIC CAMERA',
            'accountShort' => '0123456789',
            'template' => 'DH{ORDER_ID}'
        ],
        'momo' => [
            'bank' => 'Momo',
            'bankCode' => 'MOMO',
            'accountNumber' => '0392123456',
            'accountName' => 'XEDIC CAMERA',
            'accountShort' => '0392123456',
            'template' => 'DH{ORDER_ID}'
        ]
    ];

    private $db;
    private $method;
    private $amount;
    private $orderId;
    private $userId;

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
     * Lấy thông tin tài khoản
     */
    public static function getAccountInfo($method) {
        return self::$accounts[$method] ?? null;
    }

    /**
     * Lấy tất cả thông tin tài khoản
     */
    public static function getAllAccounts() {
        return self::$accounts;
    }

    /**
     * Set phương thức thanh toán
     */
    public function setMethod($method) {
        if (isset(self::$accounts[$method])) {
            $this->method = $method;
            return true;
        }
        return false;
    }

    /**
     * Set số tiền
     */
    public function setAmount($amount) {
        $this->amount = (float) $amount;
        return true;
    }

    /**
     * Set order ID
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
        return true;
    }

    /**
     * Set user ID
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        return true;
    }

    /**
     * Tạo mã QR (mô phỏng - trả về dữ liệu để tạo QR)
     */
    public function generateQRData() {
        if (!$this->method || !$this->amount || !$this->orderId) {
            return false;
        }

        $account = self::$accounts[$this->method];
        
        // Tạo nội dung chuyển khoản
        $description = str_replace('{ORDER_ID}', $this->orderId, $account['template']);

        // Format dữ liệu QR (mô phỏng)
        $qrData = [
            'method' => $this->method,
            'bank' => $account['bank'],
            'bankCode' => $account['bankCode'],
            'accountNumber' => $account['accountNumber'],
            'accountName' => $account['accountName'],
            'amount' => $this->amount,
            'description' => $description,
            'orderId' => $this->orderId,
            'timestamp' => time()
        ];

        return $qrData;
    }

    /**
     * Tạo URL QR code (sử dụng API tạo QR)
     */
    public function getQRCodeUrl($qrData) {
        // Sử dụng API QR code miễn phí (qrserver.com)
        $qrContent = json_encode($qrData);
        $encoded = urlencode($qrContent);
        
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $encoded;
    }

    /**
     * Tạo chuỗi lệnh chuyển khoản cho mô phỏng
     */
    public function generateTransferCommand() {
        if (!$this->method || !$this->amount || !$this->orderId) {
            return false;
        }

        $account = self::$accounts[$this->method];
        $description = str_replace('{ORDER_ID}', $this->orderId, $account['template']);
        
        $command = [];
        
        if ($this->method === 'vnpay') {
            $command = [
                'Số tài khoản' => $account['accountNumber'],
                'Tên tài khoản' => $account['accountName'],
                'Ngân hàng' => $account['bank'],
                'Số tiền' => number_format($this->amount, 0, ',', '.') . ' VND',
                'Nội dung' => $description
            ];
        } elseif ($this->method === 'momo') {
            $command = [
                'Số điện thoại' => $account['accountNumber'],
                'Tên tài khoản' => $account['accountName'],
                'Số tiền' => number_format($this->amount, 0, ',', '.') . ' VND',
                'Nội dung' => $description
            ];
        }

        return $command;
    }

    /**
     * Lưu hóa đơn thanh toán chuyển khoản
     */
    public function saveTransferRequest() {
        error_log('saveTransferRequest called - userId: ' . $this->userId . ', orderId: ' . $this->orderId . ', amount: ' . $this->amount . ', method: ' . $this->method);
        
        if (!$this->userId || !$this->orderId || !$this->amount) {
            error_log('Missing required fields - userId: ' . ($this->userId ? 'OK' : 'MISSING') . ', orderId: ' . ($this->orderId ? 'OK' : 'MISSING') . ', amount: ' . ($this->amount ? 'OK' : 'MISSING'));
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }

        try {
            $conn = $this->db->getConnection();
            
            $sql = "INSERT INTO transfer_payments 
                    (order_id, user_id, method, amount, status, bank_info, created_at) 
                    VALUES (:order_id, :user_id, :method, :amount, :status, :bank_info, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            $status = 'pending';
            $bankInfo = json_encode(self::$accounts[$this->method]);
            
            error_log('About to execute: orderId=' . $this->orderId . ', userId=' . $this->userId . ', method=' . $this->method . ', amount=' . $this->amount);
            
            $stmt->bindParam(':order_id', $this->orderId);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':method', $this->method);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':bank_info', $bankInfo);
            
            if ($stmt->execute()) {
                error_log('Successfully saved transfer request for order: ' . $this->orderId);
                return [
                    'success' => true,
                    'message' => 'Yêu cầu chuyển khoản đã được tạo',
                    'order_id' => $this->orderId
                ];
            }
            
            error_log('Execute failed for order: ' . $this->orderId);
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu yêu cầu'
            ];
        } catch (Exception $e) {
            error_log('Exception in saveTransferRequest: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Xác nhận thanh toán chuyển khoản (mô phỏng)
     */
    public function confirmTransfer($transferId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE transfer_payments 
                    SET status = :status, confirmed_at = NOW() 
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            
            $status = 'confirmed';
            
            $stmt->bindParam(':id', $transferId);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Thanh toán đã được xác nhận'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin yêu cầu chuyển khoản
     */
    public function getTransferRequest($orderId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM transfer_payments WHERE order_id = :order_id AND user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':user_id', $this->userId);
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('getTransferRequest Error: ' . $e->getMessage());
            return null;
        }
    }
}
?>
