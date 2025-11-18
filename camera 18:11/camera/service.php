<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Service.php';

// Initialize Auth and Service
$auth = new Auth();
$serviceModel = new Service();

// Get all services
$services = $serviceModel->getAllServices();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch Vụ - XEDIC Camera</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/service.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Services Hero Section -->
    <section id="services" class="services-hero">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <span class="services-badge">Dịch Vụ Chuyên Nghiệp</span>
                    <h1>Chăm Sóc Thiết Bị Của Bạn</h1>
                    <p>XEDIC cung cấp các dịch vụ chuyên nghiệp, đáng tin cậy cho máy ảnh và phụ kiện của bạn</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Services Content -->
    <main class="container py-5">
        <?php if (empty($services)): ?>
            <div class="empty-services">
                <i class="fas fa-inbox"></i>
                <h3>Hiện tại chưa có dịch vụ nào</h3>
                <p>Vui lòng quay lại sau</p>
            </div>
        <?php else: ?>
            <!-- All Services -->
            <section class="category-section" data-aos="fade-up">
                <h2 class="category-title">
                    Dịch Vụ
                </h2>
                
                <div class="row g-4">
                    <?php foreach ($services as $service): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="service-card">
                                <div class="service-card-body">
                                    <div class="service-icon">
                                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                    </div>
                                    <h3 class="service-card-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <p class="service-card-text"><?php echo htmlspecialchars(substr($service['description'], 0, 120)); ?>...</p>
                                    
                                    <div class="service-details">
                                        <div class="service-detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo htmlspecialchars($service['duration']); ?></span>
                                        </div>
                                        <div class="service-detail-item">
                                            <i class="fas fa-shield-alt"></i>
                                            <span><?php echo htmlspecialchars($service['warranty']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="service-price">
                                        <?php echo Service::formatPrice($service['price']); ?>
                                    </div>
                                    
                                    <button class="service-btn" data-bs-toggle="modal" data-bs-target="#serviceModal<?php echo $service['id']; ?>">
                                        <i class="fas fa-info-circle me-2"></i>Xem Chi Tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- All Service Detail Modals -->
            <?php foreach ($services as $service): ?>
                <div class="modal fade" id="serviceModal<?php echo $service['id']; ?>" tabindex="-1" aria-labelledby="serviceModalLabel<?php echo $service['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="serviceModalLabel<?php echo $service['id']; ?>">
                                    <i class="<?php echo htmlspecialchars($service['icon']); ?>" style="margin-right: 12px;"></i>
                                    <?php echo htmlspecialchars($service['name']); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-4" style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tag modal-detail-icon"></i>
                                            <div>
                                                <small class="text-muted d-block modal-detail-label">Giá dịch vụ</small>
                                                <strong class="fs-5"><?php echo Service::formatPrice($service['price']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-layer-group modal-detail-icon"></i>
                                            <div>
                                                <small class="text-muted d-block modal-detail-label">Danh mục</small>
                                                <strong class="fs-5"><?php echo htmlspecialchars($service['category']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hourglass-half modal-detail-icon"></i>
                                            <div>
                                                <small class="text-muted d-block modal-detail-label">Thời gian hoàn thành</small>
                                                <strong class="fs-5"><?php echo htmlspecialchars($service['duration']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-certificate modal-detail-icon"></i>
                                            <div>
                                                <small class="text-muted d-block modal-detail-label">Bảo hành</small>
                                                <strong class="fs-5"><?php echo htmlspecialchars($service['warranty']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booking Form -->
                                <hr class="my-4">
                                <h6 class="mb-3">Đặt Dịch Vụ</h6>
                                <form id="bookingForm<?php echo $service['id']; ?>" class="booking-form">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="fullName<?php echo $service['id']; ?>" class="form-label">Họ và Tên *</label>
                                        <input type="text" class="form-control" id="fullName<?php echo $service['id']; ?>" name="full_name" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email<?php echo $service['id']; ?>" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email<?php echo $service['id']; ?>" name="email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone<?php echo $service['id']; ?>" class="form-label">Số Điện Thoại *</label>
                                            <input type="tel" class="form-control" id="phone<?php echo $service['id']; ?>" name="phone" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="appointmentDate<?php echo $service['id']; ?>" class="form-label">Ngày Hẹn (Tùy Chọn)</label>
                                        <input type="date" class="form-control" id="appointmentDate<?php echo $service['id']; ?>" name="appointment_date" min="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes<?php echo $service['id']; ?>" class="form-label">Ghi Chú Thêm</label>
                                        <textarea class="form-control" id="notes<?php echo $service['id']; ?>" name="notes" rows="3" placeholder="Mô tả chi tiết về yêu cầu hoặc tình trạng thiết bị của bạn..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 btn-submit-booking">
                                        <i class="fas fa-paper-plane me-2"></i>Gửi Yêu Cầu
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- CTA Section -->
        <section class="services-cta" data-aos="fade-up">
            <div class="container">
                <h2>Cần Giúp Đỡ?</h2>
                <p>Liên hệ với chúng tôi để được tư vấn miễn phí và chuyên nghiệp về các dịch vụ của mình</p>
                <a href="#contact" class="services-cta-btn">
                    <i class="fas fa-phone me-2"></i>Liên Hệ Ngay
                </a>
            </div>
        </section>
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

        // Smooth scroll to contact
        document.querySelectorAll('a[href="#contact"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const footer = document.querySelector('footer');
                if (footer) {
                    footer.scrollIntoView({ behavior: 'smooth' });
                }
            });
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

        // Handle service booking forms
        document.querySelectorAll('.booking-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('.btn-submit-booking');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch('api/service-booking.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification('✓ Đặt dịch vụ thành công! Chúng tôi sẽ liên hệ với bạn sớm.', 'success');
                        this.reset();
                        
                        // Close modal after 2 seconds
                        setTimeout(() => {
                            const modalElement = this.closest('.modal');
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) modal.hide();
                        }, 2000);
                    } else {
                        showNotification('✗ ' + (data.message || 'Có lỗi xảy ra!'), 'error');
                    }
                } catch (error) {
                    showNotification('✗ Có lỗi xảy ra: ' + error.message, 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = `
                top: 100px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
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
