<?php
session_start();
require_once 'config/database.php';
require_once 'auth/auth.php';

$auth = new Auth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');

        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'confirm_password' => $confirm_password,
            'full_name' => $full_name
        ];

        $result = $auth->register($data);

        if ($result['success']) {
            $success = $result['message'];
            header('refresh:3;url=login.php');
        } else {
            $error = $result['error'] ?? 'Đăng ký thất bại.';
            if (isset($result['errors'])) {
                $error = implode('<br>', $result['errors']);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - XEDIC Camera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="css/register.css" rel="stylesheet">
    
</head>
<body>
    <div class="register-card">
        <!-- Logo & Title -->
        <div class="logo">XEDIC</div>
        <div class="title">Đăng Ký</div>
        <div class="subtitle">Camera Chuyên Nghiệp Cho Creators</div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo $success; ?><br>
                <small>Chuyển đến đăng nhập trong <span id="countdown">3</span>s...</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="register.php" id="registerForm">
            <input type="hidden" name="action" value="register">

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user"></i> Họ và Tên
                </label>
                <input type="text" name="full_name" class="form-control" 
                       placeholder="Nguyễn Văn A" required
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>

            <!-- Username -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user-tag"></i> Tên Đăng Nhập
                </label>
                <input type="text" name="username" class="form-control" 
                       placeholder="nhập tên đăng nhập" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" name="email" class="form-control" 
                       placeholder="email@example.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Mật Khẩu
                </label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="ít nhất 6 ký tự" required>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Xác Nhận Mật Khẩu
                </label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-control" placeholder="nhập lại mật khẩu" required>
                    <button type="button" class="password-toggle" id="toggleConfirm">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Đăng Ký
            </button>
        </form>

        <!-- Divider -->
        <div class="divider"><span>Hoặc</span></div>

        <!-- Login Link -->
        <div class="login-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a>
        </div>

      
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password
        function setupToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            btn.addEventListener('click', () => {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                btn.innerHTML = type === 'password' 
                    ? '<i class="fas fa-eye"></i>' 
                    : '<i class="fas fa-eye-slash"></i>';
            });
        }
        setupToggle('togglePassword', 'password');
        setupToggle('toggleConfirm', 'confirm_password');

        // Countdown
        <?php if ($success): ?>
        let sec = 3;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            sec--;
            el.textContent = sec;
            if (sec <= 0) clearInterval(timer);
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>