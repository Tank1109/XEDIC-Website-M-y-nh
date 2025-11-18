<?php
require_once 'config/database.php';
require_once 'auth/auth.php';
$auth = new Auth();
// Gọi hàm logout từ class Auth
$auth->logout();
header('Location: index.php');
exit;
?>