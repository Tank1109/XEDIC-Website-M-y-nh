<?php
// Get cart count if logged in
$cartItemCount = 0;
if (isLoggedIn()) {
    require_once __DIR__ . '/../classes/Cart.php';
    $cart = new Cart($_SESSION['user_id'] ?? null);
    $cartItemCount = $cart->getItemCount();
}
?>
<!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">XEDIC</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#home">Trang Chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="service.php">Dịch Vụ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">Sản Phẩm</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">Giới Thiệu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Liên Hệ</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-3">
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="auth-btn">
                                Đăng nhập
                            </a>
                        <?php else: ?>
                            <!-- User Info -->
                            <span class="text-dark" style="font-size: 0.9rem;">
                                Xin chào, <a href="profile.php" class="username-link" title="Xem hồ sơ">
                                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                                </a>
                            </span>
                            
                            <!-- Cart -->
                            <a href="cart.php" class="cart-link-btn" title="Xem giỏ hàng của bạn">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if ($cartItemCount > 0): ?>
                                    <span class="cart-badge-pulse"><?php echo $cartItemCount; ?></span>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Logout -->
                            <a href="logout.php" class="auth-btn" title="Đăng xuất">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>