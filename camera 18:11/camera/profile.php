<?php
session_start();
require_once 'config/database.php'; 
require_once 'auth/auth.php';   

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Khởi tạo database connection
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die('Database connection failed. Please check your database configuration.');
}

// Handle AJAX request for order details
if (isset($_GET['action']) && $_GET['action'] === 'get_order_details') {
    header('Content-Type: application/json');
    
    $orderId = $_GET['order_id'] ?? null;
    if (!$orderId) {
        echo json_encode(['success' => false, 'message' => 'Order ID required']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Lấy thông tin đơn hàng từ payments
        $stmt = $pdo->prepare("SELECT p.*, u.full_name, u.email, u.phone as user_phone, u.address as user_address 
                               FROM payments p 
                               LEFT JOIN users u ON p.user_id = u.id 
                               WHERE p.id = ? AND p.user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Lấy chi tiết sản phẩm từ order_items
        $stmt = $pdo->prepare("
            SELECT oi.product_id, oi.product_name, oi.product_price, oi.quantity, oi.subtotal,
                   p.image as product_image
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// Xử lý cập nhật profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $userId = $_SESSION['user_id'];
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($fullName) || empty($email)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin";
    } else {
        try {
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fullName, $email, $phone, $address, $userId]);

            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;

            $_SESSION['success'] = "Cập nhật thông tin thành công!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật thông tin";
        }
    }
    header('Location: profile.php?section=profile');
    exit;
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error'] = "Mật khẩu hiện tại không đúng";
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "Mật khẩu mới không khớp";
        } elseif (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            $_SESSION['success'] = "Đổi mật khẩu thành công!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra khi đổi mật khẩu";
    }
    header('Location: profile.php?section=settings');
    exit;
}

// Xử lý hủy đơn hàng từ bảng payments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $paymentId = $_POST['order_id'];
    $userId = $_SESSION['user_id'];
    
    try {
        // Kiểm tra đơn hàng thuộc về user và đang ở trạng thái pending
        $stmt = $pdo->prepare("SELECT status FROM payments WHERE id = ? AND user_id = ?");
        $stmt->execute([$paymentId, $userId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($payment && $payment['status'] === 'pending') {
            $stmt = $pdo->prepare("UPDATE payments SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$paymentId]);
            $_SESSION['success'] = "Đã hủy đơn hàng thành công!";
        } else {
            $_SESSION['error'] = "Không thể hủy đơn hàng này";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra khi hủy đơn hàng";
    }
    header('Location: profile.php?section=orders');
    exit;
}

// Xử lý thêm sản phẩm vào wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    
    try {
        // Tạo bảng wishlist nếu chưa có
        $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist (user_id, product_id)
        )");
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        $_SESSION['success'] = "Đã thêm vào danh sách yêu thích!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra";
    }
    header('Location: profile.php?section=wishlist');
    exit;
}

// Xử lý xóa khỏi wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_wishlist'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $_SESSION['success'] = "Đã xóa khỏi danh sách yêu thích!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra";
    }
    header('Location: profile.php?section=wishlist');
    exit;
}

// Lấy thông tin user từ database
$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $username = $user['username'];
        $email = $user['email'] ?? 'Chưa cập nhật';
        $fullName = $user['full_name'] ?? $username;
        $phone = $user['phone'] ?? 'Chưa cập nhật';
        $address = $user['address'] ?? 'Chưa cập nhật';
        $createdAt = $user['created_at'];
        $avatar = $_SESSION['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=1C1C1C&color=fff&bold=true';
    }
} catch (PDOException $e) {
    $error = "Không thể lấy thông tin người dùng";
}

// Lấy thống kê
$stats = [
    'total_orders' => 0,
    'total_spent' => 0,
    'pending_orders' => 0,
    'wishlist_count' => 0
];

