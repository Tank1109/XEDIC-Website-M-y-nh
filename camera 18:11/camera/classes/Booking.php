<?php
/**
 * Booking Class
 * Handles service booking operations
 */
class Booking {
    private $db;
    private $table = 'bookings';
    
    // Booking properties
    private $id;
    private $service_id;
    private $user_id;
    private $service_name;
    private $customer_name;
    private $customer_email;
    private $customer_phone;
    private $booking_date;
    private $booking_time;
    private $device_type;
    private $device_brand;
    private $device_model;
    private $device_condition;
    private $additional_notes;
    private $status;
    private $created_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        if (isset($GLOBALS['db'])) {
            $this->db = $GLOBALS['db'];
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
    }
    
    /**
     * Create new booking
     * @return bool Success status
     */
    public function create() {
        try {
            $query = "INSERT INTO {$this->table} 
                      (service_id, user_id, service_name, customer_name, customer_email, customer_phone, 
                       booking_date, booking_time, device_type, device_brand, device_model, 
                       device_condition, additional_notes, status)
                      VALUES 
                      (:service_id, :user_id, :service_name, :customer_name, :customer_email, :customer_phone,
                       :booking_date, :booking_time, :device_type, :device_brand, :device_model,
                       :device_condition, :additional_notes, :status)";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':service_id', $this->service_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':service_name', $this->service_name);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':customer_email', $this->customer_email);
            $stmt->bindParam(':customer_phone', $this->customer_phone);
            $stmt->bindParam(':booking_date', $this->booking_date);
            $stmt->bindParam(':booking_time', $this->booking_time);
            $stmt->bindParam(':device_type', $this->device_type);
            $stmt->bindParam(':device_brand', $this->device_brand);
            $stmt->bindParam(':device_model', $this->device_model);
            $stmt->bindParam(':device_condition', $this->device_condition);
            $stmt->bindParam(':additional_notes', $this->additional_notes);
            $stmt->bindParam(':status', $this->status);
            
            if ($stmt->execute()) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Booking create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking by ID
     * @param int $id Booking ID
     * @return array Booking data
     */
    public function getBookingById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        } catch (Exception $e) {
            error_log("Get booking error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all bookings for user
     * @param int $user_id User ID
     * @return array Array of bookings
     */
    public function getUserBookings($user_id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all bookings (for admin)
     * @return array Array of bookings
     */
    public function getAllBookings() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get all bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update booking status
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update booking status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete booking
     * @param int $id Booking ID
     * @return bool Success status
     */
    public function deleteBooking($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Delete booking error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get bookings by status
     * @param string $status Booking status
     * @return array Array of bookings
     */
    public function getBookingsByStatus($status) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE status = :status ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get bookings by status error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate booking data
     * @return array Errors array
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->customer_name)) {
            $errors[] = "Vui lòng nhập tên của bạn";
        }
        
        if (empty($this->customer_email) || !filter_var($this->customer_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Vui lòng nhập email hợp lệ";
        }
        
        if (empty($this->customer_phone)) {
            $errors[] = "Vui lòng nhập số điện thoại";
        }
        
        if (empty($this->booking_date)) {
            $errors[] = "Vui lòng chọn ngày đặt lịch";
        }
        
        if (empty($this->booking_time)) {
            $errors[] = "Vui lòng chọn giờ đặt lịch";
        }
        
        if (empty($this->device_type)) {
            $errors[] = "Vui lòng chọn loại thiết bị";
        }
        
        if (empty($this->device_brand)) {
            $errors[] = "Vui lòng nhập hãng sản xuất";
        }
        
        if (empty($this->device_model)) {
            $errors[] = "Vui lòng nhập model thiết bị";
        }
        
        if (empty($this->device_condition)) {
            $errors[] = "Vui lòng chọn tình trạng thiết bị";
        }
        
        return $errors;
    }
    
    /**
     * Format status to Vietnamese
     * @param string $status Status value
     * @return string Formatted status
     */
    public static function formatStatus($status) {
        $statuses = [
            'pending' => 'Đang chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'in_progress' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }
    
    /**
     * Get device condition options
     * @return array
     */
    public static function getDeviceConditions() {
        return [
            'good' => 'Bình thường',
            'minor_issue' => 'Có lỗi nhỏ',
            'major_issue' => 'Có lỗi lớn',
            'not_working' => 'Không hoạt động'
        ];
    }
    
    /**
     * Get device types
     * @return array
     */
    public static function getDeviceTypes() {
        return [
            'dslr' => 'Máy ảnh DSLR',
            'mirrorless' => 'Máy ảnh Mirrorless',
            'compact' => 'Máy ảnh Compact',
            'film' => 'Máy ảnh Film',
            'lens' => 'Ống Kính'
        ];
    }
    
    // Getters and Setters
    public function setServiceId($service_id) { $this->service_id = $service_id; }
    public function getServiceId() { return $this->service_id; }
    
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function getUserId() { return $this->user_id; }
    
    public function setServiceName($service_name) { $this->service_name = $service_name; }
    public function getServiceName() { return $this->service_name; }
    
    public function setCustomerName($customer_name) { $this->customer_name = $customer_name; }
    public function getCustomerName() { return $this->customer_name; }
    
    public function setCustomerEmail($customer_email) { $this->customer_email = $customer_email; }
    public function getCustomerEmail() { return $this->customer_email; }
    
    public function setCustomerPhone($customer_phone) { $this->customer_phone = $customer_phone; }
    public function getCustomerPhone() { return $this->customer_phone; }
    
    public function setBookingDate($booking_date) { $this->booking_date = $booking_date; }
    public function getBookingDate() { return $this->booking_date; }
    
    public function setBookingTime($booking_time) { $this->booking_time = $booking_time; }
    public function getBookingTime() { return $this->booking_time; }
    
    public function setDeviceType($device_type) { $this->device_type = $device_type; }
    public function getDeviceType() { return $this->device_type; }
    
    public function setDeviceBrand($device_brand) { $this->device_brand = $device_brand; }
    public function getDeviceBrand() { return $this->device_brand; }
    
    public function setDeviceModel($device_model) { $this->device_model = $device_model; }
    public function getDeviceModel() { return $this->device_model; }
    
    public function setDeviceCondition($device_condition) { $this->device_condition = $device_condition; }
    public function getDeviceCondition() { return $this->device_condition; }
    
    public function setAdditionalNotes($additional_notes) { $this->additional_notes = $additional_notes; }
    public function getAdditionalNotes() { return $this->additional_notes; }
    
    public function setStatus($status) { $this->status = $status; }
    public function getStatus() { return $this->status; }
}
?>
