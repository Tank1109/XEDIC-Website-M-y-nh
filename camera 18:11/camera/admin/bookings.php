<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/bookings.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get bookings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $db->prepare("SELECT sb.*, COALESCE(u.full_name, sb.full_name) as customer_name, s.name as service_name 
                      FROM service_bookings sb 
                      LEFT JOIN users u ON sb.user_id = u.id 
                      LEFT JOIN services s ON sb.service_id = s.id 
                      ORDER BY sb.created_at DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM service_bookings");
$stmt->execute();
$total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_bookings / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đặt Lịch Dịch Vụ - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Quản Lý Đặt Lịch Dịch Vụ</h1>
            <p class="text-muted">Tổng cộng: <?php echo number_format($total_bookings); ?> đặt lịch</p>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Khách Hàng</th>
                        <th>Dịch Vụ</th>
                        <th>Ngày Đặt Lịch</th>
                        <th>Trạng Thái</th>
                        <th>Ghi Chú</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['service_name'] ?? 'N/A'); ?></td>
                                <td><?php echo ($booking['appointment_date'] && $booking['appointment_date'] !== '0000-00-00') ? date('d/m/Y', strtotime($booking['appointment_date'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($booking['status']); ?>">
                                        <?php 
                                            $status_text = [
                                                'new' => 'Mới',
                                                'processing' => 'Đang xử lý',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Hủy'
                                            ];
                                            echo $status_text[$booking['status']] ?? ucfirst($booking['status']);
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(substr($booking['notes'] ?? '', 0, 30)); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($booking['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="action-btn view" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox" style="font-size: 30px; margin-bottom: 10px;"></i>
                                <p>Không có đặt lịch nào</p>
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
</body>
</html>
