<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/index.php');
    exit;
}

require_once '../config/database.php';

// Get statistics
$database = new Database();
$db = $database->getConnection();

// Total Orders (Tổng thanh toán/đơn hàng - không tính hủy)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM payments WHERE status != 'cancelled'");
$stmt->execute();
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total Revenue (Doanh thu - chỉ tính những đơn đã giao hàng)
$stmt = $db->prepare("SELECT SUM(amount) as revenue FROM payments WHERE status != 'cancelled' AND delivery_status = 'delivered'");
$stmt->execute();
$revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

// Total Customers
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$stmt->execute();
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total Products
$stmt = $db->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending Orders (Chờ xử lý - thanh toán chưa hoàn thành, không tính hủy)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM payments WHERE status = 'pending' OR delivery_status IN ('pending', 'shipped')");
$stmt->execute();
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Monthly Revenue (Doanh thu tháng này - chỉ tính đã giao hàng)
$stmt = $db->prepare("SELECT SUM(amount) as revenue FROM payments WHERE status != 'cancelled' AND delivery_status = 'delivered' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
$stmt->execute();
$monthly_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

// New Customers This Month
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
$stmt->execute();
$new_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Low Stock Products (Sản phẩm sắp hết)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE stock <= 5");
$stmt->execute();
$low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent Orders (Lấy từ bảng payments - các thanh toán gần đây, không tính hủy)
$stmt = $db->prepare("SELECT p.*, u.full_name FROM payments p LEFT JOIN users u ON p.user_id = u.id WHERE p.status != 'cancelled' ORDER BY p.created_at DESC LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Contacts
$stmt = $db->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="text-muted">Chào mừng <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></p>
        </div>
        <div class="page-header-actions">
            <a href="orders.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Đơn Hàng
            </a>
        </div>
    </div>
    
    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <!-- Tổng Đơn Hàng -->
        <div class="card-stat">
            <div class="stat-icon primary">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-label">Tổng Đơn Hàng</div>
            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
        </div>
        
        <!-- Doanh Thu (Tất cả) -->
        <div class="card-stat success">
            <div class="stat-icon success">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-label">Doanh Thu</div>
            <div class="stat-value"><?php echo number_format($revenue, 0, ',', '.'); ?> VNĐ</div>
        </div>
        
        <!-- Doanh Thu Tháng Này -->
        <div class="card-stat info">
            <div class="stat-icon info">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-label">Doanh Thu Tháng</div>
            <div class="stat-value"><?php echo number_format($monthly_revenue, 0, ',', '.'); ?> VNĐ</div>
        </div>
        
        <!-- Đơn Hàng Chờ Xử Lý -->
        <div class="card-stat warning">
            <div class="stat-icon warning">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-label">Chờ Xử Lý</div>
            <div class="stat-value"><?php echo number_format($pending_orders); ?></div>
        </div>
        
        <!-- Khách Hàng -->
        <div class="card-stat secondary">
            <div class="stat-icon secondary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Khách Hàng</div>
            <div class="stat-value"><?php echo number_format($total_customers); ?></div>
        </div>
        
        <!-- Khách Hàng Mới -->
        <div class="card-stat primary-light">
            <div class="stat-icon primary-light">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-label">Khách Mới (Tháng)</div>
            <div class="stat-value"><?php echo number_format($new_customers); ?></div>
        </div>
        
        <!-- Sản Phẩm -->
        <div class="card-stat danger">
            <div class="stat-icon danger">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-label">Sản Phẩm</div>
            <div class="stat-value"><?php echo number_format($total_products); ?></div>
        </div>
        
        <!-- Sản Phẩm Sắp Hết -->
        <div class="card-stat danger-light">
            <div class="stat-icon danger-light">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-label">Sắp Hết Hàng</div>
            <div class="stat-value"><?php echo number_format($low_stock); ?></div>
        </div>
    </div>
    
    <!-- Recent Orders & Contacts -->
    <div class="row mt-4">
        <!-- Recent Orders (8 columns) -->
        <div class="col-lg-8">
            <div class="table-container">
                <div style="padding: 20px; border-bottom: 2px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin: 0;">Đơn Hàng Gần Đây</h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">Xem Tất Cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Khách Hàng</th>
                                <th>Số Tiền</th>
                                <th>Phương Thức</th>
                                <th>Thanh Toán</th>
                                <th>Giao Hàng</th>
                                <th>Ngày Tạo</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) > 0): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_id'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['full_name'] ?? 'Không xác định'); ?></td>
                                        <td><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</td>
                                        <td>
                                            <?php 
                                                $method_text = [
                                                    'vnpay' => 'VNPay',
                                                    'momo' => 'Momo',
                                                    'cod' => 'Thu hộ'
                                                ];
                                                echo $method_text[$order['method']] ?? ucfirst($order['method']);
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status <?php echo strtolower($order['status']); ?>">
                                                <?php 
                                                    $status_text = [
                                                        'pending' => 'Chờ xử lý',
                                                        'paid' => 'Đã thanh toán',
                                                        'confirmed' => 'Đã xác nhận',
                                                        'failed' => 'Thất bại',
                                                        'cancelled' => 'Hủy'
                                                    ];
                                                    echo $status_text[$order['status']] ?? ucfirst($order['status']);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status <?php echo strtolower($order['delivery_status'] ?? 'pending'); ?>">
                                                <?php 
                                                    $delivery_text = [
                                                        'pending' => 'Chờ gửi',
                                                        'shipped' => 'Đang gửi',
                                                        'delivered' => '✓ Đã giao'
                                                    ];
                                                    echo $delivery_text[$order['delivery_status'] ?? 'pending'] ?? 'Không xác định';
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <?php if (($order['delivery_status'] ?? 'pending') !== 'delivered'): ?>
                                                <button class="action-btn success" onclick="markDelivered('<?php echo htmlspecialchars($order['order_id']); ?>')" title="Đánh dấu đã giao hàng">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-success" style="font-size: 12px;">✓ Đã giao</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Không có thanh toán nào
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Contacts (4 columns) -->
        <div class="col-lg-4">
            <div class="table-container">
                <div style="padding: 20px; border-bottom: 2px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin: 0;">Liên Hệ Gần Đây</h5>
                    <a href="contacts.php" class="btn btn-sm btn-outline-primary">Xem Tất Cả</a>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($recent_contacts) > 0): ?>
                        <?php foreach ($recent_contacts as $contact): ?>
                            <div style="padding: 15px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                <p style="margin-bottom: 5px;"><strong><?php echo htmlspecialchars($contact['name']); ?></strong></p>
                                <p style="margin-bottom: 5px; font-size: 12px; color: var(--text-light);">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?>
                                </p>
                                <p style="margin: 0; font-size: 12px; color: var(--text-light);">
                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            Không có liên hệ nào
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * Đánh dấu đơn hàng đã giao
         */
        function markDelivered(orderId) {
            if (!confirm('Bạn có chắc chắn muốn đánh dấu đơn hàng này đã giao hàng?')) {
                return;
            }
            
            fetch('../api/mark-delivered.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page sau 1.5 giây để cập nhật doanh thu
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
            });
        }
        
        /**
         * Hiển thị thông báo
         */
        function showNotification(message, type = 'success') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto remove sau 5 giây
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
