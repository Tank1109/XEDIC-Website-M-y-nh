<?php
// BẮT BUỘC: SESSION + KIỂM TRA ĐĂNG NHẬP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
$userName = isLoggedIn() ? ($_SESSION['user_name'] ?? 'Người dùng') : null;

// XỬ LÝ FORM
require_once __DIR__ . '/config/database.php';
$database = new Database();
$pdo = $database->getConnection();

$page = new stdClass();
$page->title = 'Liên Hệ - XEDIC Camera';
$page->description = 'Liên hệ với XEDIC để được hỗ trợ nhanh chóng';

$message = '';
$name = $email = $phone = $service = $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $msg     = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$phone || !$service || !$msg) {
        $message = '<div class="alert alert-danger">Vui lòng điền đầy đủ các trường bắt buộc (*).</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Email không hợp lệ.</div>';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $message = '<div class="alert alert-danger">Số điện thoại phải có 10-11 chữ số.</div>';
    } else {
        try {
            $sql = "INSERT INTO contacts (name, email, phone, service, message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $service, $msg]);
            $message = '<div class="alert alert-success">Cảm ơn bạn! Tin nhắn đã được gửi thành công.</div>';
            $name = $email = $phone = $service = $msg = '';
        } catch (Exception $e) {
            error_log('Contact error: ' . $e->getMessage());
            $message = '<div class="alert alert-danger">Lỗi lưu dữ liệu. Vui lòng thử lại.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page->title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page->description); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/contact.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>

    <!-- Hero -->
    <section class="py-5 py-md-7">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h1 class="display-4 fw-bold text-dark">Liên Hệ Với Chúng Tôi</h1>
                <p class="lead text-muted">Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
            </div>
        </div>
    </section>

    <!-- Contact Info + Form -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-5 justify-content-center">
                <!-- Thông tin liên hệ -->
                <div class="col-lg-5" data-aos="fade-right">
                    <h3 class="fw-bold mb-4">Thông Tin Liên Hệ</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt text-dark me-3"></i>
                            <strong>Địa chỉ:</strong> 123 Đường Camera, Quận 1, TP.HCM
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone text-dark me-3"></i>
                            <strong>Điện thoại:</strong> 
                            <a href="tel:0123456789" class="text-decoration-none text-dark">0123 456 789</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope text-dark me-3"></i>
                            <strong>Email:</strong> 
                            <a href="mailto:support@xedicshop.com" class="text-decoration-none text-dark">support@xedicshop.com</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-clock text-dark me-3"></i>
                            <strong>Giờ làm việc:</strong><br>
                            <small>Thứ 2 - Thứ 7: 8:00 - 21:00<br>Chủ nhật: 9:00 - 18:00</small>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <h5 class="mb-3">Theo dõi chúng tôi</h5>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-dark fs-4"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-dark fs-4"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-dark fs-4"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="fw-bold mb-4">Gửi Yêu Cầu Hỗ Trợ</h3>
                            <?php echo $message; ?>
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="0123456789" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Dịch vụ <span class="text-danger">*</span></label>
                                        <select class="form-select" name="service" required>
                                            <option value="">-- Chọn --</option>
                                            <option value="Mua hàng" <?php echo $service==='Mua hàng'?'selected':''; ?>>Mua hàng</option>
                                            <option value="Bảo hành" <?php echo $service==='Bảo hành'?'selected':''; ?>>Bảo hành</option>
                                            <option value="Sửa chữa" <?php echo $service==='Sửa chữa'?'selected':''; ?>>Sửa chữa</option>
                                            <option value="Tư vấn" <?php echo $service==='Tư vấn'?'selected':''; ?>>Tư vấn</option>
                                            <option value="Khác" <?php echo $service==='Khác'?'selected':''; ?>>Khác</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="message" rows="5" required><?php echo htmlspecialchars($msg); ?></textarea>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-outline-dark btn-lg px-5">Gửi Yêu Cầu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Maps -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.447310065407!2d106.695566!3d10.776923!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f38d7d0b3e1%3A0x5b6e7b2f7b2f7b2f!2sHo%20Chi%20Minh%20City!5e0!3m2!1sen!2s!4v1698765432100!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({duration: 800, once: true});</script>
</body>
</html>