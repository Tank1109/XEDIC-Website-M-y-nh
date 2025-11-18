<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <h1>Hồ Sơ Cá Nhân</h1>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="table-container" style="padding: 30px;">
                <h5 class="mb-4">Thông Tin Cá Nhân</h5>
                
                <div class="mb-3">
                    <label class="form-label">Tên Đăng Nhập</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tên Đầy Đủ</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Vai Trò</label>
                    <input type="text" class="form-control" value="Quản Trị Viên" disabled>
                </div>
                
                <h5 class="mt-5 mb-4">Đổi Mật Khẩu</h5>
                
                <div id="alertContainer"></div>
                
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Mật Khẩu Hiện Tại</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật Khẩu Mới</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Xác Nhận Mật Khẩu</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">Cập Nhật Mật Khẩu</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.querySelector('input[name="current_password"]').value;
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const submitBtn = document.getElementById('submitBtn');
            
            // Basic validation
            if (!currentPassword || !newPassword || !confirmPassword) {
                showAlert('warning', 'Vui lòng điền đầy đủ thông tin');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('danger', 'Mật khẩu mới không trùng khớp');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('danger', 'Mật khẩu mới phải có ít nhất 6 ký tự');
                return;
            }
            
            if (newPassword === currentPassword) {
                showAlert('danger', 'Mật khẩu mới phải khác với mật khẩu hiện tại');
                return;
            }
            
            // Send request to API
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang cập nhật...';
            
            fetch('../api/change-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Cập Nhật Mật Khẩu';
                
                if (data.success) {
                    showAlert('success', data.message);
                    document.getElementById('changePasswordForm').reset();
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '../login.php?message=password_changed';
                    }, 2000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Cập Nhật Mật Khẩu';
                showAlert('danger', 'Lỗi khi cập nhật mật khẩu');
            });
        });
        
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'}"></i>
                <strong style="margin-left: 10px;">${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            alertContainer.innerHTML = alertHtml;
            
            // Auto-dismiss after 5 seconds (except success which redirects)
            if (type !== 'success') {
                setTimeout(() => {
                    const alert = alertContainer.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }
        }
    </script>
</body>
</html>
