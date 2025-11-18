<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/bookings.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id === 0) {
    header('Location: bookings.php');
    exit;
}

// Get booking details
$stmt = $db->prepare("SELECT sb.*, COALESCE(u.full_name, sb.full_name) as customer_name, u.email as user_email, s.name as service_name, s.price as service_price, s.description as service_description
                      FROM service_bookings sb 
                      LEFT JOIN users u ON sb.user_id = u.id 
                      LEFT JOIN services s ON sb.service_id = s.id 
                      WHERE sb.id = :id LIMIT 1");
$stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
        $status = $_POST['status'];
        $stmt = $db->prepare("UPDATE service_bookings SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $booking['status'] = $status;
            $success_message = "Cập nhật trạng thái thành công!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đặt Lịch Dịch Vụ - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/contact-detail.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="contact-detail-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="contact-header">
            <div class="contact-name">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($booking['customer_name']); ?>
            </div>
            <div class="contact-meta">
                <div class="contact-meta-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" style="color: white;">
                        <?php echo htmlspecialchars($booking['email']); ?>
                    </a>
                </div>
                <div class="contact-meta-item">
                    <i class="fas fa-phone"></i>
                    <a href="tel:<?php echo htmlspecialchars($booking['phone']); ?>" style="color: white;">
                        <?php echo htmlspecialchars($booking['phone']); ?>
                    </a>
                </div>
                <div class="contact-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div style="margin-bottom: 30px;">
            <a href="bookings.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
            </a>
        </div>

        <!-- Booking Information -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i> Thông Tin Yêu Cầu Đặt Lịch
            </h3>

            <div class="two-column">
                <div class="info-group">
                    <label class="info-label">Họ và Tên</label>
                    <div class="info-value">
                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                    </div>
                </div>

                <div class="info-group">
                    <label class="info-label">Email</label>
                    <div class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>">
                            <?php echo htmlspecialchars($booking['email']); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="two-column">
                <div class="info-group">
                    <label class="info-label">Số Điện Thoại</label>
                    <div class="info-value">
                        <a href="tel:<?php echo htmlspecialchars($booking['phone']); ?>">
                            <?php echo htmlspecialchars($booking['phone']); ?>
                        </a>
                    </div>
                </div>

                <div class="info-group">
                    <label class="info-label">Dịch Vụ Yêu Cầu</label>
                    <div class="info-value">
                        <i class="fas fa-concierge-bell"></i>
                        <?php echo htmlspecialchars($booking['service_name'] ?? 'Không xác định'); ?>
                    </div>
                </div>
            </div>

            <div class="two-column">
                <div class="info-group">
                    <label class="info-label">Giá Dịch Vụ</label>
                    <div class="info-value">
                        <i class="fas fa-money-bill"></i>
                        <?php echo $booking['service_price'] ? number_format($booking['service_price'], 0, ',', '.') . ' VNĐ' : 'Không xác định'; ?>
                    </div>
                </div>

                <div class="info-group">
                    <label class="info-label">Ngày Đặt Lịch</label>
                    <div class="info-value">
                        <i class="fas fa-calendar-check"></i>
                        <?php echo $booking['appointment_date'] && $booking['appointment_date'] !== '0000-00-00' 
                                  ? date('d/m/Y', strtotime($booking['appointment_date'])) 
                                  : 'Chưa xác định'; ?>
                    </div>
                </div>
            </div>

            <div class="info-group">
                <label class="info-label">Ngày Tạo Yêu Cầu</label>
                <div class="info-value">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Service Description -->
        <?php if (!empty($booking['service_description'])): ?>
            <div class="detail-section">
                <h3 class="section-title">
                    <i class="fas fa-align-left"></i> Mô Tả Dịch Vụ
                </h3>

                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($booking['service_description'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Device Information -->
        <?php if (!empty($booking['device_info'])): ?>
            <div class="detail-section">
                <h3 class="section-title">
                    <i class="fas fa-microchip"></i> Thông Tin Thiết Bị
                </h3>

                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($booking['device_info'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if (!empty($booking['notes'])): ?>
            <div class="detail-section">
                <h3 class="section-title">
                    <i class="fas fa-sticky-note"></i> Ghi Chú Từ Khách Hàng
                </h3>

                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                </div>

                <div class="timestamp">
                    <i class="fas fa-clock"></i>
                    Tạo lúc: <strong><?php echo date('d/m/Y H:i:s', strtotime($booking['created_at'])); ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Management -->
        <div class="status-section">
            <h3 class="section-title">
                <i class="fas fa-tasks"></i> Quản Lý Trạng Thái
            </h3>

            <div class="info-group">
                <label class="info-label">Trạng Thái Hiện Tại</label>
                <div style="margin-bottom: 20px;">
                    <span class="status-badge <?php echo htmlspecialchars($booking['status']); ?>">
                        <?php 
                        $status_labels = [
                            'new' => 'Mới',
                            'processing' => 'Đang xử lý',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Hủy'
                        ];
                        echo $status_labels[$booking['status']] ?? $booking['status'];
                        ?>
                    </span>
                </div>
            </div>

            <form method="POST" class="status-form">
                <select name="status" required>
                    <option value="new" <?php echo $booking['status'] === 'new' ? 'selected' : ''; ?>>Mới</option>
                    <option value="processing" <?php echo $booking['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Hủy</option>
                </select>
                <button type="submit" name="action" value="update_status">
                    <i class="fas fa-check"></i> Cập Nhật Trạng Thái
                </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" class="btn-back" style="background-color: #10B981;">
                <i class="fas fa-reply"></i> Gửi Email
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
