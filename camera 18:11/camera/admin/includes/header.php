<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/auth.php';

// Check admin login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top admin-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-camera"></i> XEDIC Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-cog"></i> Hồ sơ cá nhân</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <ul class="sidebar-menu">
            <li class="menu-item <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                <a href="index.php" class="menu-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="menu-header">Quản Lý Sản Phẩm</li>
            
            <li class="menu-item <?php echo $current_page === 'products' ? 'active' : ''; ?>">
                <a href="products.php" class="menu-link">
                    <i class="fas fa-box"></i>
                    <span>Sản Phẩm</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page === 'categories' ? 'active' : ''; ?>">
                <a href="categories.php" class="menu-link">
                    <i class="fas fa-th-list"></i>
                    <span>Danh Mục</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page === 'brands' ? 'active' : ''; ?>">
                <a href="brands.php" class="menu-link">
                    <i class="fas fa-crown"></i>
                    <span>Thương Hiệu</span>
                </a>
            </li>
            
            <li class="menu-header">Quản Lý Bán Hàng</li>
            
            <li class="menu-item <?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                <a href="orders.php" class="menu-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Đơn Hàng</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page === 'bookings' ? 'active' : ''; ?>">
                <a href="bookings.php" class="menu-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Đặt Lịch Dịch Vụ</span>
                </a>
            </li>
            
            <li class="menu-header">Quản Lý Khách Hàng</li>
            
            <li class="menu-item <?php echo $current_page === 'customers' ? 'active' : ''; ?>">
                <a href="customers.php" class="menu-link">
                    <i class="fas fa-users"></i>
                    <span>Khách Hàng</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page === 'contacts' ? 'active' : ''; ?>">
                <a href="contacts.php" class="menu-link">
                    <i class="fas fa-envelope"></i>
                    <span>Liên Hệ</span>
                </a>
            </li>
            
            <li class="menu-header">Hệ Thống</li>
            
            <li class="menu-item <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                <a href="users.php" class="menu-link">
                    <i class="fas fa-user-shield"></i>
                    <span>Quản Lý Admin</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                <a href="settings.php" class="menu-link">
                    <i class="fas fa-sliders-h"></i>
                    <span>Cài Đặt</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <main class="admin-content">
