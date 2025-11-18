<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Page.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : null;

if (!$orderId) {
    header('Location: cart.php');
    exit;
}

// Page title
$page = new Page();
$page->setTitle('Đặt hàng thành công - XEDIC Camera');
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
    
    <!-- Order Success CSS -->
    <link href="css/order-success.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="order-success-page">
        <div class="container">
            <div class="success-container">
                <!-- Success Icon -->
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

                <!-- Success Message -->
                <h1>Đơn hàng đã được tạo thành công!</h1>
                <p class="success-message">
                    Cảm ơn bạn đã mua sắm tại XEDIC Camera. Chúng tôi đang xử lý đơn hàng của bạn.
                </p>

                <!-- Order Info -->
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">Mã đơn hàng:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderId); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày đặt hàng:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trạng thái:</span>
                        <span class="info-value">
                            <span class="badge bg-success">Chờ xác nhận</span>
                        </span>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="next-steps">
                    <h3>Bước tiếp theo:</h3>
                    <ol>
                        <li>Chúng tôi sẽ gửi email xác nhận đơn hàng trong vòng 24 giờ</li>
                        <li>Bạn có thể theo dõi tình trạng đơn hàng trong tài khoản của bạn</li>
                        <li>Sản phẩm sẽ được giao đến bạn trong thời gian như đã thỏa thuận</li>
                    </ol>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="profile.php?tab=orders" class="btn-primary-custom">
                        <i class="fas fa-box"></i> Xem đơn hàng của bạn
                    </a>
                    <a href="products.php" class="btn-secondary-custom">
                        <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                    </a>
                </div>

                <!-- Support Info -->
                <div class="support-info">
                    <h4>Cần hỗ trợ?</h4>
                    <p>
                        Nếu bạn có bất kỳ câu hỏi nào, vui lòng 
                        <a href="contact.php">liên hệ với chúng tôi</a> 
                        hoặc gọi hotline 
                        <strong>1900.1234</strong>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
