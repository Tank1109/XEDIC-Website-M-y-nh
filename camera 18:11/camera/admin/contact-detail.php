<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/contacts.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get contact ID from URL
$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($contact_id === 0) {
    header('Location: contacts.php');
    exit;
}

// Get contact details
$stmt = $db->prepare("SELECT * FROM contacts WHERE id = :id");
$stmt->bindParam(':id', $contact_id, PDO::PARAM_INT);
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    header('Location: contacts.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
        $status = $_POST['status'];
        $stmt = $db->prepare("UPDATE contacts SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $contact_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $contact['status'] = $status;
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
    <title>Chi Tiết Liên Hệ - XEDIC Admin</title>
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
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($contact['name']); ?>
            </div>
            <div class="contact-meta">
                <div class="contact-meta-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color: white;">
                        <?php echo htmlspecialchars($contact['email']); ?>
                    </a>
                </div>
                <div class="contact-meta-item">
                    <i class="fas fa-phone"></i>
                    <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" style="color: white;">
                        <?php echo htmlspecialchars($contact['phone']); ?>
                    </a>
                </div>
                <div class="contact-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div style="margin-bottom: 30px;">
            <a href="contacts.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
            </a>
        </div>

        <!-- Contact Information -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i> Thông Tin Liên Hệ
            </h3>

            <div class="two-column">
                <div class="info-group">
                    <label class="info-label">Họ và Tên</label>
                    <div class="info-value">
                        <?php echo htmlspecialchars($contact['name']); ?>
                    </div>
                </div>

                <div class="info-group">
                    <label class="info-label">Email</label>
                    <div class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                            <?php echo htmlspecialchars($contact['email']); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="two-column">
                <div class="info-group">
                    <label class="info-label">Số Điện Thoại</label>
                    <div class="info-value">
                        <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>">
                            <?php echo htmlspecialchars($contact['phone']); ?>
                        </a>
                    </div>
                </div>

                <div class="info-group">
                    <label class="info-label">Dịch Vụ Quan Tâm</label>
                    <div class="info-value">
                        <?php 
                        $service = $contact['service'] ?? 'Không xác định';
                        $service_labels = [
                            'camera' => 'Mua Camera',
                            'lens' => 'Mua Ống Kính',
                            'accessories' => 'Phụ Kiện',
                            'repair' => 'Sửa Chữa',
                            'consultation' => 'Tư Vấn',
                            'education' => 'Đào tạo chuyên sâu'
                        ];
                        echo htmlspecialchars($service_labels[$service] ?? $service);
                        ?>
                    </div>
                </div>
            </div>

            <div class="info-group">
                <label class="info-label">Ngày Gửi</label>
                <div class="info-value">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Message Content -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-comment-dots"></i> Nội Dung Tin Nhắn
            </h3>

            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
            </div>

            <div class="timestamp">
                <i class="fas fa-clock"></i>
                Gửi lúc: <strong><?php echo date('d/m/Y H:i:s', strtotime($contact['created_at'])); ?></strong>
            </div>
        </div>

        <!-- Status Management -->
        <div class="status-section">
            <h3 class="section-title">
                <i class="fas fa-tasks"></i> Quản Lý Trạng Thái
            </h3>

            <div class="info-group">
                <label class="info-label">Trạng Thái Hiện Tại</label>
                <div style="margin-bottom: 20px;">
                    <span class="status-badge <?php echo htmlspecialchars($contact['status']); ?>">
                        <?php 
                        $status_labels = [
                            'new' => 'Mới',
                            'processing' => 'Đang xử lý',
                            'completed' => 'Hoàn thành'
                        ];
                        echo $status_labels[$contact['status']] ?? $contact['status'];
                        ?>
                    </span>
                </div>
            </div>

            <form method="POST" class="status-form">
                <select name="status" required>
                    <option value="new" <?php echo $contact['status'] === 'new' ? 'selected' : ''; ?>>Mới</option>
                    <option value="processing" <?php echo $contact['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="completed" <?php echo $contact['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                </select>
                <button type="submit" name="action" value="update_status">
                    <i class="fas fa-check"></i> Cập Nhật Trạng Thái
                </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="btn-back" style="background-color: #10B981;">
                <i class="fas fa-reply"></i> Trả Lời Email
            </a>
            <button class="btn-delete" onclick="deleteContact(<?php echo $contact['id']; ?>)">
                <i class="fas fa-trash-alt"></i> Xóa Liên Hệ
            </button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteContact(id) {
            if (confirm('Bạn có chắc chắn muốn xóa liên hệ này? Hành động này không thể hoàn tác.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
