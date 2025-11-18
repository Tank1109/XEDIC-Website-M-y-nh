<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/products.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: products.php');
    exit;
}

// Get product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $brand_id = isset($_POST['brand_id']) && $_POST['brand_id'] !== '' ? (int)$_POST['brand_id'] : null;
    $image = trim($_POST['image'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $error = 'Tên sản phẩm không được để trống';
    } elseif (empty($slug)) {
        $error = 'Slug không được để trống';
    } elseif ($price <= 0) {
        $error = 'Giá phải lớn hơn 0';
    } elseif (empty($category)) {
        $error = 'Danh mục không được để trống';
    } else {
        // Check if slug is unique (excluding current product)
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE slug = :slug AND id != :id");
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            $error = 'Slug này đã được sử dụng bởi sản phẩm khác';
        } else {
            // Update product
            $stmt = $db->prepare("UPDATE products SET 
                                name = :name,
                                slug = :slug,
                                description = :description,
                                price = :price,
                                stock = :stock,
                                category = :category,
                                brand_id = :brand_id,
                                image = :image,
                                badge = :badge,
                                is_featured = :is_featured,
                                updated_at = NOW()
                                WHERE id = :id");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':brand_id', $brand_id);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':badge', $badge);
            $stmt->bindParam(':is_featured', $is_featured);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $message = 'Cập nhật sản phẩm thành công!';
                // Reload product data
                $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Lỗi khi cập nhật sản phẩm';
            }
        }
    }
}

