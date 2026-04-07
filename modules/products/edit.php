<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/layout_init.php';

$id = (int) ($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$st->execute([$id]);
$row = $st->fetch();
if (!$row) {
    flash_set('error', 'Product not found.');
    redirect('/modules/products/list.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $lowTh = max(0, (int) ($_POST['low_stock_threshold'] ?? 5));
    $category = trim((string) ($_POST['category'] ?? ''));
    if ($name === '') {
        flash_set('error', 'Name is required.');
    } elseif ($price < 0 || $stock < 0) {
        flash_set('error', 'Price and stock must be zero or positive.');
    } else {
        $pdo->prepare('UPDATE products SET name=?, price=?, stock=?, low_stock_threshold=?, category=? WHERE id=?')
            ->execute([$name, $price, $stock, $lowTh, $category ?: null, $id]);
        flash_set('success', 'Product updated.');
        redirect('/modules/products/list.php');
    }
}

$pageTitle = 'Edit product';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';
?>
<div class="mb-3">
    <a href="<?= e(BASE_URL) ?>/modules/products/list.php" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<h1 class="h3 mb-3">Edit product</h1>
<div class="card vk-card" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" data-loading>
            <div class="mb-3">
                <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                <input class="form-control" id="name" name="name" required maxlength="255" value="<?= e($_POST['name'] ?? $row['name']) ?>">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="price">Price</label>
                    <input class="form-control" type="number" step="0.01" min="0" id="price" name="price" required value="<?= e((string) ($_POST['price'] ?? $row['price'])) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="stock">Stock</label>
                    <input class="form-control" type="number" min="0" id="stock" name="stock" required value="<?= e((string) ($_POST['stock'] ?? $row['stock'])) ?>">
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label" for="low_stock_threshold">Low stock alert at or below</label>
                <input class="form-control" type="number" min="0" id="low_stock_threshold" name="low_stock_threshold" value="<?= e((string) ($_POST['low_stock_threshold'] ?? ($row['low_stock_threshold'] ?? 5))) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="category">Category</label>
                <input class="form-control" id="category" name="category" maxlength="128" value="<?= e($_POST['category'] ?? ($row['category'] ?? '')) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
