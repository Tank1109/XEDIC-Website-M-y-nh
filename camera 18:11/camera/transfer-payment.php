<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Page.php';
require_once 'classes/Cart.php';
require_once 'classes/ShippingInfo.php';
require_once 'classes/Product.php';
require_once 'classes/TransferPayment.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=transfer-payment.php');
    exit;
}

// Get parameters
$orderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : null;
$method = isset($_GET['method']) ? trim($_GET['method']) : null;

if (!$orderId || !$method || !in_array($method, ['vnpay', 'momo'])) {
    header('Location: checkout.php');
    exit;
}

// Initialize
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['user_id'];

// Get cart data
$cart = new Cart($userId);
$cartItems = $cart->getItems();
$subtotal = $cart->getTotal();

// Get shipping info
$shippingInfo = new ShippingInfo($userId);
$shippingData = $shippingInfo->get();

// Calculate total
$shippingFee = 0;
if ($shippingData) {
    $shippingFee = $shippingInfo->calculateShippingFee($shippingData['province']);
}
$totalAmount = $subtotal + $shippingFee;

// Get account info
$transferPayment = new TransferPayment($database);
$accountInfo = TransferPayment::getAccountInfo($method);

if (!$accountInfo) {
    header('Location: checkout.php');
    exit;
}

// Generate QR data
$transferPayment->setMethod($method);
$transferPayment->setAmount($totalAmount);
$transferPayment->setOrderId($orderId);
$transferPayment->setUserId($userId);

$qrData = $transferPayment->generateQRData();
$transferCommand = $transferPayment->generateTransferCommand();

// Save transfer request
$saveResult = $transferPayment->saveTransferRequest();
error_log('Save Transfer Request Result: ' . json_encode($saveResult));

