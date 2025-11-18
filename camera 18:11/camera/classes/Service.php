<?php
/**
 * Service Class
 * Handles all service-related operations
 */
class Service {
    private $db;
    private $table = 'services';
    
    // Service properties
    private $id;
    private $name;
    private $description;
    private $price;
    private $icon;
    private $category;
    private $duration;
    private $warranty;
    private $is_active;
    private $created_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Database connection can be passed or created here
        if (isset($GLOBALS['db'])) {
            $this->db = $GLOBALS['db'];
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
    }
    
    /**
     * Get all active services
     * @return array Array of services
     */
    public function getAllServices() {
        try {
            // Kiểm tra nếu table tồn tại
            if (!$this->tableExists()) {
                return $this->getDefaultServices();
            }
            
            $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Nếu không có dữ liệu trong DB, trả về default
            if (empty($result)) {
                return $this->getDefaultServices();
            }
            
            return $result;
        } catch (Exception $e) {
            return $this->getDefaultServices();
        }
    }
    
    /**
     * Get service by ID
     * @param int $id Service ID
     * @return array|null Service data or null
     */
    public function getServiceById($id) {
        if (!$this->tableExists()) {
            $services = $this->getDefaultServices();
            foreach ($services as $service) {
                if ($service['id'] == $id) return $service;
            }
            return [];
        }
        
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id AND is_active = 1 LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get services by category
     * @param string $category Service category
     * @return array Array of services
     */
    public function getServicesByCategory($category) {
        if (!$this->tableExists()) {
            $services = $this->getDefaultServices();
            $result = [];
            foreach ($services as $service) {
                if ($service['category'] == $category) {
                    $result[] = $service;
                }
            }
            return $result;
        }
        
        try {
            $query = "SELECT * FROM {$this->table} 
                      WHERE category = :category AND is_active = 1 
                      ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category', $category);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Check if services table exists
     * @return bool
     */
    private function tableExists() {
        try {
            if ($this->db === null) return false;
            $result = $this->db->query("SELECT 1 FROM {$this->table} LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get default services (when DB is not available)
     * @return array
     */
    public function getDefaultServices() {
        return [
            [
                'id' => 1,
                'name' => 'Vệ Sinh Máy Ảnh',
                'description' => 'Dịch vụ vệ sinh chuyên nghiệp cho các loại máy ảnh DSLR, Mirrorless và máy ảnh compact. Chúng tôi sử dụng những dụng cụ và chất vệ sinh an toàn, không gây hại đến các linh kiện nhạy cảm của máy ảnh. Quá trình vệ sinh bao gồm: làm sạch thân máy, vệ sinh cảm biến, làm sạch gương lật, và kiểm tra các chức năng hoạt động.',
                'price' => 500000,
                'icon' => 'fas fa-soap',
                'category' => 'Bảo trì',
                'duration' => '2-3 giờ',
                'warranty' => '30 ngày',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Tư Vấn Chọn Thiết Bị',
                'description' => 'Dịch vụ tư vấn chuyên sâu giúp bạn lựa chọn thiết bị camera, ống kính, và phụ kiện phù hợp nhất với nhu cầu của mình. Đội tư vấn viên chuyên nghiệp của chúng tôi sẽ tìm hiểu yêu cầu của bạn, so sánh các lựa chọn, và đưa ra khuyến nghị chi tiết. Dịch vụ này phù hợp cho những người mới bắt đầu cũng như những chuyên gia muốn nâng cấp thiết bị.',
                'price' => 0,
                'icon' => 'fas fa-comments',
                'category' => 'Tư vấn',
                'duration' => '1-2 giờ',
                'warranty' => 'Miễn phí',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Bảo Hiểm Thiết Bị',
                'description' => 'Gói bảo hiểm toàn diện cho thiết bị camera, ống kính, và phụ kiện của bạn. Bảo hiểm bao gồm: bảo vệ chống hư hỏng do tai nạn, mất cắp, nước, và các sự cố khác. Với gói bảo hiểm này, bạn có thể yên tâm sử dụng thiết bị mà không lo lắng về rủi ro tài chính. Liên hệ chúng tôi để biết chi tiết về các gói bảo hiểm và mức phí.',
                'price' => 2000000,
                'icon' => 'fas fa-heart',
                'category' => 'Bảo vệ',
                'duration' => 'Thương lượng',
                'warranty' => '12 tháng',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Format price to Vietnamese currency
     * @param float $price Price value
     * @return string Formatted price
     */
    public static function formatPrice($price) {
        if ($price == 0) {
            return 'Miễn phí';
        }
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
    
    // Getters and Setters
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }
    
    public function setDescription($description) { $this->description = $description; }
    public function getDescription() { return $this->description; }
    
    public function setPrice($price) { $this->price = $price; }
    public function getPrice() { return $this->price; }
    
    public function setIcon($icon) { $this->icon = $icon; }
    public function getIcon() { return $this->icon; }
    
    public function setCategory($category) { $this->category = $category; }
    public function getCategory() { return $this->category; }
    
    public function setDuration($duration) { $this->duration = $duration; }
    public function getDuration() { return $this->duration; }
    
    public function setWarranty($warranty) { $this->warranty = $warranty; }
    public function getWarranty() { return $this->warranty; }
    
    public function setIsActive($is_active) { $this->is_active = $is_active; }
    public function getIsActive() { return $this->is_active; }
}
?>
