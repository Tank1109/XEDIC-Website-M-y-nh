<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/users.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get admin users
$stmt = $db->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Admin - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Quản Lý Admin</h1>
            <p class="text-muted">Tổng cộng: <?php echo count($admins); ?> admin</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-plus"></i> Thêm Admin
            </button>
        </div>
    </div>
    
    <!-- Admins Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Tên Đầy Đủ</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($admins) > 0): ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                <td>
                                    <span class="status <?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $admin['is_active'] ? 'Hoạt động' : 'Bị khóa'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn edit" onclick="editAdmin(<?php echo $admin['id']; ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($admin['id'] !== $_SESSION['user_id']): ?>
                                            <button class="action-btn toggle" onclick="toggleStatus(<?php echo $admin['id']; ?>)" title="<?php echo $admin['is_active'] ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                                <i class="fas fa-<?php echo $admin['is_active'] ? 'lock-open' : 'lock'; ?>"></i>
                                            </button>
                                            <button class="action-btn delete" onclick="deleteAdmin(<?php echo $admin['id']; ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <p>Không có admin nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add/Edit Admin Modal -->
    <div class="modal fade" id="addAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Admin Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertContainer"></div>
                    <form id="adminForm">
                        <input type="hidden" id="adminId" name="id" value="">
                        
                        <div class="form-group" id="usernameGroup">
                            <label>Tên Đăng Nhập</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                            <small class="text-muted">Chỉ thay đổi được khi thêm mới</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tên Đầy Đủ</label>
                            <input type="text" name="full_name" id="full_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Mật Khẩu</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted" id="passwordHint">Để trống nếu không muốn đổi mật khẩu</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveAdmin()">Lưu</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('addAdminModal'));
        let isEditMode = false;

        function resetForm() {
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
            document.getElementById('username').removeAttribute('readonly');
            document.getElementById('username').parentElement.style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Thêm Admin Mới';
            document.getElementById('password').required = true;
            document.getElementById('passwordHint').textContent = 'Bắt buộc';
            document.getElementById('alertContainer').innerHTML = '';
            isEditMode = false;
        }

        function editAdmin(id) {
            resetForm();
            
            // Fetch admin data
            fetch(`../api/admin-management.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const admin = data.admin;
                    document.getElementById('adminId').value = admin.id;
                    document.getElementById('username').value = admin.username;
                    document.getElementById('username').setAttribute('readonly', 'readonly');
                    document.getElementById('email').value = admin.email;
                    document.getElementById('full_name').value = admin.full_name;
                    document.getElementById('password').value = '';
                    document.getElementById('password').required = false;
                    
                    document.getElementById('modalTitle').textContent = 'Sửa Thông Tin Admin';
                    document.getElementById('passwordHint').textContent = 'Để trống nếu không muốn đổi mật khẩu';
                    
                    isEditMode = true;
                    modal.show();
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Lỗi khi tải dữ liệu');
            });
        }

        function saveAdmin() {
            const id = document.getElementById('adminId').value;
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const full_name = document.getElementById('full_name').value.trim();
            const password = document.getElementById('password').value;
            
            // Validation
            if (!username || !email || !full_name) {
                showAlert('warning', 'Vui lòng điền đầy đủ thông tin');
                return;
            }
            
            if (!isEditMode && !password) {
                showAlert('warning', 'Vui lòng nhập mật khẩu');
                return;
            }
            
            if (password && password.length < 6) {
                showAlert('warning', 'Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', isEditMode ? 'edit' : 'add');
            if (id) formData.append('id', id);
            formData.append('username', username);
            formData.append('email', email);
            formData.append('full_name', full_name);
            if (password) formData.append('password', password);
            
            fetch('../api/admin-management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Lỗi khi lưu dữ liệu');
            });
        }

        function deleteAdmin(id) {
            if (confirm('Bạn chắc chắn muốn xóa admin này? Hành động này không thể hoàn tác.')) {
                fetch('../api/admin-management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi khi xóa admin');
                });
            }
        }

        function toggleStatus(id) {
            fetch('../api/admin-management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle-status&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi cập nhật trạng thái');
            });
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            alertContainer.innerHTML = alertHtml;
        }

        // Reset form when modal is closed
        document.getElementById('addAdminModal').addEventListener('hidden.bs.modal', function() {
            resetForm();
        });
    </script>
</body>
</html>