// Page title
$page = new Page();
$page->setTitle('Thanh toán chuyển khoản - XEDIC Camera');
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
    
    <!-- Transfer Payment CSS -->
    <link href="css/transfer-payment.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="transfer-payment-page">
        <div class="container">
            <div class="transfer-header">
                <h1>
                    <i class="fas fa-<?php echo $method === 'vnpay' ? 'money-check' : 'wallet'; ?>"></i>
                    Thanh toán chuyển khoản
                </h1>
                <p>Quét mã QR hoặc chuyển khoản bằng tay</p>
            </div>

            <div class="row g-4">
                <!-- QR Code Section -->
                <div class="col-lg-6">
                    <div class="transfer-container">
                        <!-- Bank Info Card -->
                        <div class="bank-info-card">
                            <div class="bank-header">
                                <i class="fas fa-<?php echo $method === 'vnpay' ? 'bank' : 'mobile-alt'; ?>"></i>
                                <h3><?php echo htmlspecialchars($accountInfo['bank']); ?></h3>
                            </div>

                            <!-- QR Code -->
                            <div class="qr-code-container">
                                <div class="qr-code">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode(json_encode($qrData)); ?>" 
                                         alt="QR Code" class="qr-image">
                                </div>
                                <p class="qr-instruction">
                                    <i class="fas fa-info-circle"></i>
                                    Dùng ứng dụng ngân hàng hoặc ví để quét mã này
                                </p>
                            </div>

                            <!-- Account Details -->
                            <div class="account-details">
                                <div class="detail-item">
                                    <span class="label">Tên tài khoản:</span>
                                    <span class="value"><?php echo htmlspecialchars($accountInfo['accountName']); ?></span>
                                    <button class="btn-copy" onclick="copyToClipboard(this, '<?php echo htmlspecialchars($accountInfo['accountName']); ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>

                                <?php if ($method === 'vnpay'): ?>
                                    <div class="detail-item">
                                        <span class="label">Số tài khoản:</span>
                                        <span class="value"><?php echo htmlspecialchars($accountInfo['accountNumber']); ?></span>
                                        <button class="btn-copy" onclick="copyToClipboard(this, '<?php echo htmlspecialchars($accountInfo['accountNumber']); ?>')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <span class="label">Số điện thoại:</span>
                                        <span class="value"><?php echo htmlspecialchars($accountInfo['accountNumber']); ?></span>
                                        <button class="btn-copy" onclick="copyToClipboard(this, '<?php echo htmlspecialchars($accountInfo['accountNumber']); ?>')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <div class="detail-item">
                                    <span class="label">Số tiền:</span>
                                    <span class="value amount"><?php echo Product::formatPrice($totalAmount); ?></span>
                                    <button class="btn-copy" onclick="copyToClipboard(this, '<?php echo (int)$totalAmount; ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>

                                <div class="detail-item">
                                    <span class="label">Nội dung chuyển:</span>
                                    <span class="value"><?php echo htmlspecialchars(str_replace('{ORDER_ID}', $orderId, 'DH{ORDER_ID}')); ?></span>
                                    <button class="btn-copy" onclick="copyToClipboard(this, '<?php echo htmlspecialchars(str_replace('{ORDER_ID}', $orderId, 'DH{ORDER_ID}')); ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Copy All -->
                            <button class="btn-copy-all" onclick="copyAllDetails()">
                                <i class="fas fa-copy"></i> Sao chép tất cả
                            </button>
                        </div>

                        <!-- Transfer Steps -->
                        <div class="transfer-steps">
                            <h4><i class="fas fa-list-ol"></i> Hướng dẫn chuyển khoản</h4>
                            <ol>
                                <li>Mở ứng dụng ngân hàng hoặc <?php echo $method === 'vnpay' ? 'VNPay' : 'Momo'; ?></li>
                                <li>Chọn "Chuyển tiền" hoặc quét mã QR</li>
                                <li>Nhập số tiền: <strong><?php echo Product::formatPrice($totalAmount); ?></strong></li>
                                <li>Nhập nội dung: <strong>DH<?php echo htmlspecialchars($orderId); ?></strong></li>
                                <li>Xác nhận và hoàn tất chuyển khoản</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Section -->
                <div class="col-lg-6">
                    <div class="order-summary-transfer">
                        <h3><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h3>

                        <!-- Products -->
                        <div class="summary-products-transfer">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item-transfer">
                                    <div class="item-info">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="item-price"><?php echo Product::formatPrice($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="divider"></div>

                        <!-- Pricing -->
                        <div class="pricing-summary">
                            <div class="row-summary">
                                <span>Tổng sản phẩm:</span>
                                <span><?php echo Product::formatPrice($subtotal); ?></span>
                            </div>
                            <div class="row-summary">
                                <span>Phí giao hàng:</span>
                                <span><?php echo Product::formatPrice($shippingFee); ?></span>
                            </div>
                            <div class="row-summary total">
                                <span>Tổng cộng:</span>
                                <span><?php echo Product::formatPrice($totalAmount); ?></span>
                            </div>
                        </div>

                        <!-- Confirmation Section -->
                        <div class="confirmation-section">
                            <h4>Xác nhận thanh toán</h4>
                            
                            <div class="confirmation-status">
                                <div class="status-item">
                                    <div class="status-icon pending">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="status-text">
                                        <strong>Chờ xác nhận</strong>
                                        <p>Bạn đã chuyển khoản thành công?</p>
                                    </div>
                                </div>
                            </div>

                            <div class="confirmation-buttons">
                                <button class="btn-confirm-transfer" onclick="confirmTransfer()">
                                    <i class="fas fa-check-circle"></i> Đã chuyển khoản
                                </button>
                                <a href="checkout.php" class="btn-change-method">
                                    <i class="fas fa-arrow-left"></i> Thay đổi phương thức
                                </a>
                            </div>

                            <!-- Success Message -->
                            <div class="alert alert-success d-none" id="successAlert">
                                <i class="fas fa-check-circle"></i>
                                <span>Cảm ơn bạn! Chúng tôi đang xác nhận thanh toán của bạn.</span>
                            </div>

                            <!-- Info Message -->
                            <div class="transfer-info">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Lưu ý:</strong> 
                                    Đây là dự án giáo dục. Bạn chỉ cần nhấn nút "Đã chuyển khoản" để mô phỏng quá trình thanh toán. 
                                    Không cần thực hiện chuyển khoản thực tế.
                                </div>
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
        // Copy to clipboard
        function copyToClipboard(btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.add('copied');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }

        // Copy all details
        function copyAllDetails() {
            const accountName = '<?php echo htmlspecialchars($accountInfo['accountName']); ?>';
            const accountNumber = '<?php echo htmlspecialchars($accountInfo['accountNumber']); ?>';
            const amount = '<?php echo (int)$totalAmount; ?>';
            const description = 'DH<?php echo htmlspecialchars($orderId); ?>';

            const allText = `
Tên tài khoản: ${accountName}
<?php echo $method === 'vnpay' ? 'Số tài khoản' : 'Số điện thoại'; ?>: ${accountNumber}
Số tiền: ${amount}
Nội dung: ${description}
            `.trim();

            navigator.clipboard.writeText(allText).then(() => {
                showNotification('Đã sao chép tất cả thông tin!');
            });
        }

        // Confirm transfer
        function confirmTransfer() {
            const orderId = '<?php echo htmlspecialchars($orderId); ?>';
            const btn = document.querySelector('.btn-confirm-transfer');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác nhận...';

            // Mô phỏng xử lý
            setTimeout(() => {
                console.log('Confirming transfer with order_id:', orderId);
                
                fetch('./api/confirm-transfer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response text:', text);
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.message || 'Request failed with status ' + response.status);
                            } catch (e) {
                                throw new Error('Request failed with status ' + response.status + ': ' + text);
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        document.getElementById('successAlert').classList.remove('d-none');
                        setTimeout(() => {
                            window.location.href = './order-success.php?order_id=' + encodeURIComponent(orderId);
                        }, 2000);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Đã chuyển khoản';
                        showNotification(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Đã chuyển khoản';
                    showNotification(error.message || 'Lỗi kết nối. Vui lòng thử lại.', 'error');
                });
            }, 1500);
        }

        // Notification
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
        if (!document.querySelector('style[data-transfer-animations]')) {
            const style = document.createElement('style');
            style.setAttribute('data-transfer-animations', 'true');
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
