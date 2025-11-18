<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cart Class
 * Handles all shopping cart operations
 */
class Cart {
    private $db;
    private $table = 'cart';
    private $user_id;

    /**
     * Constructor
     */
    public function __construct($user_id = null) {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user_id = $user_id;
    }

    /**
     * Set user ID
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * Add product to cart
     */
    public function addItem($product_id, $quantity = 1) {
        try {
            if (!$this->user_id) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }

            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->user_id, $product_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                // Update quantity
                $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = quantity + ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$quantity, $item['id']]);
            } else {
                // Add new item
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$this->user_id, $product_id, $quantity]);
            }

            return ['success' => true, 'message' => 'Item added to cart'];
        } catch (PDOException $e) {
            error_log('Add to Cart Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem($item_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ? AND user_id = ?");
            $stmt->execute([$item_id, $this->user_id]);

            return ['success' => true, 'message' => 'Item removed from cart'];
        } catch (PDOException $e) {
            error_log('Remove from Cart Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($item_id, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($item_id);
            }

            $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $item_id, $this->user_id]);

            return ['success' => true, 'message' => 'Quantity updated'];
        } catch (PDOException $e) {
            error_log('Update Quantity Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Get cart items
     */
    public function getItems() {
        try {
            $query = "SELECT c.*, p.name, p.price, p.image, p.stock, p.category 
                      FROM {$this->table} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.user_id = ?
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get Cart Items Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get cart item count
     */
    public function getItemCount() {
        try {
            $stmt = $this->db->prepare("SELECT SUM(quantity) as count FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$this->user_id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log('Get Item Count Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get cart total
     */
    public function getTotal() {
        try {
            $query = "SELECT SUM(c.quantity * p.price) as total 
                      FROM {$this->table} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.user_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->user_id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('Get Total Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear cart
     */
    public function clear() {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$this->user_id]);

            return ['success' => true, 'message' => 'Cart cleared'];
        } catch (PDOException $e) {
            error_log('Clear Cart Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Check if item in cart
     */
    public function hasItem($product_id) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->user_id, $product_id]);

            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log('Check Item Error: ' . $e->getMessage());
            return false;
        }
    }
}
?>