// Get brands and categories for dropdowns
$stmt = $db->prepare("SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản Phẩm - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .product-form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #E8E8E8;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1C1C1C;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #8B6F47;
            font-size: 1.4rem;
        }

        .form-label {
            font-weight: 600;
            color: #1C1C1C;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1C1C1C;
            box-shadow: 0 0 0 3px rgba(28, 28, 28, 0.1);
            outline: none;
        }

        .form-text {
            font-size: 0.85rem;
            color: #8B6F47;
            margin-top: 5px;
            display: block;
        }

        .preview-panel {
            background: linear-gradient(135deg, #F5F5F5 0%, #FFFFFF 100%);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #E8E8E8;
            position: sticky;
            top: 20px;
        }

        .preview-panel h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1C1C1C;
            margin-bottom: 20px;
        }

        .product-preview {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        .preview-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            background: linear-gradient(135deg, #E8E8E8 0%, #F5F5F5 100%);
            display: block;
        }

        .preview-content {
            padding: 20px;
        }

        .preview-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1C1C1C;
            margin-bottom: 8px;
        }

        .preview-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FF5733;
            margin: 15px 0;
            padding: 15px;
            background: rgba(255, 87, 51, 0.1);
            border-radius: 8px;
        }

        .preview-badge {
            display: inline-block;
            background: #FF5733;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 10px 0;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 3px;
            accent-color: #8B6F47;
            cursor: pointer;
            border: 2px solid #E8E8E8;
        }

        .form-check-input:checked {
            background-color: #8B6F47;
            border-color: #8B6F47;
        }

        .form-check-label {
            cursor: pointer;
            color: #1C1C1C;
            font-weight: 500;
            margin-left: 10px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .button-group .btn-primary {
            background: linear-gradient(135deg, #1C1C1C 0%, #8B6F47 100%);
            color: white;
        }

        .button-group .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 28, 28, 0.25);
        }

        .button-group .btn-secondary {
            background: #E8E8E8;
            color: #1C1C1C;
        }

        .button-group .btn-secondary:hover {
            background: #D0D0D0;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border-left-color: #10B981;
        }

        .alert-danger {
            background: rgba(255, 87, 51, 0.1);
            color: #FF5733;
            border-left-color: #FF5733;
        }

        .page-header {
            background: linear-gradient(135deg, #1C1C1C 0%, #2C2C2C 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .page-header p {
            margin: 8px 0 0 0;
            opacity: 0.85;
            font-size: 0.95rem;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-3px);
        }

        @media (max-width: 768px) {
            .product-form-container {
                padding: 20px;
            }

            .preview-panel {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid p-4">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-edit"></i> Sửa Sản Phẩm</h1>
                <p>Cập nhật thông tin sản phẩm "<?php echo htmlspecialchars($product['name']); ?>"</p>
            </div>
            <a href="products.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="product-form-container">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Thành công!</strong> <?php echo $message; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Lỗi!</strong> <?php echo $error; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="productForm">
                        <!-- Thông Tin Cơ Bản -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i> Thông Tin Cơ Bản
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tên Sản Phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                                <span class="form-text">URL-friendly slug (chỉ dùng chữ thường, số, dấu gạch ngang)</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô Tả</label>
                                <textarea class="form-control" name="description" rows="6"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                <span class="form-text">Mô tả chi tiết giúp khách hàng hiểu rõ hơn</span>
                            </div>
                        </div>

                        <!-- Giá & Kho -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-tag"></i> Giá & Kho
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price" value="<?php echo (int)$product['price']; ?>" step="1000" required>
                                    <span class="form-text">Giá bán cho khách hàng</span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Lượng Kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock" value="<?php echo (int)$product['stock']; ?>" min="0" required>
                                    <span class="form-text">Số lượng hiện có trong kho</span>
                                </div>
                            </div>
                        </div>

                        <!-- Phân Loại -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-list"></i> Phân Loại
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh Mục <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                                    <span class="form-text">Danh mục sản phẩm</span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thương Hiệu</label>
                                    <select class="form-select" name="brand_id">
                                        <option value="">-- Chọn thương hiệu --</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>" <?php echo $product['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="form-text">Nhà sản xuất/thương hiệu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hình Ảnh & Badge -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-image"></i> Hình Ảnh & Nhãn
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">URL Hình Ảnh</label>
                                    <input type="url" class="form-control" name="image" id="imageInput" value="<?php echo htmlspecialchars($product['image']); ?>" placeholder="https://example.com/image.jpg">
                                    <span class="form-text">Link hình ảnh sản phẩm (HTTPS)</span>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Badge/Nhãn</label>
                                    <input type="text" class="form-control" name="badge" id="badgeInput" value="<?php echo htmlspecialchars($product['badge'] ?? ''); ?>" placeholder="Mới / Hot / Giảm 20%">
                                    <span class="form-text">Nhãn hiển thị trên sản phẩm</span>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="isFeatured" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isFeatured">
                                    <i class="fas fa-star"></i> Hiển thị nổi bật trên trang chủ
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="button-group">
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập Nhật Sản Phẩm
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-lg-4">
                <div class="preview-panel">
                    <h5><i class="fas fa-eye"></i> Xem Trước</h5>
                    
                    <div class="product-preview">
                        <div style="position: relative;">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Preview" class="preview-image" id="previewImage" onerror="this.src='https://via.placeholder.com/400x280?text=Lỗi+Ảnh'">
                            <div class="preview-badges-container">
                                <div id="previewBadgeLeft" class="preview-badge-left" style="display: <?php echo $product['badge'] ? 'inline-block' : 'none'; ?>;"><?php echo htmlspecialchars($product['badge'] ?? ''); ?></div>
                                <div id="previewBadgeRight" class="preview-badge-right" style="display: inline-block;">Còn hàng</div>
                            </div>
                        </div>
                        <div class="preview-content">
                            <div class="preview-category" id="previewCategory"><?php echo strtoupper(htmlspecialchars($product['category'])); ?></div>
                            <div class="preview-name" id="previewName"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="preview-description" id="previewDescription">
                                <?php 
                                    $desc = htmlspecialchars($product['description'] ?? '');
                                    echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                ?>
                            </div>
                            
                            <div class="preview-price-box">
                                <div class="preview-price" id="previewPrice"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                                <button class="btn btn-dark" style="padding: 8px 15px; border-radius: 8px;">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                            
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #E8E8E8;">
                                <p style="margin: 8px 0; font-size: 0.9rem; color: #666;">
                                    <strong>Tồn kho:</strong> <span id="previewStock"><?php echo (int)$product['stock']; ?></span>
                                </p>
                                <p style="margin: 8px 0; font-size: 0.9rem; color: #666;">
                                    <strong>Nổi bật:</strong> 
                                    <span id="previewFeatured" class="badge <?php echo $product['is_featured'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $product['is_featured'] ? 'Có' : 'Không'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate slug
        document.querySelector('input[name="name"]').addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.querySelector('input[name="slug"]').value = slug;
        });

        // Update preview when image changes
        document.getElementById('imageInput').addEventListener('input', function() {
            document.getElementById('previewImage').src = this.value || 'https://via.placeholder.com/400x280?text=Ảnh+Sản+Phẩm';
        });

        // Update preview when name changes
        document.querySelector('input[name="name"]').addEventListener('input', function() {
            document.getElementById('previewName').textContent = this.value || 'Tên sản phẩm';
        });

        // Update preview when price changes
        document.querySelector('input[name="price"]').addEventListener('input', function() {
            const price = parseInt(this.value) || 0;
            document.getElementById('previewPrice').textContent = price.toLocaleString('vi-VN') + ' VNĐ';
        });

        // Update preview when badge changes
        document.getElementById('badgeInput').addEventListener('input', function() {
            const container = document.getElementById('previewBadgeContainer');
            container.innerHTML = '';
            if (this.value) {
                const badge = document.createElement('div');
                badge.className = 'preview-badge';
                badge.textContent = this.value;
                container.appendChild(badge);
            }
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const slug = document.querySelector('input[name="slug"]').value.trim();
            const price = parseFloat(document.querySelector('input[name="price"]').value);
            const category = document.querySelector('input[name="category"]').value.trim();

            if (!name) {
                e.preventDefault();
                alert('Vui lòng nhập tên sản phẩm');
                return false;
            }

            if (!slug) {
                e.preventDefault();
                alert('Vui lòng nhập slug');
                return false;
            }

            if (price <= 0) {
                e.preventDefault();
                alert('Giá phải lớn hơn 0');
                return false;
            }

            if (!category) {
                e.preventDefault();
                alert('Vui lòng chọn danh mục');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>
