<?php
/**
 * Brand Class
 * Handles all brand-related operations
 */
class Brand {
    private $db;
    private $table = 'brands';
    
    // Brand properties
    private $id;
    private $name;
    private $slug;
    private $description;
    private $logo;
    private $isActive;
    private $displayOrder;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Get all active brands
     * @return array Array of brands
     */
    public function getAllBrands() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 
                  ORDER BY display_order ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get brand by ID
     * @param int $id Brand ID
     * @return array Brand data
     */
    public function getBrandById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get brand by slug
     * @param string $slug Brand slug
     * @return array Brand data
     */
    public function getBrandBySlug($slug) {
        $query = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new brand
     * @return bool Success status
     */
    public function create() {
        $query = "INSERT INTO {$this->table} 
                  (name, slug, description, logo, is_active, display_order)
                  VALUES 
                  (:name, :slug, :description, :logo, :is_active, :display_order)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':is_active', $this->isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':display_order', $this->displayOrder, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Update brand
     * @return bool Success status
     */
    public function update() {
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      slug = :slug,
                      description = :description,
                      logo = :logo,
                      is_active = :is_active,
                      display_order = :display_order
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        
        // Bind parameters
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':is_active', $this->isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':display_order', $this->displayOrder, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete brand
     * @return bool Success status
     */
    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    // Getters and Setters
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setSlug($slug) {
        $this->slug = $slug;
    }
    
    public function getSlug() {
        return $this->slug;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setLogo($logo) {
        $this->logo = $logo;
    }
    
    public function getLogo() {
        return $this->logo;
    }
    
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }
    
    public function getIsActive() {
        return $this->isActive;
    }
    
    public function setDisplayOrder($displayOrder) {
        $this->displayOrder = $displayOrder;
    }
    
    public function getDisplayOrder() {
        return $this->displayOrder;
    }
}
?>
