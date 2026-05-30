<?php
// logout.php - Secure logout
require_once __DIR__ . '/auth.php';
logout();
header("Location: login.php");
exit;
?>
