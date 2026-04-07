<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/init.php';
require_admin();

$pdo = db();
$rows = $pdo->query('SELECT id, name, price, stock, category FROM products WHERE stock > 0 ORDER BY name')->fetchAll();
echo json_encode(['results' => $rows], JSON_THROW_ON_ERROR);
