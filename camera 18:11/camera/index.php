<?php
require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';

// Initialize Auth
$auth = new Auth();

// Initialize Page class
$page = new Page();
$page->setTitle('XEDIC Camera');
$page->setDescription('Camera Chuyên Nghiệp Cho Creators');

// Get featured products
$productModel = new Product();
$featuredProducts = $productModel->getFeaturedProducts(3);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page->getTitle(); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* ===== GALLERY SECTION STYLES ===== */
        .gallery-section {
            background: #FFFFFF;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        /* Gallery Header */
        .gallery-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .gallery-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1C1C1C;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .gallery-subtitle {
            font-size: 1.1rem;
            color: #8B6F47;
            font-weight: 500;
            margin: 0;
        }

        /* Gallery Grid - Horizontal Scroll */
        .gallery-grid-scroll-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            margin-bottom: 50px;
            padding: 20px 0;
        }

        .gallery-grid-scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .gallery-grid-scroll-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .gallery-grid-scroll-container::-webkit-scrollbar-thumb {
            background: #8B6F47;
            border-radius: 10px;
        }

        .gallery-grid-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #6A5735;
        }

        .gallery-grid-scroll {
            display: flex;
            gap: 20px;
            padding: 0 20px;
            width: max-content;
        }

        .gallery-grid-item {
            flex: 0 0 calc(100% / 6 - 16.67px);
            min-width: 280px;
        }

        /* Gallery Grid Item */
        .gallery-grid-item {
            width: 100%;
        }

        .gallery-grid-image-wrapper {
            position: relative;
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .gallery-grid-image-wrapper:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .gallery-grid-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .gallery-grid-image-wrapper:hover .gallery-grid-image {
            transform: scale(1.08);
        }

        /* Gallery Grid Overlay */
        .gallery-grid-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, 
                rgba(0, 0, 0, 0) 0%, 
                rgba(0, 0, 0, 0.4) 60%, 
                rgba(0, 0, 0, 0.8) 100%);
            display: flex;
            align-items: flex-end;
            padding: 30px 20px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .gallery-grid-image-wrapper:hover .gallery-grid-overlay {
            opacity: 1;
        }

        .gallery-grid-overlay h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* Gallery Footer */
        .gallery-footer {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .gallery-view-more {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #1C1C1C 0%, #8B6F47 100%);
            color: white;
            padding: 14px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .gallery-view-more:hover {
            background: linear-gradient(135deg, #8B6F47 0%, #FF5733 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 87, 51, 0.25);
            color: white;
            text-decoration: none;
        }

        .gallery-view-more i {
            transition: transform 0.4s ease;
        }

        .gallery-view-more:hover i {
            transform: translateX(5px);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .gallery-grid-item {
                flex: 0 0 calc(100% / 5 - 16px);
                min-width: 240px;
            }

            .gallery-grid-image-wrapper {
                height: 300px;
            }

            .gallery-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 992px) {
            .gallery-section {
                padding: 60px 0;
            }

            .gallery-header {
                margin-bottom: 50px;
            }

            .gallery-grid-item {
                flex: 0 0 calc(100% / 4 - 15px);
                min-width: 200px;
            }

            .gallery-grid-image-wrapper {
                height: 250px;
            }

            .gallery-title {
                font-size: 2rem;
                margin-bottom: 12px;
            }

            .gallery-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 768px) {
            .gallery-section {
                padding: 50px 0;
            }

            .gallery-header {
                margin-bottom: 40px;
            }

            .gallery-grid-item {
                flex: 0 0 calc(100% / 3 - 13.33px);
                min-width: 180px;
            }

            .gallery-grid-image-wrapper {
                height: 220px;
            }

            .gallery-title {
                font-size: 1.8rem;
            }

            .gallery-subtitle {
                font-size: 0.95rem;
            }

            .gallery-grid-overlay h3 {
                font-size: 1rem;
            }

            .gallery-view-more {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .gallery-section {
                padding: 40px 0;
            }

            .gallery-header {
                margin-bottom: 30px;
            }

            .gallery-grid-item {
                flex: 0 0 calc(100vw - 50px);
                min-width: calc(100vw - 50px);
            }

            .gallery-grid-image-wrapper {
                height: 200px;
            }

            .gallery-title {
                font-size: 1.5rem;
                margin-bottom: 10px;
            }

            .gallery-subtitle {
                font-size: 0.9rem;
            }

            .gallery-grid-overlay h3 {
                font-size: 0.9rem;
                padding: 0;
            }

            .gallery-footer {
                margin-top: 25px;
            }

            .gallery-view-more {
                padding: 10px 25px;
                font-size: 0.95rem;
            }
        }
    </style>
   
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="hero-badge">Mới Ra Mắt</span>
                    <h1 class="hero-title">Camera Chuyên Nghiệp Cho Creators</h1>
                    <p class="hero-subtitle">
                        Camera Chuyên Nghiệp Cho Creators
                    </p>
                    <a href="products.php" class="cta-button">Khám Phá Ngay</a>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image">
                        <img src="https://nofilmschool.com/media-library/why-is-the-fujifilm-x100vi-struggling-to-stay-in-stock.jpg?id=51951232&width=1245&height=700&quality=90&coordinates=40%2C0%2C40%2C0" 
                             alt="Camera chuyên nghiệp" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Sản Phẩm Nổi Bật</h2>
            <div class="row g-4">
                <?php
                if (!empty($featuredProducts)) {
                    $delay = 100;
                    foreach ($featuredProducts as $product):
                        $price = number_format($product['price'], 0, '.', ',') . ' VNĐ';
                        $description = substr($product['description'], 0, 100) . '...';
                ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="product-card" onclick="viewProduct(<?php echo $product['id']; ?>)">
                        <div class="product-image">
                            <?php if (!empty($product['badge'])): ?>
                                <span class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars($description); ?>
                            </p>
                            <div class="product-price"><?php echo $price; ?></div>
                            <?php if ($product['stock'] > 0): ?>
                                <small style="color: #27ae60; font-weight: 500;">✓ Còn hàng</small>
                            <?php else: ?>
                                <small style="color: #e74c3c; font-weight: 500;">✗ Hết hàng</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
                        $delay += 100;
                    endforeach;
                } else {
                    echo '<div class="col-12"><p class="text-center">Hiện chưa có sản phẩm nổi bật</p></div>';
                }
                ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="products.php" class="view-more-btn">Xem Thêm Sản Phẩm</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="services" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Cam Kết Của Chúng Tôi</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3 class="feature-title">Tư Vấn Chuyên Nghiệp</h3>
                        <p class="feature-description">
                            Đội ngũ chuyên gia với hơn 10 năm kinh nghiệm sẽ tư vấn giúp bạn chọn lựa thiết bị phù hợp nhất.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3 class="feature-title">Bảo Hành & Sửa Chữa</h3>
                        <p class="feature-description">
                            Dịch vụ bảo hành chính hãng và sửa chữa chuyên nghiệp với linh kiện chính hãng 100%.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3 class="feature-title">Giao Hàng Nhanh</h3>
                        <p class="feature-description">
                            Giao hàng miễn phí trong nội thành và giao hàng nhanh toàn quốc với đóng gói cẩn thận.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3 class="feature-title">Chất Lượng Đảm Bảo</h3>
                        <p class="feature-description">
                            Tất cả sản phẩm đều là hàng chính hãng với chứng nhận chất lượng và nguồn gốc rõ ràng.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Về Cửa Hàng XEDIC</h2>
            <div class="about-content">
                <p data-aos="fade-up" data-aos-delay="100">
                    XEDIC được thành lập với sứ mệnh mang đến những thiết bị photography và videography chất lượng cao nhất 
                    cho cộng đồng creator Việt Nam. Chúng tôi tin rằng mỗi người đều có một câu chuyện đáng kể, 
                    và công nghệ phù hợp sẽ giúp bạn kể câu chuyện đó một cách hoàn hảo nhất.
                </p>
                <p data-aos="fade-up" data-aos-delay="200">
                    Với hơn 10 năm kinh nghiệm trong lĩnh vực này, XEDIC đã trở thành địa chỉ tin cậy của hàng nghìn 
                    photographer, videographer và content creator trên khắp Việt Nam.
                </p>
                <p data-aos="fade-up" data-aos-delay="300">
                    Chúng tôi không chỉ bán sản phẩm, mà còn xây dựng một cộng đồng sáng tạo, 
                    nơi mọi người có thể học hỏi, chia sẻ và cùng nhau phát triển.
                </p>
            </div>
        </div>
    </section>

    <!-- Carousel Section -->
    <section class="gallery-section">
        <div class="container">
            <div class="gallery-header" data-aos="fade-up">
                <h2 class="gallery-title">Bộ Sưu Tập Của Chúng Tôi</h2>
                <p class="gallery-subtitle">Khám phá thế giới photography cùng XEDIC</p>
            </div>

            <!-- Gallery Grid - Horizontal Scroll -->
            <div class="gallery-grid-scroll-container" data-aos="fade-up" data-aos-delay="100">
                <div class="gallery-grid-scroll">
                    <!-- Image 1 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F2bd4faf351f5cf98d75edfb7be3091e9584d6232-1678x2098.jpg%3Fw%3D1440%26h%3D1800%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Professional Camera" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>

                    <!-- Image 2 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2Fb370ff9aaf2ce6a1f7c8b34a258a26bfa47a739c-1678x2098.jpg%3Fw%3D1440%26h%3D1800%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Photography Workshop" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>

                    <!-- Image 3 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2Fd5c3462c0508de4fa6ba24dce0360de53da00205-1678x2098.jpg%3Fw%3D1440%26h%3D1800%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Video Equipment" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>

                    <!-- Image 4 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2Fa4051576868747b3423ff0912b79d4f723317b10-1679x2098.jpg%3Fw%3D1440%26h%3D1799%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Lens Collection" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>

                    <!-- Image 5 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F809f760f5b4abcb4156ba7ce0d89c792c26ec035-1678x2098.jpg%3Fw%3D1440%26h%3D1800%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Studio Equipment" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>

                    <!-- Image 6 -->
                    <div class="gallery-grid-item">
                        <div class="gallery-grid-image-wrapper">
                            <img src="https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2Faaa38c2fdeeff055cfeb1c9fd6121c2b4eafd0a2-1678x2098.jpg%3Fw%3D1440%26h%3D1800%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=1080&q=75" 
                                 alt="Photography Gear" class="gallery-grid-image">
                            <div class="gallery-grid-overlay">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <!-- Gallery Footer -->
        </div>
    </section>

    <!-- Footer -->
<?php include 'includes/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Product navigation function
        function viewProduct(productId) {
            window.location.href = `products.php?id=${productId}`;
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
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

        // Active navigation link on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const scrollY = window.pageYOffset;

            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop - 100;
                const sectionId = section.getAttribute('id');
                const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);

                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    if (navLink) {
                        navLink.classList.add('active');
                    }
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
            `;
            notification.innerHTML = `
                <strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
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
    </script>
</body>
</html>