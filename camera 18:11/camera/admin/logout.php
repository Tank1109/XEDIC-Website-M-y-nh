<?php
require_once '../auth/auth.php';

session_start();

$auth = new Auth();
$auth->logout();

header('Location: ../login.php');
exit;
?>
