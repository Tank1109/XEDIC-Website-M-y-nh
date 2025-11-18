<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/brands.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get brands
$stmt = $db->prepare("SELECT * FROM brands ORDER BY display_order ASC");
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thương Hiệu - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Quản Lý Thương Hiệu</h1>
            <p class="text-muted">Tổng cộng: <?php echo count($brands); ?> thương hiệu</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                <i class="fas fa-plus"></i> Thêm Thương Hiệu
            </button>
        </div>
    </div>
    
    <!-- Brands Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tên Thương Hiệu</th>
                        <th>Slug</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($brands) > 0): ?>
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($brand['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($brand['slug']); ?></td>
                                <td>
                                    <span class="status <?php echo $brand['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $brand['is_active'] ? 'Hoạt động' : 'Bị khóa'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn edit" onclick="editBrand(<?php echo $brand['id']; ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <p>Không có thương hiệu nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Thương Hiệu Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBrandForm">
                        <div class="form-group">
                            <label>Tên Thương Hiệu</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Mô Tả</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="saveBrand()">Lưu</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBrand(id) {
            alert('Chức năng chỉnh sửa sẽ được thực hiện');
        }
        
        function saveBrand() {
            alert('Thương hiệu mới sẽ được thêm');
        }
    </script>
</body>
</html>
