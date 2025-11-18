<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/settings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <h1>Cài Đặt Hệ Thống</h1>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="table-container" style="padding: 30px;">
                <h5 class="mb-4">Thông Tin Trang Web</h5>
                
                <form>
                    <div class="mb-3">
                        <label class="form-label">Tên Cửa Hàng</label>
                        <input type="text" class="form-control" value="XEDIC Camera" placeholder="Nhập tên cửa hàng">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Liên Hệ</label>
                        <input type="email" class="form-control" placeholder="Nhập email liên hệ">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="tel" class="form-control" placeholder="Nhập số điện thoại">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa Chỉ</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập địa chỉ cửa hàng"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-3">Mạng Xã Hội</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Facebook</label>
                            <input type="text" class="form-control" placeholder="URL Facebook">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Instagram</label>
                            <input type="text" class="form-control" placeholder="URL Instagram">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">YouTube</label>
                            <input type="text" class="form-control" placeholder="URL YouTube">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Cài Đặt
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
