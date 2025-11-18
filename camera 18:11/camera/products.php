<?php
header('Content-Type: text/html; charset=utf-8');

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'classes/Product.php';
require_once 'classes/Page.php';
require_once 'classes/Request.php';
require_once 'classes/ProductFilter.php';
require_once 'controllers/ProductController.php';

// Initialize controller
$controller = new ProductController();
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
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <!-- Products Page Specific Styles -->
    <link href="css/products.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="products-page">
        <div class="container-fluid">
            <div class="row g-0">
                <!-- Sidebar Filter -->
                <div class="col-lg-3">
                    <div class="filter-sidebar">
                        <!-- Search Filter -->
                        <div class="filter-section filter-search">
                            <h5 class="filter-title">
                                <i class="fas fa-search me-2"></i>Tìm Kiếm
                            </h5>
                            <form method="GET" class="search-form">
                                <div class="search-box">
                                    <input type="text" name="search" class="search-input" 
                                           placeholder="Nhập tên sản phẩm..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn-search">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Category Filter -->
                        <div class="filter-section">
                            <h5 class="filter-title">
                                <i class="fas fa-folder me-2"></i>Danh Mục Sản Phẩm
                            </h5>
                            <form method="GET">
                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                
                                <div class="filter-category-all">
                                    <input type="radio" id="cat_all" name="category" value="" 
                                           <?php echo empty($category) ? 'checked' : ''; ?> 
                                           onchange="this.form.submit()">
                                    <label for="cat_all">
                                        <span class="category-badge">Tất Cả Sản Phẩm</span>
                                    </label>
                                </div>

                                <div class="filter-group">
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="filter-option">
                                            <input type="radio" id="cat_<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $cat))); ?>" 
                                                   name="category" value="<?php echo htmlspecialchars($cat); ?>" 
                                                   <?php echo $category === $cat ? 'checked' : ''; ?> 
                                                   onchange="this.form.submit()">
                                            <label for="cat_<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $cat))); ?>">
                                                <?php echo htmlspecialchars($cat); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Brand/Manufacturer Filter -->
                        <div class="filter-section">
                            <h5 class="filter-title">
                                <i class="fas fa-tag me-2"></i>Thương Hiệu
                            </h5>
                            <form method="GET" id="brand-filter-form">
                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <?php if (!empty($category)): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                                <?php endif; ?>
                                <?php if (!empty($price_min) && $price_min > 0): ?>
                                    <input type="hidden" name="price_min" value="<?php echo htmlspecialchars($price_min); ?>">
                                <?php endif; ?>
                                <?php if (!empty($price_max) && $price_max < 100000000): ?>
                                    <input type="hidden" name="price_max" value="<?php echo htmlspecialchars($price_max); ?>">
                                <?php endif; ?>
                                
                                <div class="filter-group">
                                    <?php foreach ($brands as $brandItem): ?>
                                        <div class="filter-option">
                                            <input type="checkbox" 
                                                   id="brand_<?php echo htmlspecialchars($brandItem['slug']); ?>" 
                                                   class="brand-checkbox"
                                                   name="brands[]" 
                                                   value="<?php echo htmlspecialchars($brandItem['name']); ?>" 
                                                   <?php 
                                                       $brandsArray = isset($_GET['brands']) ? (is_array($_GET['brands']) ? $_GET['brands'] : explode(',', $_GET['brands'])) : [];
                                                       echo in_array($brandItem['name'], $brandsArray) ? 'checked' : ''; 
                                                   ?> 
                                                   onchange="submitBrandFilter();">
                                            <label for="brand_<?php echo htmlspecialchars($brandItem['slug']); ?>">
                                                <?php echo htmlspecialchars($brandItem['name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="filter-section">
                            <h5 class="filter-title">
                                <i class="fas fa-dollar-sign me-2"></i>Khoảng Giá
                            </h5>
                            <div class="price-range-container">
                                <form method="GET" id="price-filter-form">
                                    <?php if (!empty($search)): ?>
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <?php endif; ?>
                                    <?php if (!empty($category)): ?>
                                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                                    <?php endif; ?>
                                    <?php 
                                        // Preserve selected brands
                                        $brandsArray = isset($_GET['brands']) ? (is_array($_GET['brands']) ? $_GET['brands'] : explode(',', $_GET['brands'])) : [];
                                        foreach ($brandsArray as $selectedBrand): 
                                    ?>
                                        <input type="hidden" name="brands[]" value="<?php echo htmlspecialchars($selectedBrand); ?>">
                                    <?php endforeach; ?>
                                    
                                    <div class="price-inputs">
                                        <div class="price-input-group">
                                            <label for="price-min">Từ:</label>
                                            <input type="number" id="price-min" name="price_min" class="form-control price-input" 
                                                   placeholder="0" min="0" value="<?php echo htmlspecialchars($price_min); ?>">
                                        </div>
                                        <div class="price-input-group">
                                            <label for="price-max">Đến:</label>
                                            <input type="number" id="price-max" name="price_max" class="form-control price-input" 
                                                   placeholder="100,000,000" min="0" value="<?php echo htmlspecialchars($price_max); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="price-slider-container">
                                        <input type="range" id="price-slider" class="price-slider" 
                                               min="0" max="100000000" value="<?php echo htmlspecialchars($price_max); ?>">
                                    </div>

                                    <div class="price-display">
                                        Giá: <span id="price-value"><?php echo htmlspecialchars($price_min); ?> - <?php echo htmlspecialchars($price_max); ?> ₫</span>
                                    </div>

                                    <button type="submit" class="btn-filter-price">
                                        <i class="fas fa-check me-1"></i>Áp Dụng
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Sort Filter -->
                        <div class="filter-section">
                            <h5 class="filter-title">
                                <i class="fas fa-sort me-2"></i>Sắp Xếp
                            </h5>
                            <form method="GET">
                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <?php if (!empty($category)): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                                <?php endif; ?>
                                
                                <select name="sort" class="sort-select" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Giá: thấp đến cao</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Giá: cao đến thấp</option>
                                </select>
                            </form>
                        </div>

                        <!-- Clear Filters -->
                        <div class="filter-actions">
                            <a href="products.php" class="clear-filters-btn">
                                <i class="fas fa-redo me-2"></i>Xóa Bộ Lọc
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="col-lg-9">
                    <!-- Products Header Section -->
                    <div class="products-main">
                        <div class="breadcrumb-section-inline">
                            <h1><?php echo htmlspecialchars($heading); ?></h1>
                            <p class="products-subtitle">
                                Hiển thị <strong><?php echo $totalProducts; ?></strong> sản phẩm
                            </p>
                        </div>

                        <!-- Products Grid -->
                        <?php if (!empty($products)): ?>
                            <div class="row g-4" style="padding: 0 40px;">
                                <?php foreach ($products as $index => $product): ?>
                                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                                        <div class="product-card">
                                            <div class="product-image-wrapper">
                                                <?php if (!empty($product['badge'])): ?>
                                                    <span class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if ($product['stock'] > 0): ?>
                                                    <span class="product-badge stock">Còn hàng</span>
                                                <?php else: ?>
                                                    <span class="product-badge out-of-stock">Hết hàng</span>
                                                <?php endif; ?>
                                                
                                                <img src="<?php echo htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/400x300?text=No+Image'); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="product-image">
                                            </div>

                                            <div class="product-info">
                                                <div class="product-category"><?php echo htmlspecialchars($product['category'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></div>
                                                <h3 class="product-title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                                <p class="product-description">
                                                    <?php 
                                                    $desc = $product['description'] ?? '';
                                                    $desc_processed = mb_substr($desc, 0, 100, 'UTF-8');
                                                    echo htmlspecialchars($desc_processed, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </p>

                                                <div class="product-footer">
                                                    <div class="product-price"><?php echo Product::formatPrice($product['price']); ?></div>
                                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)" 
                                                        <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-shopping-cart me-1"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state" style="padding: 60px 40px;">
                                <i class="fas fa-inbox"></i>
                                <h3>Không tìm thấy sản phẩm</h3>
                                <p>Chúng tôi không tìm thấy sản phẩm phù hợp với tiêu chí tìm kiếm của bạn.</p>
                                <a href="products.php" class="back-btn">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Error Handler to suppress external scripts -->
    <script>
        // Suppress errors from external/browser extension scripts
        window.addEventListener('error', function(event) {
            if (event.message && (event.message.includes('share-modal') || event.message.includes('addEventListener'))) {
                event.preventDefault();
                return true;
            }
        }, true);
    </script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Submit brand filter form and preserve price range
        function submitBrandFilter() {
            const priceMin = document.getElementById('price-min');
            const priceMax = document.getElementById('price-max');
            const brandForm = document.getElementById('brand-filter-form');
            
            if (priceMin && priceMin.value && parseInt(priceMin.value) > 0) {
                const hiddenMin = document.createElement('input');
                hiddenMin.type = 'hidden';
                hiddenMin.name = 'price_min';
                hiddenMin.value = priceMin.value;
                brandForm.appendChild(hiddenMin);
            }
            
            if (priceMax && priceMax.value) {
                const hiddenMax = document.createElement('input');
                hiddenMax.type = 'hidden';
                hiddenMax.name = 'price_max';
                hiddenMax.value = priceMax.value;
                brandForm.appendChild(hiddenMax);
            }
            
            brandForm.submit();
        }

        // Price range filter
        const priceMin = document.getElementById('price-min');
        const priceMax = document.getElementById('price-max');
        const priceSlider = document.getElementById('price-slider');
        const priceValue = document.getElementById('price-value');

        function updatePriceDisplay() {
            const minVal = parseInt(priceMin.value) || 0;
            const maxVal = parseInt(priceMax.value) || 100000000;
            const sliderVal = parseInt(priceSlider.value) || 100000000;
            
            // Sync slider with max price input
            priceSlider.value = maxVal;
            
            // Update display text
            priceValue.textContent = 
                formatCurrency(minVal) + ' - ' + formatCurrency(maxVal) + ' ₫';
        }

        function formatCurrency(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }

        priceMin.addEventListener('change', updatePriceDisplay);
        priceMax.addEventListener('change', updatePriceDisplay);
        priceSlider.addEventListener('input', function() {
            priceMax.value = this.value;
            updatePriceDisplay();
        });

        // Initialize price display on page load
        updatePriceDisplay();

        // Add to cart function
        function addToCart(productId) {
            fetch('api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✓ Đã thêm vào giỏ hàng', 'success');
                } else {
                    if (data.message === 'not_logged_in') {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    } else {
                        showNotification('✗ ' + (data.message || 'Có lỗi xảy ra!'), 'error');
                    }
                }
            })
            .catch(error => {
                showNotification('Có lỗi xảy ra!', 'error');
            });
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