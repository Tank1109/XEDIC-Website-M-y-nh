<?php
session_start();

// Check admin login
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/orders.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        
        $_SESSION['success'] = "Cập nhật trạng thái đơn hàng thành công!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi cập nhật trạng thái: " . $e->getMessage();
    }
    header('Location: orders.php');
    exit;
}

// Handle accept order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_order'])) {
    $orderId = $_POST['order_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE payments SET status = 'processing' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$orderId]);
        
        $_SESSION['success'] = "Đã tiếp nhận đơn hàng!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi tiếp nhận đơn hàng: " . $e->getMessage();
    }
    header('Location: orders.php');
    exit;
}

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = "WHERE 1=1";
$params = [];

if ($status_filter) {
    $where .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where .= " AND (p.id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Get orders from payments table
$sql = "SELECT p.*, u.full_name, u.email, u.phone 
        FROM payments p 
        LEFT JOIN users u ON p.user_id = u.id 
        $where
        ORDER BY p.created_at DESC 
        LIMIT $offset, $limit";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM payments p 
             LEFT JOIN users u ON p.user_id = u.id 
             $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total_orders = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_orders / $limit);

// Status definitions
$statuses = [
    'pending' => ['text' => 'Chờ xử lý', 'class' => 'warning'],
    'processing' => ['text' => 'Đang xử lý', 'class' => 'info'],
    'shipped' => ['text' => 'Đang giao', 'class' => 'primary'],
    'delivered' => ['text' => 'Đã giao', 'class' => 'success'],
    'cancelled' => ['text' => 'Đã hủy', 'class' => 'danger']
];

