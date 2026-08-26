<?php
declare(strict_types=1);

require_once __DIR__ . '/adminauth.php';
require_admin();
$db = database();
start_session();
$_SESSION['stock_token'] ??= bin2hex(random_bytes(32));
$message = '';
$error = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['stock_token'], $_POST['token'] ?? '')) throw new RuntimeException('Your session expired. Please try again.');
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $reason = trim($_POST['reason'] ?? '');
        if (!$productId || $quantity === false || $quantity === 0 || $reason === '') throw new RuntimeException('Choose a product, enter a quantity, and give a reason.');

        $change = match ($action) {
            'stock_in' => abs($quantity),
            'stock_out' => -abs($quantity),
            'adjustment' => $quantity,
            default => throw new RuntimeException('Invalid stock action.'),
        };
        $type = $action === 'stock_in' ? 'IN' : ($action === 'stock_out' ? 'OUT' : 'ADJUSTMENT');
        $db->beginTransaction();
        $update = $db->prepare('UPDATE tbl_product SET stock = stock + ? WHERE productID = ? AND stock + ? >= 0');
        $update->execute([$change, $productId, $change]);
        if ($update->rowCount() !== 1) throw new RuntimeException('Not enough stock, or the product does not exist.');
        $record = $db->prepare('INSERT INTO tbl_stock_transaction (transaction_type, quantity, reason, reference_no, adminID, productID) VALUES (?, ?, ?, ?, ?, ?)');
        $record->execute([$type, $action === 'adjustment' ? $change : abs($quantity), $reason, 'MANUAL-' . date('YmdHis'), current_admin_id(), $productId]);
        $db->commit();
        $message = 'Stock updated and transaction recorded.';
    }
} catch (Throwable $exception) {
    if ($db->inTransaction()) $db->rollBack();
    $error = $exception->getMessage();
}

$products = $db->query('SELECT productID, product_name, size, stock, image_path FROM tbl_product WHERE is_active = 1 ORDER BY product_name, size')->fetchAll();
$history = $db->query('SELECT st.*, p.product_name, p.size, a.name AS admin_name FROM tbl_stock_transaction st INNER JOIN tbl_product p ON p.productID = st.productID INNER JOIN tbl_admin a ON a.adminID = st.adminID ORDER BY st.transaction_date DESC, st.transactionID DESC LIMIT 100')->fetchAll();
$totalUnits = array_sum(array_map(fn($product) => (int)$product['stock'], $products));
$lowCount = count(array_filter($products, fn($product) => (int)$product['stock'] <= 10));
$productImages = ['images/water_one.jpg', 'images/water_two.jpg', 'images/Purified Water – Plastic Bottle.jpg'];
$activePage = 'stock';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Management | H2O2U Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-dashboard.css?v=20260819-stock-2">
</head>
<body class="admin-app">
<?php include __DIR__ . '/admin_sidebar.php'; ?>
<main class="container">
    <header class="dashboard-top">
        <div>
            <p class="eyebrow">Inventory control</p>
            <h1>Stock management</h1>
            <p>Track every bottle coming in or going out, all from one place.</p>
        </div>
        <div class="admin-user"><i class="fa-solid fa-boxes-stacked"></i><span><?= number_format($totalUnits) ?> units available</span></div>
    </header>

    <section class="stock-summary-grid">
        <article class="stock-summary"><span class="stock-summary-icon"><i class="fa-solid fa-bottle-water"></i></span><div><span>Active products</span><strong><?= count($products) ?></strong><small>Available to customers</small></div></article>
        <article class="stock-summary good"><span class="stock-summary-icon"><i class="fa-solid fa-layer-group"></i></span><div><span>Total inventory</span><strong><?= number_format($totalUnits) ?></strong><small>Units currently available</small></div></article>
        <article class="stock-summary warning"><span class="stock-summary-icon"><i class="fa-solid fa-triangle-exclamation"></i></span><div><span>Low stock</span><strong><?= $lowCount ?></strong><small>Products with 10 or fewer</small></div></article>
    </section>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="admin-panel stock-update-panel mb-4">
        <div class="section-head"><div><h2>Record a stock change</h2><p>Use Stock In for supplier deliveries, Stock Out for non-order removals, and Adjustment for corrections.</p></div><span class="panel-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></span></div>
        <form method="post" class="stock-form-grid">
            <input type="hidden" name="token" value="<?= $_SESSION['stock_token'] ?>">
            <label><span>Choose product</span><select class="form-select" name="product_id" required><option value="">Select a water product</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['productID'] ?>"><?= htmlspecialchars($product['product_name'] . ' (' . $product['size'] . ') — ' . $product['stock'] . ' available') ?></option><?php endforeach; ?></select></label>
            <label><span>Change type</span><select class="form-select" name="action"><option value="stock_in">Stock in</option><option value="stock_out">Stock out</option><option value="adjustment">Adjustment</option></select></label>
            <label><span>Quantity</span><input class="form-control" name="quantity" type="number" placeholder="e.g. 20" required></label>
            <label><span>Reason</span><input class="form-control" name="reason" placeholder="e.g. Supplier delivery" required></label>
            <button class="btn btn-primary stock-save"><i class="fa-solid fa-check"></i> Save record</button>
        </form>
        <p class="stock-hint"><i class="fa-solid fa-circle-info"></i> For a negative adjustment, enter a negative number such as <strong>-2</strong>. Paid customer orders reduce stock automatically.</p>
    </section>

    <section class="admin-panel stock-overview-panel mb-4">
        <div class="section-head"><div><h2>Current stock</h2><p>A quick view of products currently available for orders.</p></div><a class="btn btn-outline-primary btn-sm" href="admin_products.php"><i class="fa-solid fa-bottle-water"></i> Manage products</a></div>
        <div class="stock-product-grid">
            <?php foreach ($products as $index => $product): $image = $product['image_path'] ?: $productImages[$index % count($productImages)]; ?>
                <article class="stock-product-card">
                    <img src="../<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <div><h3><?= htmlspecialchars($product['product_name']) ?></h3><p><?= htmlspecialchars($product['size']) ?></p></div>
                    <span class="stock-amount <?= (int)$product['stock'] <= 10 ? 'low' : '' ?>"><strong><?= (int)$product['stock'] ?></strong> units</span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel stock-history-panel">
        <div class="section-head"><div><h2>Stock transaction history</h2><p>Every manual change and every paid-order stock deduction is recorded here.</p></div><span class="history-count">Latest 100 records</span></div>
        <div class="table-responsive"><table class="table stock-table"><thead><tr><th>Date & time</th><th>Product</th><th>Action</th><th>Quantity</th><th>Reason</th><th>Recorded by</th></tr></thead><tbody><?php if (!$history): ?><tr><td colspan="6" class="text-center text-muted py-4">No stock transactions yet.</td></tr><?php endif; ?><?php foreach ($history as $row): ?><tr><td><?= htmlspecialchars($row['transaction_date']) ?></td><td><strong><?= htmlspecialchars($row['product_name']) ?></strong><br><small><?= htmlspecialchars($row['size']) ?></small></td><td><span class="transaction-tag <?= strtolower($row['transaction_type']) ?>"><?= htmlspecialchars($row['transaction_type']) ?></span></td><td class="fw-bold"><?= (int)$row['quantity'] ?></td><td><?= htmlspecialchars($row['reason']) ?></td><td><?= htmlspecialchars($row['admin_name']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</main>
</body>
</html>
