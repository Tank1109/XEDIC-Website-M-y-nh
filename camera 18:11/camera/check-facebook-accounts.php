<?php
/**
 * Multi-Account Facebook Login Checker
 * Kiểm tra xem hệ thống hỗ trợ bao nhiêu tài khoản Facebook
 */

require_once 'config/database.php';

// Kiểm tra xem có database connection không
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("❌ Không thể kết nối database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Multi-Account Checker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            line-height: 1.6;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        
        .content h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #667eea;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #555;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #1565c0;
        }
        
        .success-box {
            background: #c8e6c9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #2e7d32;
        }
        
        .warning-box {
            background: #fff9c4;
            border-left: 4px solid #fbc02d;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #f57f17;
        }
        
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        
        .test-section {
            margin-top: 20px;
        }
        
        .test-button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .test-button:hover {
            background: #764ba2;
        }
        
        .test-button-secondary {
            background: #6c757d;
        }
        
        .test-button-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Facebook Multi-Account Checker</h1>
            <p>Kiểm tra hệ thống hỗ trợ đăng nhập bằng bao nhiêu tài khoản Facebook</p>
        </div>
        
        <?php
        try {
            // Get statistics
            $stmt = $pdo->query("SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN facebook_uid IS NOT NULL THEN 1 ELSE 0 END) as facebook_users,
                SUM(CASE WHEN google_uid IS NOT NULL THEN 1 ELSE 0 END) as google_users
            FROM users WHERE is_active = 1");
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_users = $stats['total_users'] ?? 0;
            $facebook_users = $stats['facebook_users'] ?? 0;
            $google_users = $stats['google_users'] ?? 0;
            
            echo '<div class="stats">
                <div class="stat-card">
                    <div class="stat-label">👥 Total Users</div>
                    <div class="stat-value">' . $total_users . '</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">📘 Facebook Users</div>
                    <div class="stat-value">' . $facebook_users . '</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🔵 Google Users</div>
                    <div class="stat-value">' . $google_users . '</div>
                </div>
            </div>';
            
        } catch (Exception $e) {
            echo '<div class="warning-box">❌ Error: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <div class="content">
            <h2>✅ Kiểm Tra Hỗ Trợ Multi-Account</h2>
            
            <?php
            try {
                // Check database column
                $stmt = $pdo->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $facebook_uid_exists = false;
                
                foreach ($columns as $column) {
                    if ($column['Field'] === 'facebook_uid') {
                        $facebook_uid_exists = true;
                        break;
                    }
                }
                
                if ($facebook_uid_exists) {
                    echo '<div class="success-box">✓ Cột <code>facebook_uid</code> tồn tại trong database</div>';
                } else {
                    echo '<div class="warning-box">⚠️ Cột <code>facebook_uid</code> không tồn tại. Chạy migration để thêm cột.</div>';
                }
                
                // Get Facebook users
                $stmt = $pdo->query("SELECT 
                    id, username, email, facebook_uid, created_at 
                FROM users 
                WHERE facebook_uid IS NOT NULL 
                AND is_active = 1
                ORDER BY created_at DESC");
                
                $facebook_logins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<h3 style="margin-top: 30px; margin-bottom: 15px;">📘 Danh Sách Users Đăng Nhập Facebook:</h3>';
                
                if (count($facebook_logins) > 0) {
                    echo '<table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Facebook UID</th>
                                <th>Ngày Tạo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
                    
                    foreach ($facebook_logins as $i => $user) {
                        echo '<tr>
                            <td>' . ($i + 1) . '</td>
                            <td><strong>' . htmlspecialchars($user['username']) . '</strong></td>
                            <td>' . htmlspecialchars($user['email']) . '</td>
                            <td><code>' . substr($user['facebook_uid'], 0, 10) . '...</code></td>
                            <td>' . date('d/m/Y H:i', strtotime($user['created_at'])) . '</td>
                            <td><span class="badge badge-success">✓ Active</span></td>
                        </tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<div class="empty">
                        <p>Chưa có users nào đăng nhập bằng Facebook</p>
                        <p style="margin-top: 10px; font-size: 0.9rem; color: #999;">Hãy thử <a href="login.php" style="color: #667eea;">đăng nhập bằng Facebook</a></p>
                    </div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="warning-box">❌ Error: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
        
        <div class="content">
            <h2>🧪 Test Multi-Account</h2>
            
            <div class="info-box">
                <strong>💡 Hướng Dẫn:</strong>
                <ol style="margin: 10px 0 0 20px;">
                    <li>Mở nhiều Private Window</li>
                    <li>Mỗi window đăng nhập Facebook account khác nhau</li>
                    <li>Kiểm tra xem có user khác nhau được tạo không</li>
                </ol>
            </div>
            
            <div class="test-section">
                <button class="test-button" onclick="openLoginWindow()">🔗 Mở Trang Đăng Nhập</button>
                <button class="test-button test-button-secondary" onclick="location.reload()">🔄 Refresh Page</button>
            </div>
        </div>
        
        <div class="content">
            <h2>📊 SQL Queries Hữu Ích</h2>
            
            <h3 style="margin-top: 20px; margin-bottom: 10px;">1. Xem tất cả users đăng nhập Facebook:</h3>
            <code>SELECT id, username, email, facebook_uid FROM users WHERE facebook_uid IS NOT NULL;</code>
            
            <h3 style="margin-top: 20px; margin-bottom: 10px;">2. Đếm số users Facebook:</h3>
            <code>SELECT COUNT(*) FROM users WHERE facebook_uid IS NOT NULL;</code>
            
            <h3 style="margin-top: 20px; margin-bottom: 10px;">3. Xem user cụ thể:</h3>
            <code>SELECT * FROM users WHERE email = 'your-email@gmail.com';</code>
            
            <h3 style="margin-top: 20px; margin-bottom: 10px;">4. Xem users đăng nhập cùng một email:</h3>
            <code>SELECT email, COUNT(*) as count FROM users GROUP BY email HAVING count > 1;</code>
        </div>
    </div>
    
    <script>
        function openLoginWindow() {
            window.open('login.php', '_blank', 'width=800,height=600');
        }
    </script>
</body>
</html>
