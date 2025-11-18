<?php

class Database {
    private $host = "localhost";
    private $db_name = "camera_db";
    private $username = "root";
    private $password = "root"; // XAMPP uses empty password by default
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8mb4");
            $this->conn->exec("SET CHARACTER SET utf8mb4");
            $this->conn->exec("SET CHARACTER_SET_CONNECTION=utf8mb4");
            $this->conn->exec("SET collation_connection=utf8mb4_unicode_ci");
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            return null;
        }

        return $this->conn;
    }

}

