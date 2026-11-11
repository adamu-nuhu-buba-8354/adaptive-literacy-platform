<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_id'], $_SESSION['admin_username']);
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
