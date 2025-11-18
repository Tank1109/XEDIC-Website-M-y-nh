<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';
require_once 'classes/Request.php';
require_once 'classes/Cart.php';
require_once 'classes/ShippingInfo.php';
require_once 'controllers/CartController.php';

// Initialize controller
$controller = new CartController();
$viewData = $controller->init();

// Extract view data
extract($viewData);
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
    
    <!-- Cart Page Styles -->
    <link href="css/cart.css" rel="stylesheet">
    
    <!-- Shipping Styles -->
    <link href="css/shipping.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="cart-page">
        <div class="container">
            <div class="cart-header">
                <h1><i class="fas fa-shopping-cart">
                    
                </i>Giỏ Hàng</h1>
                <p class="cart-subtitle">Quản lý các sản phẩm bạn muốn mua</p>
            </div>

            <?php if ($isEmpty): ?>
                <!-- Empty Cart State -->
                <div class="empty-cart-state">
                    <div class="empty-cart-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h2>Giỏ hàng trống</h2>
                    <p>Hãy thêm một số sản phẩm để bắt đầu mua sắm</p>
                    <a href="products.php" class="btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i>Tiếp tục mua sắm
                    </a>
                </div>
            <?php else: ?>
                <!-- Cart Content -->
                <div class="row g-4">
                    <!-- Cart Items and Shipping Form -->
                    <div class="col-lg-8">
                        <div class="cart-items-container">
                            <div class="cart-items-header">
                                <h3><i class="fas fa-list"></i> Sản phẩm trong giỏ <span>(<?php echo $itemCount; ?>)</span></h3>
                            </div>

                            <div class="cart-items-list">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                                        <div class="item-image">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/150x150?text=No+Image'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>

                                        <div class="item-details">
                                            <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p class="item-category"><?php echo htmlspecialchars($item['category'] ?? 'Uncategorized'); ?></p>
                                            <p class="item-price"><?php echo Product::formatPrice($item['price']); ?></p>
                                        </div>

                                        <div class="item-quantity">
                                            <label for="qty-<?php echo $item['id']; ?>">Số lượng:</label>
                                            <div class="quantity-control">
                                                <button class="qty-btn qty-minus" onclick="updateQuantity(<?php echo $item['id']; ?>, this)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" id="qty-<?php echo $item['id']; ?>" 
                                                       class="qty-input" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock']; ?>" 
                                                       onchange="updateQuantity(<?php echo $item['id']; ?>, this)">
                                                <button class="qty-btn qty-plus" onclick="updateQuantity(<?php echo $item['id']; ?>, this)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="item-subtotal">
                                            <p class="subtotal-label">Thành tiền:</p>
                                            <p class="subtotal-price"><?php echo Product::formatPrice($item['price'] * $item['quantity']); ?></p>
                                        </div>

                                        <div class="item-actions">
                                            <button class="btn-remove" onclick="removeItem(<?php echo $item['id']; ?>)" title="Xóa khỏi giỏ">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Shipping Information Form -->
                        <div class="shipping-form-container">
                            <h3><i class="fas fa-truck"></i> Thông tin giao hàng</h3>

                            <div class="shipping-info-note">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Lưu ý:</strong> Giao hàng miễn phí tại Hà Nội, các tỉnh khác đồng giá 30.000đ
                                </div>
                            </div>

                            <div class="form-errors" id="shippingErrors"></div>
                            <div class="shipping-success" id="shippingSuccess">
                                <i class="fas fa-check-circle"></i>
                                <span id="successMessage">Lưu thông tin giao hàng thành công!</span>
                            </div>

                            <form id="shippingForm" onsubmit="submitShippingForm(event)">
                                <div class="form-group">
                                    <label for="phone">
                                        Số điện thoại <span class="required">*</span>
                                    </label>
                                    <input type="tel" id="phone" name="phone" 
                                           placeholder="Nhập số điện thoại" 
                                           value="<?php echo htmlspecialchars($shippingData['phone'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="province">
                                        Tỉnh/Thành phố <span class="required">*</span>
                                    </label>
                                    <select id="province" name="province" onchange="updateShippingFee()">
                                        <option value="">-- Chọn tỉnh/thành phố --</option>
                                        <?php foreach ($provinces as $prov): ?>
                                            <option value="<?php echo htmlspecialchars($prov); ?>" 
                                                    <?php echo ($shippingData['province'] ?? '') === $prov ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($prov); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="address">
                                        Địa chỉ nhận hàng <span class="required">*</span>
                                    </label>
                                    <input type="text" id="address" name="address" 
                                           placeholder="Nhập địa chỉ nhận hàng" 
                                           value="<?php echo htmlspecialchars($shippingData['address'] ?? ''); ?>">
                                </div>

                                <div class="shipping-fee-preview" id="shippingFeePreview">
                                    <i class="fas fa-info-circle"></i>
                                    <span id="shippingFeeText"></span>
                                </div>

                                <button type="submit" class="btn-submit-shipping">
                                    <i class="fas fa-save"></i> Lưu thông tin giao hàng
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h3>

                            <!-- Products in Summary -->
                            <div class="summary-products">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="summary-product-item">
                                        <div class="product-info">
                                            <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span class="product-qty">x<?php echo $item['quantity']; ?></span>
                                        </div>
                                        <span class="product-price"><?php echo Product::formatPrice($item['price'] * $item['quantity']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row shipping">
                                <span>Phí giao hàng:</span>
                                <span id="summaryShippingFee">
                                    <small style="color: #999;">Chọn tỉnh thành</small>
                                </span>
                            </div>

                            <div class="summary-row total">
                                <span>Tổng cộng:</span>
                                <span id="summaryTotal"><?php echo Product::formatPrice($subtotal); ?></span>
                            </div>

                            <a href="checkout.php" class="btn-checkout">
                                <i class="fas fa-credit-card"></i>Thanh toán
                            </a>

                            <a href="products.php" class="btn-continue-shopping">
                                <i class="fas fa-arrow-left"></i>Tiếp tục mua sắm
                            </a>

                            <div class="cart-actions">
                                <button class="btn-clear-cart" onclick="clearCart()">
                                    <i class="fas fa-trash-alt"></i>Xóa tất cả
                                </button>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="payment-info">
                            <h4><i class="fas fa-shield-alt"></i> Thông tin thanh toán</h4>
                            <ul>
                                <li><i class="fas fa-check-circle"></i> Giao hàng miễn phí (Hà Nội)</li>
                                <li><i class="fas fa-check-circle"></i> Giao hàng 30.000đ (Các tỉnh khác)</li>
                                <li><i class="fas fa-check-circle"></i> Bảo hành 12 tháng</li>
                                <li><i class="fas fa-check-circle"></i> Hoàn tiền 30 ngày</li>
                                <li><i class="fas fa-check-circle"></i> Hỗ trợ 24/7</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global variables
        const subtotalAmount = <?php echo $subtotal; ?>;
        let currentShippingFee = 0;

        // Load available provinces on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a selected province
            const provinceSelect = document.getElementById('province');
            if (provinceSelect && provinceSelect.value) {
                updateShippingFee();
            }
        });

        // Submit shipping form
        function submitShippingForm(event) {
            event.preventDefault();

            const phone = document.getElementById('phone').value.trim();
            const province = document.getElementById('province').value.trim();
            const address = document.getElementById('address').value.trim();
            const errorsContainer = document.getElementById('shippingErrors');
            const successContainer = document.getElementById('shippingSuccess');

            // Hide previous messages
            errorsContainer.classList.remove('show');
            errorsContainer.innerHTML = '';
            successContainer.classList.remove('show');

            // Validate inputs
            const errors = validateShippingForm(phone, province, address);
            if (errors.length > 0) {
                showShippingErrors(errors);
                return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('province', province);
            formData.append('address', address);

            // Submit form
            const submitBtn = document.querySelector('.btn-submit-shipping');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            fetch('api/save-shipping-info.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                if (data.success) {
                    // Show success message
                    successContainer.classList.add('show');
                    document.getElementById('successMessage').textContent = data.message;

                    // Update shipping fee display
                    updateShippingFeeDisplay(data.shippingFee, data.isFreeShipping);

                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        successContainer.classList.remove('show');
                    }, 3000);
                } else {
                    showShippingErrors([data.message]);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                showShippingErrors(['Lỗi kết nối. Vui lòng thử lại.']);
            });
        }

        // Update shipping fee preview
        function updateShippingFee() {
            const province = document.getElementById('province').value;
            
            if (!province) {
                document.getElementById('shippingFeePreview').classList.remove('show');
                return;
            }

            // Fetch shipping fee from API
            const formData = new FormData();
            formData.append('province', province);

            fetch('api/save-shipping-info.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateShippingFeeDisplay(data.shippingFee, data.isFreeShipping);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Update shipping fee display in UI
        function updateShippingFeeDisplay(fee, isFree) {
            currentShippingFee = fee;
            const preview = document.getElementById('shippingFeePreview');
            const text = document.getElementById('shippingFeeText');
            const summaryShippingFee = document.getElementById('summaryShippingFee');

            if (isFree) {
                preview.className = 'shipping-fee-preview show free';
                text.innerHTML = '<i class="fas fa-check-circle"></i> Giao hàng miễn phí tại Hà Nội';
                summaryShippingFee.innerHTML = '<span style="color: #28a745; font-weight: 600;">Miễn phí</span>';
            } else {
                preview.className = 'shipping-fee-preview show paid';
                text.innerHTML = '<i class="fas fa-info-circle"></i> Phí giao hàng: ' + formatPrice(fee);
                summaryShippingFee.innerHTML = formatPrice(fee);
            }

            // Update total
            updateOrderTotal();
        }

        // Update order total
        function updateOrderTotal() {
            const total = subtotalAmount + currentShippingFee;
            document.getElementById('summaryTotal').textContent = formatPrice(total);
        }

        // Validate shipping form
        function validateShippingForm(phone, province, address) {
            const errors = [];

            // Validate phone
            if (!phone) {
                errors.push('Số điện thoại là bắt buộc');
            } else if (!/^[0-9]{10,11}$/.test(phone.replace(/[\s\-\+]/g, ''))) {
                errors.push('Số điện thoại phải có 10-11 chữ số');
            }

            // Validate province
            if (!province) {
                errors.push('Tỉnh/Thành phố là bắt buộc');
            }

            // Validate address
            if (!address) {
                errors.push('Địa chỉ là bắt buộc');
            } else if (address.length < 5) {
                errors.push('Địa chỉ phải có ít nhất 5 ký tự');
            }

            return errors;
        }

        // Show shipping errors
        function showShippingErrors(errors) {
            const errorsContainer = document.getElementById('shippingErrors');
            let html = '<ul>';
            errors.forEach(error => {
                html += '<li>' + error + '</li>';
            });
            html += '</ul>';
            
            errorsContainer.innerHTML = html;
            errorsContainer.classList.add('show');
            
            // Scroll to errors
            errorsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Format price
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Update quantity
        function updateQuantity(itemId, element) {
            let newQuantity;
            
            if (element.classList.contains('qty-minus')) {
                const input = element.nextElementSibling;
                newQuantity = Math.max(1, parseInt(input.value) - 1);
                input.value = newQuantity;
            } else if (element.classList.contains('qty-plus')) {
                const input = element.previousElementSibling;
                newQuantity = parseInt(input.value) + 1;
                input.value = newQuantity;
            } else {
                newQuantity = parseInt(element.value);
            }

            // Send update to server
            fetch('api/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'item_id=' + itemId + '&quantity=' + newQuantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification('Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                showNotification('Có lỗi xảy ra!', 'error');
            });
        }

        // Remove item
        function removeItem(itemId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('api/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'item_id=' + itemId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification('Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra!', 'error');
                });
            }
        }

        // Clear cart
        function clearCart() {
            if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
                fetch('api/clear-cart.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification('Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra!', 'error');
                });
            }
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = `
                top: 100px;
                right: 20px;
                z-index: 9999;
                min-width: 280px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease;
                background: ${type === 'success' ? '#4CAF50' : '#F44336'};
                border: none;
                border-radius: 4px;
                color: white;
                font-weight: 600;
                padding: 14px 20px;
                font-size: 0.9rem;
            `;
            notification.innerHTML = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add CSS animations if not already present
        if (!document.querySelector('style[data-animations="true"]')) {
            const style = document.createElement('style');
            style.setAttribute('data-animations', 'true');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>
