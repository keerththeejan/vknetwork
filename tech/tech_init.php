<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';

if (empty($_SESSION['user_id'])) {
    flash_set('warning', 'Please sign in.');
    redirect('/login.php');
}
if (($_SESSION['user_role'] ?? 'admin') !== 'technician') {
    flash_set('warning', 'Technician access only.');
    redirect('/login.php');
}
$techStaffId = (int) ($_SESSION['technician_id'] ?? 0);
if ($techStaffId <= 0) {
    flash_set('error', 'Your user is not linked to a technician profile. Ask an admin to set technician on your account.');
    redirect('/login.php');
}
$pdo = db();
