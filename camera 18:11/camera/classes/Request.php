<?php
/**
 * Request Class
 * Handles GET, POST, and other HTTP request data
 */
class Request {
    private $get = [];
    private $post = [];
    private $request = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->request = $_REQUEST ?? [];
    }
    
    /**
     * Get GET parameter
     * @param string $key Parameter key
     * @param mixed $default Default value if not found
     * @return mixed Parameter value
     */
    public function get($key, $default = null) {
        if (!isset($this->get[$key])) {
            return $default;
        }
        
        $value = $this->get[$key];
        
        // Handle arrays (for multi-select filters like brands[])
        if (is_array($value)) {
            return array_map('htmlspecialchars', $value);
        }
        
        // Handle strings
        return htmlspecialchars($value);
    }
    
    /**
     * Get POST parameter
     * @param string $key Parameter key
     * @param mixed $default Default value if not found
     * @return mixed Parameter value
     */
    public function post($key, $default = null) {
        if (!isset($this->post[$key])) {
            return $default;
        }
        
        $value = $this->post[$key];
        
        // Handle arrays
        if (is_array($value)) {
            return array_map('htmlspecialchars', $value);
        }
        
        // Handle strings
        return htmlspecialchars($value);
    }
    
    /**
     * Get REQUEST parameter
     * @param string $key Parameter key
     * @param mixed $default Default value if not found
     * @return mixed Parameter value
     */
    public function request($key, $default = null) {
        if (!isset($this->request[$key])) {
            return $default;
        }
        
        $value = $this->request[$key];
        
        // Handle arrays
        if (is_array($value)) {
            return array_map('htmlspecialchars', $value);
        }
        
        // Handle strings
        return htmlspecialchars($value);
    }
    
    /**
     * Check if GET key exists
     * @param string $key Parameter key
     * @return bool
     */
    public function hasGet($key) {
        return isset($this->get[$key]) && !empty($this->get[$key]);
    }
    
    /**
     * Check if POST key exists
     * @param string $key Parameter key
     * @return bool
     */
    public function hasPost($key) {
        return isset($this->post[$key]) && !empty($this->post[$key]);
    }
    
    /**
     * Get all GET parameters
     * @return array
     */
    public function getAllGet() {
        return $this->get;
    }
    
    /**
     * Get all POST parameters
     * @return array
     */
    public function getAllPost() {
        return $this->post;
    }
    
    /**
     * Get method (GET, POST, etc.)
     * @return string HTTP method
     */
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Check if request is POST
     * @return bool
     */
    public function isPost() {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Check if request is GET
     * @return bool
     */
    public function isGet() {
        return $this->getMethod() === 'GET';
    }
}
?>
