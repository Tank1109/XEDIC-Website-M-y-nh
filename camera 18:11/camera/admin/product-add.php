<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/product-add.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get all categories and brands for dropdown
$stmt = $db->prepare("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT id, name FROM brands ORDER BY name");
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $stock = (int)($_POST['stock'] ?? 0);

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
        try {
            // Check if slug already exists
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $error = 'Slug này đã tồn tại';
            } else {
                // Insert product
                $stmt = $db->prepare("INSERT INTO products (name, slug, description, price, category, brand_id, image, badge, is_featured, stock) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([
                    $name,
                    $slug,
                    $description,
                    $price,
                    $category,
                    $brand_id > 0 ? $brand_id : NULL,
                    $image,
                    $badge,
                    $is_featured,
                    $stock
                ])) {
                    $success = 'Thêm sản phẩm thành công!';
                    // Clear form
                    $_POST = [];
                } else {
                    $error = 'Lỗi khi thêm sản phẩm. Vui lòng thử lại.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Generate slug from name
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm - XEDIC Admin</title>
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

        .input-group .btn {
            border: 2px solid #E8E8E8;
            color: #1C1C1C;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .input-group .btn:hover {
            background: #1C1C1C;
            color: white;
            border-color: #1C1C1C;
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
            min-height: 25px;
        }

        .preview-category {
            font-size: 0.85rem;
            color: #8B6F47;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .preview-brand {
            font-size: 0.85rem;
            color: #4A4A4A;
            margin-bottom: 15px;
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

        .preview-badges-container {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
        }

        .preview-badge-left {
            background: #FF5733;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .preview-badge-right {
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .preview-price-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Badge styles */
        .badge {
            color: white !important;
            font-weight: 600;
        }

        .badge.bg-secondary {
            background-color: #6C757D !important;
            color: white !important;
        }

        .badge.bg-success {
            background-color: #10B981 !important;
            color: white !important;
        }

        .preview-meta {
            background: #F5F5F5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .preview-meta p {
            margin: 5px 0;
            color: #4A4A4A;
        }

        .preview-meta strong {
            color: #1C1C1C;
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
                <h1><i class="fas fa-plus-circle"></i> Thêm Sản Phẩm</h1>
                <p>Thêm sản phẩm mới vào kho hàng</p>
            </div>
            <a href="products.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="product-form-container">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Lỗi!</strong> <?php echo htmlspecialchars($error); ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Thành công!</strong> <?php echo htmlspecialchars($success); ?>
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
                                <label for="name" class="form-label">
                                    Tên Sản Phẩm <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       placeholder="Nhập tên sản phẩm" required>
                                <span class="form-text"><i class="fas fa-lightbulb"></i> Ví dụ: Camera Fujifilm XT-30</span>
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">
                                    Slug <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>"
                                           placeholder="camera-fujifilm-xt30" required>
                                    <button type="button" class="btn btn-outline-secondary" id="generateSlug">
                                        <i class="fas fa-wand-magic-sparkles"></i> Tự Động
                                    </button>
                                </div>
                                <span class="form-text">URL-friendly slug (chỉ dùng chữ thường, số, dấu gạch ngang)</span>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Mô Tả
                                </label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="6" placeholder="Nhập mô tả chi tiết sản phẩm"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
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
                                    <label for="price" class="form-label">
                                        Giá (VNĐ) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                                           placeholder="0" step="1000" required>
                                    <span class="form-text">Giá bán cho khách hàng</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">
                                        Tồn Kho <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>"
                                           placeholder="0" min="0" required>
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
                                    <label for="category" class="form-label">
                                        Danh Mục <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="category" name="category" 
                                           value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>"
                                           list="categoryList" placeholder="Camera / Ống Kính / Phụ Kiện" required>
                                    <datalist id="categoryList">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <span class="form-text">Danh mục sản phẩm</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="brand_id" class="form-label">
                                        Thương Hiệu
                                    </label>
                                    <select class="form-select" id="brand_id" name="brand_id">
                                        <option value="0">-- Chọn thương hiệu --</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>" 
                                                <?php echo ((int)($_POST['brand_id'] ?? 0) === $brand['id']) ? 'selected' : ''; ?>>
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
                                    <label for="image" class="form-label">
                                        URL Hình Ảnh
                                    </label>
                                    <input type="url" class="form-control" id="image" name="image" 
                                           value="<?php echo htmlspecialchars($_POST['image'] ?? ''); ?>"
                                           placeholder="https://example.com/image.jpg">
                                    <span class="form-text">Link hình ảnh sản phẩm (HTTPS)</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="badge" class="form-label">
                                        Badge/Nhãn
                                    </label>
                                    <input type="text" class="form-control" id="badge" name="badge" 
                                           value="<?php echo htmlspecialchars($_POST['badge'] ?? ''); ?>"
                                           placeholder="Mới / Hot / Giảm 20%">
                                    <span class="form-text">Nhãn hiển thị trên sản phẩm</span>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                       value="1" <?php echo (isset($_POST['is_featured'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
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
                                <i class="fas fa-save"></i> Thêm Sản Phẩm
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
                        <div style="position: relative; overflow: hidden; border-radius: 10px 10px 0 0;">
                            <img id="imagePreview" src="https://via.placeholder.com/400x280?text=Ảnh+Sản+Phẩm" 
                                 alt="Preview" class="preview-image" style="width: 100%; height: 280px; object-fit: cover; display: block;">
                            <div class="preview-badges-container">
                                <div id="previewBadgeLeft" class="preview-badge-left" style="display: none;"></div>
                                <div id="previewBadgeRight" class="preview-badge-right" style="display: none;">Còn hàng</div>
                            </div>
                        </div>
                        <div class="preview-content">
                            <div class="preview-category" id="previewCategory">CAMERA</div>
                            <div class="preview-name" id="previewName">Tên Sản Phẩm</div>
                            <div class="preview-description" id="previewDescription">-</div>
                            
                            <div class="preview-price-box">
                                <div class="preview-price" id="previewPrice">0 VNĐ</div>
                                <button class="btn btn-dark" style="padding: 8px 15px; border-radius: 8px;">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                            
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #E8E8E8;">
                                <p style="margin: 8px 0; font-size: 0.9rem; color: #666;">
                                    <strong>Tồn kho:</strong> <span id="previewStock">0</span>
                                </p>
                                <p style="margin: 8px 0; font-size: 0.9rem; color: #666;">
                                    <strong>Nổi bật:</strong> 
                                    <span id="previewFeatured" class="badge bg-secondary">Không</span>
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
        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Trigger preview updates for existing values
            if (document.getElementById('name').value) {
                document.getElementById('name').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('price').value) {
                document.getElementById('price').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('category').value) {
                document.getElementById('category').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('image').value) {
                document.getElementById('image').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('badge').value) {
                document.getElementById('badge').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('stock').value) {
                document.getElementById('stock').dispatchEvent(new Event('input'));
            }
            if (document.getElementById('is_featured').checked) {
                document.getElementById('is_featured').dispatchEvent(new Event('change'));
            }
        });

        // Generate slug from name
        document.getElementById('generateSlug').addEventListener('click', function() {
            const name = document.getElementById('name').value;
            if (name) {
                const slug = name
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                document.getElementById('slug').value = slug;
            }
        });

        // Real-time preview updates
        document.getElementById('name').addEventListener('input', function() {
            document.getElementById('previewName').textContent = this.value || 'Tên Sản Phẩm';
        });

        document.getElementById('price').addEventListener('input', function() {
            const price = parseInt(this.value) || 0;
            document.getElementById('previewPrice').textContent = new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
        });

        document.getElementById('category').addEventListener('input', function() {
            document.getElementById('previewCategory').textContent = 'Danh mục: ' + (this.value || '-');
        });

        document.getElementById('brand_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            document.getElementById('previewBrand').textContent = 'Thương hiệu: ' + (option.text !== '-- Chọn thương hiệu --' ? option.text : '-');
        });

        document.getElementById('image').addEventListener('input', function() {
            const imageUrl = this.value.trim();
            if (imageUrl) {
                // Validate URL format
                try {
                    new URL(imageUrl);
                    const img = document.getElementById('imagePreview');
                    img.src = imageUrl;
                    img.onerror = function() {
                        this.src = 'https://via.placeholder.com/400x280?text=Lỗi+Tải+Ảnh';
                        console.error('Không thể tải hình ảnh từ URL: ' + imageUrl);
                    };
                } catch (e) {
                    document.getElementById('imagePreview').src = 'https://via.placeholder.com/400x280?text=URL+Không+Hợp+Lệ';
                }
            } else {
                document.getElementById('imagePreview').src = 'https://via.placeholder.com/400x280?text=Ảnh+Sản+Phẩm';
            }
        });

        document.getElementById('badge').addEventListener('input', function() {
            const badgeLeft = document.getElementById('previewBadgeLeft');
            if (this.value) {
                badgeLeft.textContent = this.value;
                badgeLeft.style.display = 'block';
            } else {
                badgeLeft.style.display = 'none';
            }
        });

        document.getElementById('stock').addEventListener('input', function() {
            document.getElementById('previewStock').textContent = this.value || '0';
        });

        document.getElementById('is_featured').addEventListener('change', function() {
            const badge = document.getElementById('previewFeatured');
            if (this.checked) {
                badge.textContent = 'Có';
                badge.className = 'badge bg-success';
            } else {
                badge.textContent = 'Không';
                badge.className = 'badge bg-secondary';
            }
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const slug = document.getElementById('slug').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const category = document.getElementById('category').value.trim();

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