try {
    // Tổng đơn hàng từ bảng payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM payments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tổng chi tiêu từ bảng payments
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status != 'cancelled'");
    $stmt->execute([$userId]);
    $stats['total_spent'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Đơn hàng đang xử lý từ bảng payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM payments WHERE user_id = ? AND status IN ('pending', 'processing')");
    $stmt->execute([$userId]);
    $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Wishlist
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['wishlist_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    // Ignore stats errors
}

// Lấy danh sách đơn hàng từ bảng payments
$orders = [];
$orderFilter = $_GET['filter'] ?? 'all';
try {
    $sql = "SELECT p.id, p.order_id, p.user_id, p.method, p.amount, p.status, 
                   p.shipping_phone, p.shipping_address, p.created_at, p.updated_at
            FROM payments p 
            WHERE p.user_id = ?";
    
    if ($orderFilter !== 'all') {
        $sql .= " AND p.status = ?";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    if ($orderFilter !== 'all') {
        $stmt->execute([$userId, $orderFilter]);
    } else {
        $stmt->execute([$userId]);
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orderError = "Không thể lấy danh sách đơn hàng";
}

// Lấy chi tiết đơn hàng từ bảng payments
$orderDetails = [];
$selectedOrder = null;
if (isset($_GET['order_id'])) {
    $paymentId = $_GET['order_id'];
    try {
        // Lấy thông tin đơn hàng từ payments
        $stmt = $pdo->prepare("SELECT p.*, u.full_name, u.email, u.phone as user_phone, u.address as user_address 
                               FROM payments p 
                               LEFT JOIN users u ON p.user_id = u.id 
                               WHERE p.id = ? AND p.user_id = ?");
        $stmt->execute([$paymentId, $userId]);
        $selectedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy chi tiết sản phẩm từ order_items
        if ($selectedOrder) {
            $stmt = $pdo->prepare("
                SELECT oi.product_id, oi.product_name, oi.product_price, oi.quantity, oi.subtotal,
                       p.image as product_image
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ");
            $stmt->execute([$paymentId]);
            $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Nếu không có dữ liệu trong order_items, thử lấy từ cart (fallback - chỉ để tham khảo)
            if (empty($orderDetails)) {
                $stmt = $pdo->prepare("
                    SELECT c.quantity, c.product_id,
                           p.name as product_name, p.price as product_price, p.image as product_image,
                           (c.quantity * p.price) as subtotal
                    FROM cart c 
                    LEFT JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?
                    ORDER BY c.id
                    LIMIT 20
                ");
                $stmt->execute([$userId]);
                $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        $orderDetailError = "Không thể lấy chi tiết đơn hàng";
    }
}

// Lấy wishlist
$wishlistItems = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, w.created_at as added_date 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$userId]);
    $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Wishlist table might not exist yet
}

$section = $_GET['section'] ?? 'dashboard';
$isEditing = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($username); ?> | XEDIC Camera</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
        <link href="css/profile.css" rel="stylesheet">

</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-layout">
        <div class="container">
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" data-aos="fade-down">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" data-aos="fade-down">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="profile-sidebar" data-aos="fade-right">
                        <div class="list-group">
                            <a href="?section=dashboard" class="list-group-item <?= $section === 'dashboard' ? 'active' : '' ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="?section=profile" class="list-group-item <?= $section === 'profile' ? 'active' : '' ?>">
                                <i class="fas fa-user"></i> Hồ sơ
                            </a>
                            <a href="?section=orders" class="list-group-item <?= $section === 'orders' ? 'active' : '' ?>">
                                <i class="fas fa-shopping-bag"></i> Đơn hàng
                                <?php if ($stats['pending_orders'] > 0): ?>
                                    <span class="badge bg-danger ms-auto"><?= $stats['pending_orders'] ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="?section=addresses" class="list-group-item <?= $section === 'addresses' ? 'active' : '' ?>">
                                <i class="fas fa-map-marker-alt"></i> Địa chỉ
                            </a>
                            <a href="?section=settings" class="list-group-item <?= $section === 'settings' ? 'active' : '' ?>">
                                <i class="fas fa-cog"></i> Cài đặt
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="profile-card" data-aos="fade-left">
                        <!-- Profile Header -->
                        <div class="profile-header">
                            <img src="<?= $avatar ?>" alt="Avatar" class="profile-avatar">
                            <h3 class="profile-name"><?= htmlspecialchars($fullName) ?></h3>
                            <p class="profile-email"><?= htmlspecialchars($email) ?></p>
                        </div>

                        <!-- Content -->
                        <div class="profile-content">
                            <?php if ($section === 'dashboard'): ?>
                                <h4 class="section-title">Dashboard</h4>
                                
                                <!-- Stats -->
                                <div class="stats-grid">
                                    <div class="stat-card">
                                        <div class="stat-value"><?= $stats['total_orders'] ?></div>
                                        <div class="stat-label">Tổng đơn hàng</div>
                                    </div>
                                    <div class="stat-card success">
                                        <div class="stat-value"><?= number_format($stats['total_spent'], 0, ',', '.') ?> ₫</div>
                                        <div class="stat-label">Tổng chi tiêu</div>
                                    </div>
                                    <div class="stat-card warning">
                                        <div class="stat-value"><?= $stats['pending_orders'] ?></div>
                                        <div class="stat-label">Đơn đang xử lý</div>
                                    </div>
                                   

                                <!-- Recent Orders -->
                                <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Đơn hàng gần đây</h5>
                                <?php
                                $recentOrders = array_slice($orders, 0, 3);
                                if (!empty($recentOrders)):
                                ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <div class="order-card">
                                            <div class="order-header">
                                                <div>
                                                    <div class="order-number">
                                                        <i class="fas fa-receipt me-2"></i>
                                                        #<?= htmlspecialchars($order['order_id']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <span class="order-status status-<?= $order['status'] ?>">
                                                    <?php
                                                    $statusText = [
                                                        'pending' => 'Chờ xử lý',
                                                        'processing' => 'Đang xử lý',
                                                        'shipped' => 'Đang giao',
                                                        'delivered' => 'Đã giao',
                                                        'cancelled' => 'Đã hủy'
                                                    ];
                                                    echo $statusText[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="order-total">
                                                    <?= number_format($order['amount'], 0, ',', '.') ?> VNĐ
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>Chi tiết
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center mt-3">
                                        <a href="?section=orders" class="btn btn-primary">Xem tất cả đơn hàng</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Chưa có đơn hàng nào</p>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($section === 'profile'): ?>
                                <h4 class="section-title">Thông tin cá nhân</h4>
                                
                                <?php if ($isEditing): ?>
                                    <!-- Edit Form -->
                                    <form method="POST" action="profile.php">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Họ và tên</label>
                                                <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($fullName) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Số điện thoại</label>
                                                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Địa chỉ</label>
                                                <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($address) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                                            </button>
                                            <a href="?section=profile" class="btn btn-secondary">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- View Mode -->
                                    <div class="info-item">
                                        <i class="fas fa-id-card"></i>
                                        <div>
                                            <strong>ID:</strong> #<?= $userId ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-user-tag"></i>
                                        <div>
                                            <strong>Tên đăng nhập:</strong> <?= htmlspecialchars($username) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-envelope"></i>
                                        <div>
                                            <strong>Email:</strong> <?= htmlspecialchars($email) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <div>
                                            <strong>Số điện thoại:</strong> <?= htmlspecialchars($phone) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div>
                                            <strong>Địa chỉ:</strong> <?= htmlspecialchars($address) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($createdAt)) ?>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <a href="?section=profile&edit=1" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i> Chỉnh sửa hồ sơ
                                        </a>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($section === 'orders'): ?>
                                <h4 class="section-title">Lịch sử đơn hàng</h4>
                                
                                <!-- Filter Buttons -->
                                <div class="filter-buttons">
                                    <a href="?section=orders&filter=all" class="filter-btn <?= $orderFilter === 'all' ? 'active' : '' ?>">
                                        Tất cả
                                    </a>
                                    <a href="?section=orders&filter=pending" class="filter-btn <?= $orderFilter === 'pending' ? 'active' : '' ?>">
                                        Chờ xử lý
                                    </a>
                                    <a href="?section=orders&filter=processing" class="filter-btn <?= $orderFilter === 'processing' ? 'active' : '' ?>">
                                        Đang xử lý
                                    </a>
                                    <a href="?section=orders&filter=shipped" class="filter-btn <?= $orderFilter === 'shipped' ? 'active' : '' ?>">
                                        Đang giao
                                    </a>
                                    <a href="?section=orders&filter=delivered" class="filter-btn <?= $orderFilter === 'delivered' ? 'active' : '' ?>">
                                        Đã giao
                                    </a>
                                    <a href="?section=orders&filter=cancelled" class="filter-btn <?= $orderFilter === 'cancelled' ? 'active' : '' ?>">
                                        Đã hủy
                                    </a>
                                </div>
                                
                                <?php if (empty($orders)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">Không có đơn hàng nào.</p>
                                        <a href="/products.php" class="btn btn-primary mt-3">
                                            <i class="fas fa-shopping-cart me-2"></i>Mua sắm ngay
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <div class="order-card">
                                            <div class="order-header">
                                                <div>
                                                    <div class="order-number">
                                                        <i class="fas fa-receipt me-2"></i>
                                                        Đơn hàng #<?= htmlspecialchars($order['order_id']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <span class="order-status status-<?= $order['status'] ?>">
                                                    <?php
                                                    $statusText = [
                                                        'pending' => 'Chờ xử lý',
                                                        'processing' => 'Đang xử lý',
                                                        'shipped' => 'Đang giao',
                                                        'delivered' => 'Đã giao',
                                                        'cancelled' => 'Đã hủy'
                                                    ];
                                                    echo $statusText[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="order-info">
                                                <div class="order-info-item">
                                                    <span class="order-info-label">Tổng tiền</span>
                                                    <span class="order-info-value order-total">
                                                        <?= number_format($order['amount'], 0, ',', '.') ?> VNĐ
                                                    </span>
                                                </div>
                                                <div class="order-info-item">
                                                    <span class="order-info-label">Phương thức thanh toán</span>
                                                    <span class="order-info-value">
                                                        <?php
                                                        $methodDisplay = [
                                                            'vnpay' => 'VNPay',
                                                            'momo' => 'Momo',
                                                            'cod' => 'COD',
                                                            'transfer' => 'Chuyển khoản'
                                                        ];
                                                        echo htmlspecialchars($methodDisplay[$order['method']] ?? ucfirst($order['method']));
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="order-info-item">
                                                    <span class="order-info-label">Trạng thái thanh toán</span>
                                                    <span class="order-info-value">
                                                        <?php
                                                        $paymentStatusDisplay = [
                                                            'pending' => '⏳ Chờ thanh toán',
                                                            'processing' => '🔄 Đang xử lý',
                                                            'shipped' => '🚚 Đang giao hàng',
                                                            'delivered' => '✅ Đã giao',
                                                            'cancelled' => '❌ Đã hủy'
                                                        ];
                                                        echo $paymentStatusDisplay[$order['status']] ?? $order['status'];
                                                        ?>
                                                    </span>
                                                </div>
                                                <?php if ($order['shipping_address']): ?>
                                                <div class="order-info-item">
                                                    <span class="order-info-label">Địa chỉ giao hàng</span>
                                                    <span class="order-info-value">
                                                        <?= htmlspecialchars($order['shipping_address']) ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>Chi tiết
                                                </button>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                        <button type="submit" name="cancel_order" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-times me-1"></i>Hủy đơn
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <button class="btn btn-sm btn-outline-success" onclick="alert('Tính năng đánh giá đang phát triển')">
                                                        <i class="fas fa-star me-1"></i>Đánh giá
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Order Items Detail -->
                                            <?php if (isset($_GET['order_id']) && $_GET['order_id'] == $order['id'] && !empty($orderDetails)): ?>
                                                <div class="order-items">
                                                    <h6 class="mt-3 mb-2"><i class="fas fa-box me-2"></i>Chi tiết sản phẩm</h6>
                                                    <?php foreach ($orderDetails as $item): ?>
                                                        <div class="order-item">
                                                            <img src="<?= htmlspecialchars($item['product_image'] ?? 'assets/images/placeholder.jpg') ?>" 
                                                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                                 class="order-item-image">
                                                            <div class="order-item-info">
                                                                <div class="order-item-name">
                                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                                </div>
                                                                <div class="text-muted">
                                                                    Số lượng: <?= $item['quantity'] ?>
                                                                </div>
                                                                <div class="order-item-price">
                                                                    <?= number_format($item['product_price'], 0, ',', '.') ?> VNĐ
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <strong>
                                                                    <?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    
                                                    <!-- Order Summary -->
                                                    <?php if ($selectedOrder): ?>
                                                        <div class="mt-3 p-3 bg-light rounded">
                                                            <h6><i class="fas fa-info-circle me-2"></i>Thông tin giao hàng</h6>
                                                            <p class="mb-1"><strong>Người nhận:</strong> <?= htmlspecialchars($selectedOrder['full_name'] ?? 'Chưa cập nhật') ?></p>
                                                            <p class="mb-1"><strong>Số điện thoại:</strong> <?= htmlspecialchars($selectedOrder['shipping_phone'] ?? $selectedOrder['user_phone'] ?? 'Chưa cập nhật') ?></p>
                                                            <p class="mb-1"><strong>Địa chỉ:</strong> <?= htmlspecialchars($selectedOrder['shipping_address'] ?? $selectedOrder['user_address'] ?? 'Chưa cập nhật') ?></p>
                                                            <p class="mb-1"><strong>Phương thức thanh toán:</strong> 
                                                                <?php
                                                                $methodDisplay = [
                                                                    'vnpay' => 'VNPay',
                                                                    'momo' => 'Momo',
                                                                    'cod' => 'Thanh toán khi nhận hàng (COD)',
                                                                    'transfer' => 'Chuyển khoản ngân hàng'
                                                                ];
                                                                echo htmlspecialchars($methodDisplay[$selectedOrder['method']] ?? ucfirst($selectedOrder['method']));
                                                                ?>
                                                            </p>
                                                            <p class="mb-0"><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= number_format($selectedOrder['amount'], 0, ',', '.') ?> VNĐ</span></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php elseif ($section === 'wishlist'): ?>
                                <h4 class="section-title">Danh sách yêu thích</h4>
                                
                                <?php if (empty($wishlistItems)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">Chưa có sản phẩm nào trong danh sách yêu thích.</p>
                                        <a href="/products.php" class="btn btn-primary mt-3">
                                            <i class="fas fa-search me-2"></i>Khám phá sản phẩm
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="row g-3">
                                        <?php foreach ($wishlistItems as $product): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="product-card">
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                                         class="product-image">
                                                    <div class="product-body">
                                                        <div class="product-name">
                                                            <?= htmlspecialchars($product['name']) ?>
                                                        </div>
                                                        <div class="product-price mb-2">
                                                            <?= number_format($product['price'], 0, ',', '.') ?> VNĐ
                                                        </div>
                                                        <?php if ($product['badge']): ?>
                                                            <span class="badge bg-danger mb-2"><?= htmlspecialchars($product['badge']) ?></span>
                                                        <?php endif; ?>
                                                        <div class="d-flex gap-2">
                                                            <a href="products/product-detail.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary flex-grow-1">
                                                                <i class="fas fa-eye me-1"></i>Xem
                                                            </a>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                                <button type="submit" name="remove_from_wishlist" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <small class="text-muted d-block mt-2">
                                                            Thêm vào: <?= date('d/m/Y', strtotime($product['added_date'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($section === 'addresses'): ?>
                                <h4 class="section-title">Địa chỉ giao hàng</h4>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <strong>Địa chỉ mặc định:</strong><br>
                                        <?= htmlspecialchars($address) ?>
                                    </div>
                                </div>
                                <button class="btn btn-primary mt-3" onclick="alert('Tính năng đang phát triển')">
                                    <i class="fas fa-plus"></i> Thêm địa chỉ mới
                                </button>

                            <?php elseif ($section === 'settings'): ?>
                                <h4 class="section-title">Cài đặt tài khoản</h4>
                                
                                <!-- Change Password Section -->
                                <div class="mb-4">
                                    <h5 class="mb-3"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h5>
                                    <form method="POST" action="profile.php">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Mật khẩu hiện tại</label>
                                                <input type="password" class="form-control" name="current_password" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mật khẩu mới</label>
                                                <input type="password" class="form-control" name="new_password" required minlength="6">
                                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                                <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                            </div>
                                        </div>
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Đổi mật khẩu
                                        </button>
                                    </form>
                                </div>

                                <hr>

                                <!-- Account Settings -->
                                <div class="mb-4">
                                    <h5 class="mb-3"><i class="fas fa-bell me-2"></i>Thông báo</h5>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                        <label class="form-check-label" for="emailNotif">
                                            Nhận thông báo qua email
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="orderNotif" checked>
                                        <label class="form-check-label" for="orderNotif">
                                            Thông báo cập nhật đơn hàng
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="promoNotif">
                                        <label class="form-check-label" for="promoNotif">
                                            Nhận thông báo khuyến mãi
                                        </label>
                                    </div>
                                    <button class="btn btn-primary mt-3" onclick="alert('Tính năng đang phát triển')">
                                        <i class="fas fa-save me-2"></i>Lưu cài đặt
                                    </button>
                                </div>

                                <hr>

                                <!-- Danger Zone -->
                                <div>
                                    <h5 class="mb-3 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Vùng nguy hiểm</h5>
                                    <button class="btn btn-outline-danger" onclick="if(confirm('Bạn có chắc muốn xóa tài khoản? Hành động này không thể hoàn tác!')) alert('Tính năng đang phát triển')">
                                        <i class="fas fa-trash me-2"></i>Xóa tài khoản
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">
                        <i class="fas fa-receipt me-2"></i>Chi Tiết Đơn Hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-out-quart',
            once: true
        });

        // Auto hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // View Order Details in Modal
        function viewOrderDetails(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            const contentDiv = document.getElementById('orderDetailsContent');
            
            // Show loading state
            contentDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
            modal.show();
            
            // Fetch order details
            fetch(`profile.php?action=get_order_details&order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const items = data.items || [];
                        
                        const statusText = {
                            'pending': 'Chờ xử lý',
                            'processing': 'Đang xử lý',
                            'shipped': 'Đang giao',
                            'delivered': 'Đã giao',
                            'cancelled': 'Đã hủy'
                        };
                        
                        const methodDisplay = {
                            'vnpay': 'VNPay',
                            'momo': 'Momo',
                            'cod': 'Thanh toán khi nhận hàng (COD)',
                            'transfer': 'Chuyển khoản ngân hàng'
                        };
                        
                        let html = `
                            <div class="order-details-container">
                                <!-- Order Header -->
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><strong>Đơn hàng #${order.order_id}</strong></h6>
                                            <small>Ngày đặt: ${new Date(order.created_at).toLocaleString('vi-VN')}</small>
                                        </div>
                                        <span class="badge bg-${getStatusColor(order.status)} fs-6">
                                            ${statusText[order.status] || order.status}
                                        </span>
                                    </div>
                                </div>

                                <!-- Customer Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <i class="fas fa-user me-2"></i>Thông Tin Khách Hàng
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>Họ tên:</strong> ${order.full_name || 'Chưa cập nhật'}
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Email:</strong> ${order.email || 'Chưa cập nhật'}
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>SĐT tài khoản:</strong> ${order.user_phone || 'Chưa cập nhật'}
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <strong>Địa chỉ tài khoản:</strong> ${order.user_address || 'Chưa cập nhật'}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <i class="fas fa-truck me-2"></i>Thông Tin Giao Hàng
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>SĐT giao hàng:</strong> ${order.shipping_phone || order.user_phone || 'Chưa cập nhật'}
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <strong>Địa chỉ giao hàng:</strong> ${order.shipping_address || order.user_address || 'Chưa cập nhật'}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="fas fa-credit-card me-2"></i>Thông Tin Thanh Toán
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>Phương thức:</strong> ${methodDisplay[order.method] || order.method}
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Trạng thái:</strong> <span class="badge bg-${getStatusColor(order.status)}">${statusText[order.status] || order.status}</span>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <strong class="text-danger fs-5">Tổng tiền: ${new Intl.NumberFormat('vi-VN').format(order.amount)} VNĐ</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="card mb-3">
                                    <div class="card-header bg-dark text-white">
                                        <i class="fas fa-shopping-cart me-2"></i>Danh Sách Sản Phẩm
                                    </div>
                                    <div class="card-body p-0">
                                        ${items.length > 0 ? `
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Sản phẩm</th>
                                                            <th class="text-center">Số lượng</th>
                                                            <th class="text-end">Đơn giá</th>
                                                            <th class="text-end">Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        ${items.map(item => `
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        ${item.product_image ? `<img src="${item.product_image}" alt="${item.product_name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">` : ''}
                                                                        <span>${item.product_name || 'N/A'}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center"><strong>${item.quantity || 1}</strong></td>
                                                                <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.product_price || 0)} VNĐ</td>
                                                                <td class="text-end"><strong class="text-primary">${new Intl.NumberFormat('vi-VN').format(item.subtotal || 0)} VNĐ</strong></td>
                                                            </tr>
                                                        `).join('')}
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                                            <td class="text-end"><strong class="text-danger fs-5">${new Intl.NumberFormat('vi-VN').format(order.amount)} VNĐ</strong></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        ` : `
                                            <div class="text-center py-4">
                                                <i class="fas fa-inbox text-muted" style="font-size: 50px;"></i>
                                                <p class="text-muted mt-2">Không có sản phẩm nào</p>
                                            </div>
                                        `}
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <i class="fas fa-history me-2"></i>Lịch Sử Đơn Hàng
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><i class="fas fa-clock text-primary me-2"></i><strong>Ngày tạo:</strong> ${new Date(order.created_at).toLocaleString('vi-VN')}</p>
                                        ${order.updated_at ? `<p class="mb-0"><i class="fas fa-sync text-success me-2"></i><strong>Cập nhật:</strong> ${new Date(order.updated_at).toLocaleString('vi-VN')}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>${data.message || 'Không thể tải thông tin đơn hàng'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Lỗi kết nối: ${error.message}</div>`;
                });
        }

        function getStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'processing': 'info',
                'shipped': 'primary',
                'delivered': 'success',
                'cancelled': 'danger'
            };
            return colors[status] || 'secondary';
        }
    </script>
</body>
</html>
