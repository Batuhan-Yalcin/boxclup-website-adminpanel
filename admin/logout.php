<?php
require_once '../config.php';

// Çıkış logunu kaydet
if (isAdmin()) {
    logAdminLogout();
}

session_destroy();
header('Location: login.php');
exit;
?>

