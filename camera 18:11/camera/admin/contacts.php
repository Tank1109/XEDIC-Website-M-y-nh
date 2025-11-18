<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/contacts.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get contacts with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $db->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM contacts");
$stmt->execute();
$total_contacts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_contacts / $limit);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            header('Location: contacts.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Liên Hệ - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Quản Lý Liên Hệ</h1>
            <p class="text-muted">Tổng cộng: <?php echo number_format($total_contacts); ?> liên hệ</p>
        </div>
    </div>
    
    <!-- Search -->
    <div class="table-container" style="margin-bottom: 20px; padding: 20px;">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm liên hệ...">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    
    <!-- Contacts Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Số Điện Thoại</th>
                        <th>Dịch Vụ</th>
                        <th>Ngày Gửi</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($contacts) > 0): ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($contact['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                <td><?php echo htmlspecialchars($contact['service'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    $status = $contact['status'];
                                    $status_labels = [
                                        'new' => 'Mới',
                                        'processing' => 'Đang xử lý',
                                        'completed' => 'Hoàn thành'
                                    ];
                                    $status_classes = [
                                        'new' => 'warning',
                                        'processing' => 'primary',
                                        'completed' => 'success'
                                    ];
                                    ?>
                                    <span class="badge badge-<?php echo $status_classes[$status] ?? 'secondary'; ?>">
                                        <?php echo $status_labels[$status] ?? $status; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="contact-detail.php?id=<?php echo $contact['id']; ?>" class="action-btn view" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="action-btn delete" onclick="deleteContact(<?php echo $contact['id']; ?>)" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox" style="font-size: 30px; margin-bottom: 10px;"></i>
                                <p>Không có liên hệ nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4" style="padding: 20px;">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">Đầu</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Trước</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Tiếp</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>">Cuối</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteContact(id) {
            if (confirm('Bạn có chắc chắn muốn xóa liên hệ này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
