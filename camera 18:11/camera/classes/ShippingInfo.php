<?php
/**
 * ShippingInfo Class
 * Handles shipping information and delivery fees
 */
class ShippingInfo {
    private $db;
    private $table = 'shipping_info';
    private $user_id;
    
    // Vietnamese provinces with free shipping
    private $freeShippingProvinces = ['Hà Nội'];
    private $standardShippingFee = 30000; // VND

    /**
     * Constructor
     */
    public function __construct($user_id = null) {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user_id = $user_id;
    }

    /**
     * Get all Vietnamese provinces
     */
    public static function getProvinces() {
        return [
            'Hà Nội',
            'Hà Giang',
            'Cao Bằng',
            'Bắc Kạn',
            'Tuyên Quang',
            'Lào Cai',
            'Điện Biên',
            'Lai Châu',
            'Sơn La',
            'Yên Bái',
            'Hòa Bình',
            'Thái Nguyên',
            'Lạng Sơn',
            'Quảng Ninh',
            'Bắc Giang',
            'Bắc Ninh',
            'Hải Dương',
            'Hải Phòng',
            'Hưng Yên',
            'Thái Bình',
            'Hà Nam',
            'Nam Định',
            'Ninh Bình',
            'Thanh Hóa',
            'Nghệ An',
            'Hà Tĩnh',
            'Quảng Bình',
            'Quảng Trị',
            'Thừa Thiên Huế',
            'Đà Nẵng',
            'Quảng Nam',
            'Quảng Ngãi',
            'Bình Định',
            'Phú Yên',
            'Khánh Hòa',
            'Ninh Thuận',
            'Bình Thuận',
            'Đồng Nai',
            'Bà Rịa - Vũng Tàu',
            'TP. Hồ Chí Minh',
            'Long An',
            'Tiền Giang',
            'Bến Tre',
            'Trà Vinh',
            'Vĩnh Long',
            'Cần Thơ',
            'Đồng Tháp',
            'An Giang',
            'Kiên Giang',
            'Hậu Giang',
            'Sóc Trăng',
            'Bạc Liêu',
            'Cà Mau'
        ];
    }

    /**
     * Save shipping information
     */
    public function save($phone, $province, $address) {
        try {
            // Check if shipping info already exists for user
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$this->user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing shipping info
                $stmt = $this->db->prepare(
                    "UPDATE {$this->table} SET phone = ?, province = ?, address = ?, updated_at = NOW() WHERE user_id = ?"
                );
                $stmt->execute([$phone, $province, $address, $this->user_id]);
            } else {
                // Create new shipping info
                $stmt = $this->db->prepare(
                    "INSERT INTO {$this->table} (user_id, phone, province, address, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, NOW(), NOW())"
                );
                $stmt->execute([$this->user_id, $phone, $province, $address]);
            }

            return ['success' => true, 'message' => 'Lưu thông tin giao hàng thành công'];
        } catch (PDOException $e) {
            error_log('Save Shipping Info Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu'];
        }
    }

    /**
     * Get shipping information for user
     */
    public function get() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$this->user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get Shipping Info Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate shipping fee based on province
     */
    public function calculateShippingFee($province) {
        return in_array($province, $this->freeShippingProvinces) ? 0 : $this->standardShippingFee;
    }

    /**
     * Get shipping fee
     */
    public function getShippingFee($province) {
        return $this->calculateShippingFee($province);
    }

    /**
     * Check if province has free shipping
     */
    public function isFreeShipping($province) {
        return in_array($province, $this->freeShippingProvinces);
    }

    /**
     * Validate shipping information
     */
    public static function validate($phone, $province, $address) {
        $errors = [];

        // Validate phone
        if (empty($phone)) {
            $errors[] = 'Số điện thoại là bắt buộc';
        } elseif (!preg_match('/^[0-9]{10,11}$/', preg_replace('/[\s\-\+]/', '', $phone))) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }

        // Validate province
        if (empty($province)) {
            $errors[] = 'Tỉnh thành là bắt buộc';
        } elseif (!in_array($province, self::getProvinces())) {
            $errors[] = 'Tỉnh thành không hợp lệ';
        }

        // Validate address
        if (empty($address)) {
            $errors[] = 'Địa chỉ là bắt buộc';
        } elseif (strlen($address) < 5) {
            $errors[] = 'Địa chỉ phải có ít nhất 5 ký tự';
        }

        return $errors;
    }
}
?>
