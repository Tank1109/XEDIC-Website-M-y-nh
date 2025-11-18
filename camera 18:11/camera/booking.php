<?php
session_start();
require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Booking.php';
require_once 'classes/Service.php';

// Initialize
$auth = new Auth();
$bookingModel = new Booking();
$serviceModel = new Service();

// Get service name from URL or session
$serviceName = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : '';
$successMessage = '';
$errorMessages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingModel->setServiceName($serviceName);
    $bookingModel->setCustomerName($_POST['customer_name'] ?? '');
    $bookingModel->setCustomerEmail($_POST['customer_email'] ?? '');
    $bookingModel->setCustomerPhone($_POST['customer_phone'] ?? '');
    $bookingModel->setBookingDate($_POST['booking_date'] ?? '');
    $bookingModel->setBookingTime($_POST['booking_time'] ?? '');
    $bookingModel->setDeviceType($_POST['device_type'] ?? '');
    $bookingModel->setDeviceBrand($_POST['device_brand'] ?? '');
    $bookingModel->setDeviceModel($_POST['device_model'] ?? '');
    $bookingModel->setDeviceCondition($_POST['device_condition'] ?? '');
    $bookingModel->setAdditionalNotes($_POST['additional_notes'] ?? '');
    $bookingModel->setStatus('pending');
    
    // Validate
    $errorMessages = $bookingModel->validate();
    
    if (empty($errorMessages)) {
        if ($bookingModel->create()) {
            $successMessage = "✓ Đặt lịch thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.";
        } else {
            $errorMessages[] = "Có lỗi xảy ra khi lưu đặt lịch. Vui lòng thử lại.";
        }
    }
}

$deviceTypes = Booking::getDeviceTypes();
$deviceConditions = Booking::getDeviceConditions();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Dịch Vụ - XEDIC Camera</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Booking Hero Section -->
    <section class="booking-hero">
        <div class="container">
            <div class="row" data-aos="fade-up">
                <div class="col-lg-8">
                    <span class="booking-badge">Đặt Lịch Dịch Vụ</span>
                    <h1>Đặt Lịch Dịch Vụ Của Bạn</h1>
                    <p>Điền thông tin chi tiết để đặt lịch dịch vụ và chúng tôi sẽ xác nhận ngay</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Form -->
    <main class="booking-container py-5">
        <div class="booking-form-card" data-aos="fade-up">
            <!-- Success Message -->
            <?php if ($successMessage): ?>
                <div class="alert alert-custom alert-success-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errorMessages)): ?>
                <div class="alert alert-custom alert-danger-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Vui lòng sửa lỗi sau:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errorMessages as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Service Information -->
            <?php if ($serviceName): ?>
                <div class="service-info">
                    <div class="service-info-title">
                        <i class="fas fa-check me-2"></i>Dịch Vụ Được Chọn
                    </div>
                    <div class="service-info-value"><?php echo htmlspecialchars($serviceName); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Customer Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-user"></i>
                        Thông Tin Khách Hàng
                    </h3>

                    <div class="form-group">
                        <label class="form-label required-field">Họ và Tên</label>
                        <input type="text" class="form-control" name="customer_name" 
                               placeholder="Nhập họ và tên của bạn" 
                               value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required-field">Email</label>
                            <input type="email" class="form-control" name="customer_email" 
                                   placeholder="example@email.com"
                                   value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required-field">Số Điện Thoại</label>
                            <input type="tel" class="form-control" name="customer_phone" 
                                   placeholder="0123 456 789"
                                   value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Booking Schedule Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Lịch Đặt Dịch Vụ
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required-field">Ngày Đặt Lịch</label>
                            <input type="date" class="form-control" name="booking_date" 
                                   value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <small class="text-muted d-block mt-2">Chọn ngày từ hôm nay trở đi</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label required-field">Giờ Đặt Lịch</label>
                            <select class="form-select" name="booking_time" required>
                                <option value="">Chọn giờ dịch vụ</option>
                                <option value="09:00" <?php echo ($_POST['booking_time'] ?? '') === '09:00' ? 'selected' : ''; ?>>09:00 - 11:00</option>
                                <option value="13:00" <?php echo ($_POST['booking_time'] ?? '') === '13:00' ? 'selected' : ''; ?>>13:00 - 15:00</option>
                                <option value="15:00" <?php echo ($_POST['booking_time'] ?? '') === '15:00' ? 'selected' : ''; ?>>15:00 - 17:00</option>
                                <option value="17:00" <?php echo ($_POST['booking_time'] ?? '') === '17:00' ? 'selected' : ''; ?>>17:00 - 19:00</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Device Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-camera"></i>
                        Thông Tin Thiết Bị
                    </h3>

                    <div class="form-group">
                        <label class="form-label required-field">Loại Thiết Bị</label>
                        <select class="form-select" name="device_type" required>
                            <option value="">Chọn loại thiết bị</option>
                            <?php foreach ($deviceTypes as $key => $type): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo ($_POST['device_type'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required-field">Hãng Sản Xuất</label>
                            <input type="text" class="form-control" name="device_brand" 
                                   placeholder="Ví dụ: Canon, Sony, Nikon..."
                                   value="<?php echo htmlspecialchars($_POST['device_brand'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required-field">Model/Số Hiệu</label>
                            <input type="text" class="form-control" name="device_model" 
                                   placeholder="Ví dụ: EOS R5, A7IV..."
                                   value="<?php echo htmlspecialchars($_POST['device_model'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required-field">Tình Trạng Thiết Bị</label>
                        <select class="form-select" name="device_condition" required>
                            <option value="">Chọn tình trạng thiết bị</option>
                            <?php foreach ($deviceConditions as $key => $condition): ?>
                                <option value="<?php echo $key; ?>"
                                        <?php echo ($_POST['device_condition'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($condition); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-pen-fancy"></i>
                        Thông Tin Bổ Sung
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Ghi Chú Thêm (tuỳ chọn)</label>
                        <textarea class="form-control" name="additional_notes" rows="4"
                                  placeholder="Mô tả chi tiết các vấn đề hay yêu cầu khác..."><?php echo htmlspecialchars($_POST['additional_notes'] ?? ''); ?></textarea>
                        <small class="text-muted d-block mt-2">Ví dụ: 'Máy bị hỏng ống kính, nút bấm bị cứng'</small>
                    </div>

                <!-- Form Actions -->
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-check me-2"></i>Xác Nhận Đặt Lịch
                    </button>
                    <a href="service.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Quay Lại</span>
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Add scroll-triggered navbar background
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(245, 241, 232, 0.98)';
                header.style.boxShadow = '0 2px 30px rgba(139, 115, 85, 0.15)';
            } else {
                header.style.background = 'rgba(245, 241, 232, 0.95)';
                header.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.1)';
            }
        });

        // Minimum date validation (optional enhancement)
        document.querySelector('input[name="booking_date"]').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date(this.getAttribute('min'));
            
            if (selectedDate < today) {
                alert('Vui lòng chọn ngày từ hôm nay trở đi');
                this.value = '';
            }
        });
    </script>
</body>
</html>
