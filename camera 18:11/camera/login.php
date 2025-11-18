<?php
require_once 'config/database.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Get redirect URL safely
$redirect = isset($_GET['redirect']) ? filter_var($_GET['redirect'], FILTER_SANITIZE_URL) : 'index.php';
if (empty($redirect) || strpos($redirect, 'http') !== false) {
    $redirect = 'index.php';
}

if (isLoggedIn()) {
    // Already logged in, redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: ' . $redirect);
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        if($_POST['action'] === 'login') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if(empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ thông tin';
            } else {
                if($auth->login($username, $password)) {
                    // Login successful, redirect based on role
                    if ($_SESSION['role'] === 'admin') {
                        // Admin users go to admin dashboard
                        header('Location: admin/index.php');
                    } else {
                        // Regular customers go to redirect or home
                        header('Location: ' . $redirect);
                    }
                    exit();
                } else {
                    $error = 'Thông tin đăng nhập không chính xác';
                }
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
    <title>Đăng Nhập - XEDIC Camera</title>
        <link href="css/login.css" rel="stylesheet">

    <!-- Bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">    
    <!-- Font chữ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">XEDIC</div>
                <h1 class="login-title">Đăng Nhập</h1>
                <p class="login-subtitle">Camera Chuyên Nghiệp Cho Creators</p>
            </div>

            <!-- Thông báo -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- Form đăng nhập -->
            <form method="POST" action="login.php">
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Tên Đăng Nhập
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Nhập tên đăng nhập"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <!-- Mật khẩu -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Mật Khẩu
                    </label>
                    <div class="password-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Nhập mật khẩu"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- GHi nhớ cho lần đăng nhập sau và Quên mật khẩu -->
                <div class="remember-forgot">
                    <div class="remember-checkbox">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <label for="remember">Ghi nhớ tôi</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-link">Quên mật khẩu?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn" name="action" value="login">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                </button>
            </form>
            <!-- Divider -->
            <div class="divider">
                <span>Hoặc</span>
            </div>

            <!-- Social Sign In Buttons -->
            <div class="social-signin-container">
                <!-- Google Sign In Button -->
                <button type="button" class="google-signin-btn" id="googleSignInBtn">
                    <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#4285F4" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#FBBC05" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </button>

                <!-- Facebook Sign In Button -->
                <button type="button" class="facebook-signin-btn" id="facebookSignInBtn">
                    <svg class="facebook-icon" viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    
                </button>
            </div>

            <!-- Signup Link -->
            <div class="signup-link">
                <a href="register.php" onclick="event.preventDefault(); window.location.href='register.php';">Đăng ký tại đây</a>
            </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Firebase SDK -->
    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js";
        import { getAuth, signInWithPopup, GoogleAuthProvider, FacebookAuthProvider, setPersistence, browserLocalPersistence } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-auth.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyAoPyDna81R3CSgKr845rpmQo2WFDY6vZE",
            authDomain: "xedic-6ef10.firebaseapp.com",
            projectId: "xedic-6ef10",
            storageBucket: "xedic-6ef10.firebasestorage.app",
            messagingSenderId: "643547721626",
            appId: "1:643547721626:web:01ff35c4139cb28efc1423",
            measurementId: "G-K1EC98H7NZ"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth();
        
        // Set persistence to LOCAL to prevent sessionStorage issues
        setPersistence(auth, browserLocalPersistence)
            .catch(error => console.error('Persistence error:', error));
        
        const googleProvider = new GoogleAuthProvider();
        const facebookProvider = new FacebookAuthProvider();
        
        // Set Facebook permissions
        facebookProvider.addScope('public_profile');
        facebookProvider.addScope('email');
        
        // Set popup properties
        googleProvider.setCustomParameters({
            'prompt': 'consent'
        });
        
        facebookProvider.setCustomParameters({
            'display': 'popup'
        });

        // Google Sign In Button Handler
        document.getElementById('googleSignInBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            await handleSocialSignIn(this, googleProvider, 'google');
        });

        // Facebook Sign In Button Handler
        document.getElementById('facebookSignInBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            await handleSocialSignIn(this, facebookProvider, 'facebook');
        });

        // Handle social sign in
        async function handleSocialSignIn(btn, provider, providerType) {
            const originalText = btn.innerHTML;
            
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                
                // Ensure auth persistence is set
                await setPersistence(auth, browserLocalPersistence);
                
                // Sign in with provider popup
                const result = await signInWithPopup(auth, provider);
                
                // Get user info from Firebase
                const user = result.user;
                let token = null;
                let credential = null;

                if (providerType === 'google') {
                    credential = GoogleAuthProvider.credentialFromResult(result);
                    token = credential.accessToken;
                } else if (providerType === 'facebook') {
                    credential = FacebookAuthProvider.credentialFromResult(result);
                    token = credential.accessToken;
                }
                
                // Verify user data
                if (!user.uid || !user.email) {
                    throw new Error('Không thể lấy thông tin người dùng từ ' + (providerType === 'google' ? 'Google' : 'Facebook'));
                }
                
                // Send to backend for session creation
                const apiEndpoint = providerType === 'google' ? 'api/google-login.php' : 'api/facebook-login.php';
                
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        uid: user.uid,
                        email: user.email,
                        displayName: user.displayName || '',
                        photoURL: user.photoURL || '',
                        token: token || '',
                        providerId: user.providerData[0]?.providerId || providerType
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Lỗi server: ' + response.status);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Sign out from Firebase after successful backend login
                    await auth.signOut();
                    
                    // Redirect based on role
                    if (data.role === 'admin') {
                        window.location.href = 'admin/index.php';
                    } else {
                        const redirect = new URLSearchParams(window.location.search).get('redirect') || 'index.php';
                        window.location.href = redirect;
                    }
                } else {
                    showError(data.message || 'Đăng nhập thất bại');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error(`${providerType} Sign In Error:`, error);
                let errorMessage = `Đăng nhập ${providerType === 'google' ? 'Google' : 'Facebook'} thất bại`;
                
                if (error.code === 'auth/popup-closed-by-user') {
                    errorMessage = 'Bạn đã đóng cửa sổ đăng nhập';
                } else if (error.code === 'auth/popup-blocked') {
                    errorMessage = 'Cửa sổ đăng nhập bị chặn. Vui lòng kiểm tra cài đặt popup';
                } else if (error.code === 'auth/account-exists-with-different-credential') {
                    errorMessage = 'Tài khoản này đã được đăng ký với phương thức khác. Vui lòng đăng nhập bằng email hoặc phương thức khác.';
                } else if (error.code === 'auth/network-request-failed') {
                    errorMessage = 'Lỗi kết nối mạng. Vui lòng kiểm tra kết nối internet';
                } else if (error.message && error.message.includes('missing initial state')) {
                    errorMessage = 'Lỗi phiên làm việc. Vui lòng thử lại hoặc xóa cache trình duyệt';
                }
                
                showError(errorMessage);
                
                // Try to sign out from Firebase
                try {
                    await auth.signOut();
                } catch (signOutError) {
                    console.error('Sign out error:', signOutError);
                }
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Show error function
        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-error';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>${message}</span>
            `;
            
            const loginCard = document.querySelector('.login-card');
            const firstAlert = document.querySelector('.alert');
            
            if (firstAlert) {
                firstAlert.replaceWith(alertDiv);
            } else {
                loginCard.insertBefore(alertDiv, loginCard.querySelector('.login-header').nextElementSibling);
            }
            
            setTimeout(() => {
                alertDiv.style.animation = 'slideUp 0.3s ease forwards';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }
        
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        if (togglePassword && passwordField) {
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Update icon
                this.innerHTML = type === 'password' 
                    ? '<i class="fas fa-eye"></i>' 
                    : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();

                if (!username) {
                    e.preventDefault();
                    showError('Vui lòng nhập tên đăng nhập');
                    return;
                }

                if (!password) {
                    e.preventDefault();
                    showError('Vui lòng nhập mật khẩu');
                    return;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    showError('Mật khẩu phải có ít nhất 6 ký tự');
                    return;
                }
            });
        }

        // Hiển thị và ẩn thông báo sau 5s
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>