<?php
/**
 * Clear Firebase Cache - Help file
 * Nếu bạn gặp lỗi "missing initial state", 
 * hãy mở file này trong trình duyệt để xóa cache
 */

// Set headers to clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa Cache Firebase</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .button:hover {
            background: #764ba2;
        }
        
        .button-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        
        .button-secondary:hover {
            background: #5a6268;
        }
        
        .steps {
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .steps li {
            margin-bottom: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Xóa Cache Firebase</h1>
        
        <div class="warning">
            <strong>⚠️ Nếu bạn gặp lỗi:</strong><br>
            "Unable to process request due to missing initial state"
        </div>
        
        <p>Điều này có thể xảy ra khi trình duyệt xóa sessionStorage hoặc cache.</p>
        
        <div class="steps">
            <strong>Hãy thử các cách sau:</strong>
            <ol>
                <li><strong>Xóa Storage cục bộ:</strong> Bấm nút bên dưới</li>
                <li><strong>Xóa Cookie & Cache:</strong> Ctrl+Shift+Delete (Windows) hoặc Cmd+Shift+Delete (Mac)</li>
                <li><strong>Mở Private/Incognito Window:</strong> Thử đăng nhập ở chế độ riêng tư</li>
                <li><strong>Thử browser khác:</strong> Chrome, Firefox, Safari, Edge</li>
            </ol>
        </div>
        
        <button class="button" onclick="clearFirebaseStorage()">✓ Xóa Storage</button>
        <button class="button button-secondary" onclick="window.location.href='login.php'">← Quay Lại Đăng Nhập</button>
        
        <div id="result" style="margin-top: 20px;"></div>
    </div>
    
    <script>
        function clearFirebaseStorage() {
            try {
                // Clear localStorage
                localStorage.clear();
                
                // Clear sessionStorage
                sessionStorage.clear();
                
                // Clear all Firebase-related storage
                const keysToDelete = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.includes('firebase')) {
                        keysToDelete.push(key);
                    }
                }
                
                keysToDelete.forEach(key => localStorage.removeItem(key));
                
                // Clear IndexedDB (Firebase uses this)
                if ('indexedDB' in window) {
                    const databases = ['firebase', 'firebaseLocalStorageDb', 'firebaseRemoteConfigDb'];
                    databases.forEach(dbName => {
                        const request = indexedDB.deleteDatabase(dbName);
                        request.onerror = () => console.error('Failed to delete', dbName);
                    });
                }
                
                // Show success message
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = `
                    <div class="success">
                        <strong>✓ Đã xóa cache thành công!</strong><br>
                        Hãy <a href="login.php" style="color: inherit; font-weight: bold;">quay lại trang đăng nhập</a> và thử lại.
                    </div>
                `;
                
                // Redirect after 3 seconds
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
                
            } catch (error) {
                console.error('Error clearing storage:', error);
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = `
                    <div class="warning">
                        <strong>Không thể xóa tất cả cache.</strong><br>
                        Vui lòng thử xóa cache của trình duyệt bằng tay (Ctrl+Shift+Delete)
                    </div>
                `;
            }
        }
        
        // Auto-check and display current storage info
        window.addEventListener('load', () => {
            const storageInfo = `
                localStorage: ${localStorage.length} items<br>
                sessionStorage: ${sessionStorage.length} items
            `;
            console.log('Storage info:', storageInfo);
        });
    </script>
</body>
</html>
