<?php
// BẮT BUỘC: BẮT ĐẦU SESSION + KIỂM TRA ĐĂNG NHẬP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Lấy tên người dùng
$user_name = isLoggedIn() ? ($_SESSION['user_name'] ?? 'Người dùng') : 'Tài khoản';

// Cấu hình trang
$page_title = 'Giới Thiệu - XEDIC Camera';
$page_description = 'Về XEDIC - Cửa hàng camera chuyên nghiệp cho creators';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <!-- About Page CSS -->
    <link href="css/about.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- HEADER: DÙNG CHUNG VỚI contact.php -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-up">
                    <h1 class="display-4 fw-bold text-dark">Về XEDIC</h1>
                    <p class="lead text-muted">
                        Cửa hàng chuyên cung cấp máy ảnh, ống kính và phụ kiện cao cấp cho <strong>creators, nhiếp ảnh gia</strong> và <strong>người yêu công nghệ</strong>.
                    </p>
                    <p class="mb-4">
                        Cam kết chất lượng sản phẩm chính hãng, dịch vụ tận tâm và giá cả hợp lý.
                    </p>
                    <a href="#philosophy" class="btn btn-outline-dark btn-lg">Tìm hiểu thêm</a>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-up" data-aos-delay="100">
                    <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800" 
                         alt="XEDIC Team" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
    </section>

    <!-- Triết lý hoạt động -->
    <section id="philosophy" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold text-dark">Triết Lý Hoạt Động</h2>
                <p class="text-muted">Nguyên tắc cốt lõi của XEDIC</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-dark mb-3"><i class="fas fa-users fa-3x"></i></div>
                            <h5 class="card-title">Khách hàng là trung tâm</h5>
                            <p class="card-text text-muted small">
                                Mọi quyết định đều dựa trên trải nghiệm và nhu cầu của khách hàng.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-dark mb-3"><i class="fas fa-award fa-3x"></i></div>
                            <h5 class="card-title">Chất lượng hàng đầu</h5>
                            <p class="card-text text-muted small">
                                Chỉ cung cấp sản phẩm chính hãng, đã qua kiểm định nghiêm ngặt.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-dark mb-3"><i class="fas fa-lightbulb fa-3x"></i></div>
                            <h5 class="card-title">Sáng tạo không ngừng</h5>
                            <p class="card-text text-muted small">
                                Luôn đổi mới để mang đến giải pháp tốt nhất cho người dùng.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Văn hóa công ty -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2" data-aos="fade-left">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800" 
                         alt="Văn hóa XEDIC" class="img-fluid rounded shadow-sm">
                </div>
                <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                    <h2 class="display-5 fw-bold text-dark mb-4">Văn Hóa XEDIC</h2>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <div><strong>Đam mê công nghệ</strong> – Luôn học hỏi và chia sẻ kiến thức mới.</div>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <div><strong>Hợp tác & tôn trọng</strong> – Lắng nghe, hỗ trợ và cùng phát triển.</div>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <div><strong>Trách nhiệm xã hội</strong> – Hỗ trợ cộng đồng sáng tạo trẻ.</div>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <div><strong>Minh bạch</strong> – Trung thực trong mọi giao dịch và cam kết.</div>
                        </li>
                    </ul>
                    <p class="mt-4 fst-italic text-muted">
                        "Chúng tôi không chỉ bán sản phẩm – chúng tôi mang đến giá trị thực sự."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sứ mệnh & Tầm nhìn -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-5">
                <div class="col-md-6" data-aos="fade-right">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-5 bg-dark text-white">
                            <i class="fas fa-bullseye fa-3x mb-3"></i>
                            <h3 class="fw-bold">Sứ Mệnh</h3>
                            <p class="fs-5">
                                Trang bị công cụ hình ảnh chuyên nghiệp, giúp mọi creator hiện thực hóa ý tưởng.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-5 bg-secondary text-white">
                            <i class="fas fa-mountain fa-3x mb-3"></i>
                            <h3 class="fw-bold">Tầm Nhìn</h3>
                            <p class="fs-5">
                                Trở thành hệ sinh thái camera lớn nhất Đông Nam Á vào năm 2030.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Đội ngũ (4 người) -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold text-dark">Đội Ngũ Của Chúng Tôi</h2>
                <p class="text-muted">Những con người tận tâm và sáng tạo</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-center">
                        <img src="" class="rounded-circle mb-3 border border-3 border-dark" width="120" height="120">
                        <h5 class="mt-2">Đỗ Tuấn Anh</h5>
                        <p class="text-muted">Trưởng nhóm</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="150">
                    <div class="text-center">
                        <img src="" class="rounded-circle mb-3 border border-3 border-dark" width="120" height="120">
                        <h5 class="mt-2">Nguyễn Tiến Đạt</h5>
                        <p class="text-muted">Thành viên</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-center">
                        <img src="" class="rounded-circle mb-3 border border-3 border-dark" width="120" height="120">
                        <h5 class="mt-2">Cao Minh Tú</h5>
                        <p class="text-muted">Thành viên</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="250">
                    <div class="text-center">
                        <img src="" class="rounded-circle mb-3 border border-3 border-dark" width="120" height="120">
                        <h5 class="mt-2">Đinh Đức Tâm</h5>
                        <p class="text-muted">Thành viên</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-dark text-white">
        <div class="container text-center">
            <h3 class="fw-bold mb-3">Sẵn sàng sáng tạo?</h3>
            <p class="lead mb-4">Khám phá ngay các sản phẩm chất lượng cao!</p>
            <a href="products.php" class="btn btn-outline-light btn-lg">Xem Sản Phẩm</a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>

    <!-- CẬP NHẬT HEADER SAU KHI TẢI TRANG -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userSpan = document.querySelector('.dropdown-toggle .d-none.d-md-inline');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (userSpan && dropdownMenu) {
                const userName = <?php echo json_encode($user_name); ?>;
                const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;

                if (isLoggedIn) {
                    userSpan.textContent = userName;
                    dropdownMenu.innerHTML = `
                        <li class="dropdown-header">Xin chào, ${userName}!</li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i> Đơn hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('Đăng xuất?');">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                        </a></li>
                    `;
                } else {
                    userSpan.textContent = 'Tài khoản';
                    dropdownMenu.innerHTML = `
                        <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-in-alt me-2"></i> Đăng nhập</a></li>
                        <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus me-2"></i> Đăng ký</a></li>
                    `;
                }
            }
        });
    </script>
</body>
</html>