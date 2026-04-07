<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/layout_init.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0 || !db_table_exists($pdo, 'web_portfolio_posts')) {
    redirect('/modules/portfolio/list.php');
}
$pdo->prepare('DELETE FROM web_portfolio_posts WHERE id = ?')->execute([$id]);
flash_set('success', 'Post removed.');
redirect('/modules/portfolio/list.php');
