<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';
require_once 'classes/Cart.php';
require_once 'classes/ShippingInfo.php';
require_once 'classes/Payment.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

// Get user data
$userId = $_SESSION['user_id'];

// Get cart items
$cart = new Cart($userId);
$cartItems = $cart->getItems();
$itemCount = count($cartItems);
$subtotal = $cart->getTotal();

// Check if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Get shipping info
$shippingInfo = new ShippingInfo($userId);
$shippingData = $shippingInfo->get();

// Get payment methods
$payment = new Payment($db);
$paymentMethods = Payment::getPaymentMethods();

// Page title
$page = new Page();
$page->setTitle('Thanh toán - XEDIC Camera');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page->getTitle()); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <!-- Checkout CSS -->
    <link href="css/checkout.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="checkout-page">
        <div class="container">
            <!-- Progress Bar -->
            <div class="checkout-progress">
                <div class="progress-step completed">
                    <div class="step-number">1</div>
                    <div class="step-title">Giỏ hàng</div>
                </div>
                <div class="progress-line completed"></div>
                <div class="progress-step active">
                    <div class="step-number">2</div>
                    <div class="step-title">Thanh toán</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-title">Hoàn tất</div>
                </div>
            </div>

            <div class="checkout-header">
                <h1><i class="fas fa-credit-card"></i> Thanh toán</h1>
                <p>Chọn phương thức thanh toán để hoàn tất đơn hàng</p>
            </div>

            <div class="row g-4">
                <!-- Payment Methods -->
                <div class="col-lg-8">
                    <div class="payment-methods-container">
                        <h3><i class="fas fa-wallet"></i> Phương thức thanh toán</h3>

                        <form id="paymentForm">
                            <div class="payment-methods-list">
                                <?php foreach ($paymentMethods as $method): ?>
                                    <label class="payment-method-card">
                                        <input type="radio" name="payment_method" 
                                               value="<?php echo htmlspecialchars($method['id']); ?>" 
                                               class="payment-radio"
                                               data-method="<?php echo htmlspecialchars($method['id']); ?>">
                                        
                                        <div class="method-content">
                                            <div class="method-header">
                                                <i class="<?php echo htmlspecialchars($method['icon']); ?>"></i>
                                                <div class="method-info">
                                                    <h4><?php echo htmlspecialchars($method['name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($method['description']); ?></p>
                                                </div>
                                            </div>
                                            <div class="method-badge">
                                                <span class="badge-text">Chọn</span>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="payment-terms">
                                <label class="terms-checkbox">
                                    <input type="checkbox" id="agreeTerms" name="agree_terms" required>
                                    <span>Tôi đồng ý với <a href="#">điều khoản</a> và <a href="#">chính sách bảo mật</a></span>
                                </label>
                            </div>

                            <button type="submit" class="btn-confirm-payment" disabled>
                                <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                            </button>

                            <a href="cart.php" class="btn-back-cart">
                                <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h3><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h3>

                        <!-- Products -->
                        <div class="summary-products-checkout">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-product-item-checkout">
                                    <div class="product-info-checkout">
                                        <span class="product-name-checkout"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="product-qty-checkout">x<?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="product-price-checkout"><?php echo Product::formatPrice($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-divider-checkout"></div>

                        <!-- Shipping Info -->
                        <div class="summary-section">
                            <h4>Thông tin giao hàng</h4>
                            <?php if ($shippingData): ?>
                                <div class="shipping-details">
                                    <p><strong>Tên:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'N/A'); ?></p>
                                    <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($shippingData['phone'] ?? 'N/A'); ?></p>
                                    <p><strong>Tỉnh/Thành phố:</strong> <?php echo htmlspecialchars($shippingData['province'] ?? 'N/A'); ?></p>
                                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($shippingData['address'] ?? 'N/A'); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Vui lòng hoàn thành thông tin giao hàng trong giỏ hàng
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="summary-divider-checkout"></div>

                        <!-- Price Summary -->
                        <div class="price-summary">
                            <div class="summary-row-checkout subtotal">
                                <span>Tổng sản phẩm:</span>
                                <span><?php echo Product::formatPrice($subtotal); ?></span>
                            </div>

                            <div class="summary-row-checkout shipping">
                                <span>Phí giao hàng:</span>
                                <span id="shippingFeeSummary">
                                    <?php 
                                    if ($shippingData) {
                                        $shippingInfoObj = new ShippingInfo($userId);
                                        $fee = $shippingInfoObj->calculateShippingFee($shippingData['province']);
                                        echo Product::formatPrice($fee);
                                    } else {
                                        echo '<small style="color: #999;">Chưa xác định</small>';
                                    }
                                    ?>
                                </span>
                            </div>

                            <div class="summary-row-checkout total">
                                <span>Tổng cộng:</span>
                                <span id="totalAmount">
                                    <?php 
                                    $shippingFee = 0;
                                    if ($shippingData) {
                                        $shippingInfoObj = new ShippingInfo($userId);
                                        $shippingFee = $shippingInfoObj->calculateShippingFee($shippingData['province']);
                                    }
                                    echo Product::formatPrice($subtotal + $shippingFee);
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Guarantees -->
                        <div class="order-guarantees">
                            <div class="guarantee-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Thanh toán an toàn</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="fas fa-redo"></i>
                                <span>Hoàn tiền 30 ngày</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="fas fa-headset"></i>
                                <span>Hỗ trợ 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Payment form handler
        const paymentForm = document.getElementById('paymentForm');
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const agreeTerms = document.getElementById('agreeTerms');
        const confirmBtn = document.querySelector('.btn-confirm-payment');

        // Enable/disable confirm button
        function updateButtonState() {
            const methodSelected = Array.from(paymentRadios).some(r => r.checked);
            const termsAgreed = agreeTerms.checked;
            confirmBtn.disabled = !(methodSelected && termsAgreed);
        }

        paymentRadios.forEach(radio => {
            radio.addEventListener('change', updateButtonState);
        });

        agreeTerms.addEventListener('change', updateButtonState);

        // Form submission
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const selectedMethod = Array.from(paymentRadios).find(r => r.checked)?.value;
            const shippingInfo = <?php echo $shippingData ? 'true' : 'false'; ?>;

            if (!selectedMethod) {
                showNotification('Vui lòng chọn phương thức thanh toán', 'error');
                return;
            }

            if (!shippingInfo) {
                showNotification('Vui lòng hoàn thành thông tin giao hàng', 'error');
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            // Submit to checkout API
            fetch('api/process-checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'payment_method=' + selectedMethod
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Check if it's a transfer payment (VNPay or Momo)
                    if (selectedMethod === 'vnpay' || selectedMethod === 'momo') {
                        // Redirect to transfer payment page
                        window.location.href = 'transfer-payment.php?order_id=' + encodeURIComponent(data.order_id) + '&method=' + selectedMethod;
                    } else if (data.redirect_url) {
                        // For VNPay gateway, redirect to payment gateway
                        window.location.href = data.redirect_url;
                    } else {
                        // For COD, redirect to success page
                        window.location.href = 'order-success.php?order_id=' + data.order_id;
                    }
                } else {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';
                    showNotification(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';
                showNotification('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
        });

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                padding: 16px 20px;
                border-radius: 8px;
                background: ${type === 'success' ? '#10B981' : '#EF4444'};
                color: white;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease;
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add animations
        if (!document.querySelector('style[data-checkout-animations]')) {
            const style = document.createElement('style');
            style.setAttribute('data-checkout-animations', 'true');
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>
