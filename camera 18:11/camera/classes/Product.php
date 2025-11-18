<?php
/**
 * Product Class
 * Handles all product-related operations
 */
class Product {
    private $db;
    private $table = 'products';
    
    // Product properties
    private $id;
    private $name;
    private $description;
    private $price;
    private $image;
    private $category;
    private $badge;
    private $isFeatured;
    private $stock;
    private $createdAt;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Get featured products
     * @param int $limit Number of products to fetch
     * @return array Array of products
     */
    public function getFeaturedProducts($limit = 3) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_featured = 1 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all products
     * @return array Array of all products
     */
    public function getAllProducts() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get product by ID
     * @param int $id Product ID
     * @return array Product data
     */
    public function getProductById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get products by category
     * @param string $category Category name
     * @return array Array of products
     */
    public function getProductsByCategory($category) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE category = :category 
                  ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search products
     * @param string $keyword Search keyword
     * @return array Array of products
     */
    public function searchProducts($keyword) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE name LIKE :keyword 
                  OR description LIKE :keyword 
                  ORDER BY created_at DESC";
        
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':keyword', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new product
     * @return bool Success status
     */
    public function create() {
        $query = "INSERT INTO {$this->table} 
                  (name, description, price, image, category, badge, is_featured, stock)
                  VALUES 
                  (:name, :description, :price, :image, :category, :badge, :is_featured, :stock)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->badge = htmlspecialchars(strip_tags($this->badge));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':badge', $this->badge);
        $stmt->bindParam(':is_featured', $this->isFeatured, PDO::PARAM_BOOL);
        $stmt->bindParam(':stock', $this->stock, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Update product
     * @return bool Success status
     */
    public function update() {
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      description = :description,
                      price = :price,
                      image = :image,
                      category = :category,
                      badge = :badge,
                      is_featured = :is_featured,
                      stock = :stock
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->badge = htmlspecialchars(strip_tags($this->badge));
        
        // Bind parameters
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':badge', $this->badge);
        $stmt->bindParam(':is_featured', $this->isFeatured, PDO::PARAM_BOOL);
        $stmt->bindParam(':stock', $this->stock, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete product
     * @return bool Success status
     */
    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Format price to Vietnamese currency
     * @param float $price Price value
     * @return string Formatted price
     */
    public static function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
    
    /**
     * Check if product is in stock
     * @return bool Stock status
     */
    public function isInStock() {
        return $this->stock > 0;
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
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setPrice($price) {
        $this->price = $price;
    }
    
    public function getPrice() {
        return $this->price;
    }
    
    public function setImage($image) {
        $this->image = $image;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function setCategory($category) {
        $this->category = $category;
    }
    
    public function getCategory() {
        return $this->category;
    }
    
    public function setBadge($badge) {
        $this->badge = $badge;
    }
    
    public function getBadge() {
        return $this->badge;
    }
    
    public function setIsFeatured($isFeatured) {
        $this->isFeatured = $isFeatured;
    }
    
    public function getIsFeatured() {
        return $this->isFeatured;
    }
    
    public function setStock($stock) {
        $this->stock = $stock;
    }
    
    public function getStock() {
        return $this->stock;
    }
}
?>