// Handle AJAX request for order details
if (isset($_GET['action']) && $_GET['action'] === 'get_order_details') {
    header('Content-Type: application/json');
    
    $orderId = $_GET['order_id'] ?? null;
    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Order ID required']);
        exit;
    }
    
    try {
        // Get order information
        $stmt = $pdo->prepare("SELECT p.*, u.full_name, u.email, u.phone, u.address 
                               FROM payments p 
                               LEFT JOIN users u ON p.user_id = u.id 
                               WHERE p.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Get order items from cart or order_items table if exists
        // Try to get from order_items table first
        $orderItems = [];
        try {
            $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image 
                                   FROM order_items oi 
                                   LEFT JOIN products p ON oi.product_id = p.id 
                                   WHERE oi.order_id = ?");
            $stmt->execute([$orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // order_items table might not exist, that's ok
        }
        
        // If no order items found, try to get from cart for this user
        if (empty($orderItems) && isset($order['user_id'])) {
            try {
                $stmt = $pdo->prepare("SELECT c.id, c.product_id, c.quantity, p.name as product_name, p.price, p.image 
                                       FROM cart c 
                                       LEFT JOIN products p ON c.product_id = p.id 
                                       WHERE c.user_id = ? 
                                       LIMIT 20");
                $stmt->execute([$order['user_id']]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // cart table might not exist
            }
        }
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $orderItems
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - XEDIC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .status-badge {
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-processing {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-shipped {
            background-color: #cfe2ff;
            color: #084298;
            border: 1px solid #b6d4fe;
        }
        
        .status-delivered {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .action-btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div>
            <h1>Quản Lý Đơn Hàng</h1>
            <p class="text-muted">Tổng cộng: <?php echo number_format($total_orders); ?> đơn hàng</p>
        </div>
        <div class="page-header-actions">
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Xóa Lọc
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="table-container" style="margin-bottom: 20px; padding: 20px;">
        <div class="row g-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo mã đơn, tên khách hoặc email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="statusFilter" onchange="filterByStatus()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Đang giao</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 100px;">Mã Đơn</th>
                        <th style="width: 150px;">Khách Hàng</th>
                        <th style="width: 180px;">Email</th>
                        <th style="width: 120px;">Tổng Tiền</th>
                        <th style="width: 140px;">Phương Thức</th>
                        <th style="width: 130px;">Trạng Thái</th>
                        <th style="width: 180px;">Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></td>
                                <td><strong><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</strong></td>
                                <td>
                                    <?php 
                                    $methodDisplay = [
                                        'vnpay' => 'VNPay',
                                        'momo' => 'Momo',
                                        'cod' => 'COD',
                                        'transfer' => 'Chuyển khoản'
                                    ];
                                    echo htmlspecialchars($methodDisplay[$order['method']] ?? ucfirst($order['method']));
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                        <?php echo htmlspecialchars($statuses[$order['status']]['text'] ?? ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- View Details Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn-small" 
                                                onclick="viewOrderDetails(<?php echo $order['id']; ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </button>
                                        
                                        <!-- Accept Order (only for pending) -->
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Tiếp nhận đơn hàng này?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" name="accept_order" class="btn btn-sm btn-outline-success action-btn-small" title="Tiếp nhận">
                                                    <i class="fas fa-check"></i> Tiếp nhận
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <!-- Status Dropdown -->
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle action-btn-small" type="button" 
                                                    id="dropdownStatus<?php echo $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Trạng thái
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownStatus<?php echo $order['id']; ?>">
                                                <li><h6 class="dropdown-header">Đổi trạng thái</h6></li>
                                                <?php foreach ($statuses as $statusKey => $statusInfo): ?>
                                                    <?php if ($statusKey !== $order['status']): ?>
                                                        <li>
                                                            <form method="POST" style="display: inline-block; width: 100%;">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                <input type="hidden" name="new_status" value="<?php echo $statusKey; ?>">
                                                                <button type="submit" name="update_status" class="dropdown-item" style="border: none; background: none; text-align: left; padding: 0.5rem 1rem; cursor: pointer; width: 100%;">
                                                                    <i class="fas fa-arrow-right me-2"></i><?php echo $statusInfo['text']; ?>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox" style="font-size: 30px; margin-bottom: 10px;"></i>
                                <p>Không có đơn hàng nào</p>
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
                            <a class="page-link" href="?page=1<?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Đầu</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Trước</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Tiếp</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Cuối</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-receipt me-2"></i>Chi Tiết Đơn Hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const searchParam = new URLSearchParams(window.location.search).get('search') || '';
            if (status) {
                window.location.href = '?status=' + status + (searchParam ? '&search=' + encodeURIComponent(searchParam) : '');
            } else {
                window.location.href = '?' + (searchParam ? 'search=' + encodeURIComponent(searchParam) : '');
            }
        }

        function viewOrderDetails(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            const contentDiv = document.getElementById('orderDetailsContent');
            
            // Show loading state
            contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
            modal.show();
            
            // Fetch order details via AJAX
            fetch(`orders.php?action=get_order_details&order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const items = data.items || [];
                        
                        let html = `
                            <div class="order-details-container">
                                <!-- Customer Information -->
                                <div class="details-section">
                                    <h6 class="section-title"><i class="fas fa-user me-2"></i>Thông Tin Khách Hàng</h6>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="label">Tên khách hàng:</span>
                                            <span class="value">${order.full_name || 'Chưa cập nhật'}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Email:</span>
                                            <span class="value">${order.email || 'Chưa cập nhật'}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Số điện thoại:</span>
                                            <span class="value">${order.phone || 'Chưa cập nhật'}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Địa chỉ:</span>
                                            <span class="value">${order.address || 'Chưa cập nhật'}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Information -->
                                <div class="details-section">
                                    <h6 class="section-title"><i class="fas fa-box me-2"></i>Thông Tin Đơn Hàng</h6>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="label">Mã đơn hàng:</span>
                                            <span class="value">#${order.id}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Phương thức thanh toán:</span>
                                            <span class="value">${order.method}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Tổng tiền:</span>
                                            <span class="value" style="color: #FF5733; font-weight: bold;">${new Intl.NumberFormat('vi-VN').format(order.amount)} VNĐ</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Ngày tạo:</span>
                                            <span class="value">${new Date(order.created_at).toLocaleString('vi-VN')}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Information from Payments Table -->
                                <div class="details-section">
                                    <h6 class="section-title"><i class="fas fa-truck me-2"></i>Thông Tin Giao Hàng (Từ Đơn Hàng)</h6>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="label">Số điện thoại giao hàng:</span>
                                            <span class="value">${order.shipping_phone || 'Chưa cập nhật'}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Địa chỉ giao hàng:</span>
                                            <span class="value">${order.shipping_address || 'Chưa cập nhật'}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Account Information -->
                                <div class="details-section">
                                    <h6 class="section-title"><i class="fas fa-id-card me-2"></i>Thông Tin Tài Khoản Người Dùng</h6>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="label">Số điện thoại tài khoản:</span>
                                            <span class="value">${order.phone || 'Chưa cập nhật'}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">Địa chỉ tài khoản:</span>
                                            <span class="value">${order.address || 'Chưa cập nhật'}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="details-section">
                                    <h6 class="section-title"><i class="fas fa-shopping-cart me-2"></i>Danh Sách Sản Phẩm</h6>
                                    ${items.length > 0 ? `
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Sản Phẩm</th>
                                                        <th class="text-center">Số Lượng</th>
                                                        <th class="text-right">Giá</th>
                                                        <th class="text-right">Thành Tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${items.map(item => `
                                                        <tr>
                                                            <td>
                                                                <div class="item-info">
                                                                    ${item.image ? `<img src="${item.image}" alt="${item.product_name}" class="item-image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">` : ''}
                                                                    <span>${item.product_name || 'N/A'}</span>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">${item.quantity || 1}</td>
                                                            <td class="text-right">${new Intl.NumberFormat('vi-VN').format(item.price || 0)} VNĐ</td>
                                                            <td class="text-right"><strong>${new Intl.NumberFormat('vi-VN').format((item.price || 0) * (item.quantity || 1))}</strong></td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    ` : `
                                        <div class="text-center py-3">
                                            <i class="fas fa-inbox text-muted" style="font-size: 30px;"></i>
                                            <p class="text-muted mt-2">Không có sản phẩm nào</p>
                                        </div>
                                    `}
                                </div>
                            </div>
                        `;
                        
                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Lỗi tải thông tin đơn hàng'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = `<div class="alert alert-danger">Lỗi kết nối: ${error.message}</div>`;
                });
        }
    </script>

    <style>
        .order-details-container {
            padding: 10px 0;
        }

        .details-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .details-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1C1C1C;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #FF5733;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-item .label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
        }

        .info-item .value {
            color: #1C1C1C;
            font-size: 1rem;
            word-break: break-word;
        }

        .item-info {
            display: flex;
            align-items: center;
        }

        @media (max-width: 576px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
