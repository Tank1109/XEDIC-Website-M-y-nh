<?php
/**
 * Cart Controller
 * Handles cart-related business logic
 */
class CartController {
    private $cart;
    private $product;
    private $page;
    private $shipping;

    public function __construct() {
        // Verify user is logged in
        if (!isLoggedIn()) {
            header('Location: login.php');
            exit;
        }

        // Initialize classes
        $this->product = new Product();
        $this->page = new Page();
        
        // Initialize cart with user ID
        $user_id = $_SESSION['user_id'] ?? null;
        $this->cart = new Cart($user_id);
        $this->shipping = new ShippingInfo($user_id);
        
        // Set page title
        $this->page->setTitle('Giỏ Hàng - XEDIC Camera');
        $this->page->setDescription('Giỏ hàng của bạn');
    }

    /**
     * Initialize controller and return view data
     */
    public function init() {
        $cartItems = $this->cart->getItems();
        $cartTotal = $this->cart->getTotal();
        $itemCount = $this->cart->getItemCount();

        // Calculate subtotal and tax
        $subtotal = $cartTotal;
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $tax;

        // Get shipping information
        $shippingData = $this->shipping->get();
        $provinces = ShippingInfo::getProvinces();

        return [
            'page' => $this->page,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'itemCount' => $itemCount,
            'isEmpty' => empty($cartItems),
            'shippingData' => $shippingData,
            'provinces' => $provinces,
            'shipping' => $this->shipping
        ];
    }

    /**
     * Get cart items for display
     */
    public function getCartItems() {
        return $this->cart->getItems();
    }

    /**
     * Get cart total
     */
    public function getCartTotal() {
        return $this->cart->getTotal();
    }

    /**
     * Get item count
     */
    public function getItemCount() {
        return $this->cart->getItemCount();
    }

    /**
     * Get shipping instance
     */
    public function getShipping() {
        return $this->shipping;
    }
}
?>
