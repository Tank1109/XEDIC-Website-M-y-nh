<?php
/**
 * ServiceBooking Class
 * Handles service booking operations
 */
class ServiceBooking {
    private $db;
    private $table = 'service_bookings';
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Create new service booking
     * @param array $data Booking data
     * @return int|false Booking ID or false
     */
    public function createBooking($data) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (service_id, user_id, full_name, email, phone, notes, appointment_date, status)
                      VALUES 
                      (:service_id, :user_id, :full_name, :email, :phone, :notes, :appointment_date, 'new')";
            
            $stmt = $this->db->prepare($query);
            
            // Bind values
            $stmt->bindParam(':service_id', $data['service_id'], PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':appointment_date', $data['appointment_date']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get booking by ID
     * @param int $id Booking ID
     * @return array|null Booking data or null
     */
    public function getBookingById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all bookings for a service
     * @param int $serviceId Service ID
     * @return array Bookings array
     */
    public function getBookingsByService($serviceId) {
        try {
            $query = "SELECT * FROM {$this->table} 
                      WHERE service_id = :service_id 
                      ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all bookings for a user
     * @param int $userId User ID
     * @return array Bookings array
     */
    public function getBookingsByUser($userId) {
        try {
            $query = "SELECT sb.*, s.name as service_name, s.price as service_price
                      FROM {$this->table} sb
                      JOIN services s ON sb.service_id = s.id
                      WHERE sb.user_id = :user_id 
                      ORDER BY sb.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update booking status
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool Success or failure
     */
    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete booking
     * @param int $id Booking ID
     * @return bool Success or failure
     */
    public function deleteBooking($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
