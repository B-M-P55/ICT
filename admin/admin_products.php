<?php
declare(strict_types=1);

require_once __DIR__ . '/adminauth.php';
require_admin();
$db = database();

function product_token(): string
{
    start_session();
    $_SESSION['product_token'] ??= bin2hex(random_bytes(32));
    return $_SESSION['product_token'];
}

function verify_product_token(): void
{
    start_session();
    if (!hash_equals($_SESSION['product_token'] ?? '', $_POST['token'] ?? '')) {
        throw new RuntimeException('Your session expired. Please try again.');
    }
}

function save_product_image(array $upload): ?string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Upload a valid image no larger than 5 MB.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($upload['tmp_name']);
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');

    $folder = __DIR__ . '/../productImage';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) throw new RuntimeException('Product image folder could not be created.');
    $fileName = 'product_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $folder . '/' . $fileName)) throw new RuntimeException('The product image could not be saved.');
    return 'productImage/' . $fileName;
}

$message = '';
$error = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_product_token();
        $action = $_POST['action'] ?? '';

        if ($action === 'deactivate') {
            $db->prepare('UPDATE tbl_product SET is_active = 0 WHERE productID = ?')->execute([(int) $_POST['product_id']]);
            $message = 'Product hidden from customers.';
        } elseif ($action === 'activate') {
            $db->prepare('UPDATE tbl_product SET is_active = 1 WHERE productID = ?')->execute([(int) $_POST['product_id']]);
            $message = 'Product is visible to customers again.';
        } else {
            $name = trim($_POST['product_name'] ?? '');
            $size = trim($_POST['size'] ?? '');
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
            if ($name === '' || $size === '' || $price === false || $price < 0 || $stock === false || $stock < 0) {
                throw new RuntimeException('Enter a name, size, non-negative price, and non-negative stock quantity.');
            }
            $imagePath = save_product_image($_FILES['image'] ?? []);

            if ($action === 'create') {
                $db->beginTransaction();
                $db->prepare('INSERT INTO tbl_product (product_name, size, price, stock, image_path) VALUES (?, ?, ?, ?, ?)')->execute([$name, $size, $price, $stock, $imagePath]);
                $productId = (int) $db->lastInsertId();
                if ($stock > 0) {
                    $db->prepare("INSERT INTO tbl_stock_transaction (transaction_type, quantity, reason, reference_no, adminID, productID) VALUES ('IN', ?, 'Opening stock for new product', ?, ?, ?)")->execute([$stock, 'PRODUCT-' . $productId, current_admin_id(), $productId]);
                }
                $db->commit();
                $message = 'Product created.';
            } elseif ($action === 'update') {
                $productId = (int) $_POST['product_id'];
                $sql = 'UPDATE tbl_product SET product_name = ?, size = ?, price = ?';
                $values = [$name, $size, $price];
                if ($imagePath !== null) { $sql .= ', image_path = ?'; $values[] = $imagePath; }
                $sql .= ' WHERE productID = ?';
                $values[] = $productId;
                $db->prepare($sql)->execute($values);
                $message = 'Product details updated. Use Stock Management for stock changes.';
            }
        }
    }
} catch (Throwable $exception) {
    if ($db->inTransaction()) $db->rollBack();
    $error = $exception->getMessage();
}

$products = $db->query("SELECT productID, product_name, size, price, stock, is_active, COALESCE(image_path, 'images/water_one.jpg') AS image_path FROM tbl_product ORDER BY is_active DESC, productID DESC")->fetchAll();
$activePage = 'products';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | H2O2U Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-dashboard.css">
</head>
<body class="admin-app">
<?php include __DIR__ . '/admin_sidebar.php'; ?>
<main class="container">
    <header class="dashboard-top">
        <div>
            <p class="eyebrow">Catalogue</p>
            <h1>Product management</h1>
            <p>Keep your water catalogue clear, up to date, and ready to sell.</p>
        </div>
        <div class="admin-user"><i class="fa-solid fa-user-shield"></i><span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span></div>
    </header>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="admin-panel product-create mb-4">
        <div class="section-head">
            <div><h2>Add a new water product</h2><p>Its opening quantity is saved automatically in stock history.</p></div>
            <span class="panel-icon"><i class="fa-solid fa-circle-plus"></i></span>
        </div>
        <form method="post" enctype="multipart/form-data" class="product-form-grid">
            <input type="hidden" name="token" value="<?= product_token() ?>">
            <input type="hidden" name="action" value="create">
            <label><span>Product name</span><input class="form-control" name="product_name" placeholder="e.g. Purified Drinking Water" required></label>
            <label><span>Size</span><input class="form-control" name="size" placeholder="e.g. 20 L" required></label>
            <label><span>Price (Ks)</span><input class="form-control" name="price" type="number" min="0" step="0.01" placeholder="0.00" required></label>
            <label><span>Opening stock</span><input class="form-control" name="stock" type="number" min="0" placeholder="0" required></label>
            <label class="image-picker"><span>Product image</span><input class="form-control" name="image" type="file" accept="image/jpeg,image/png,image/webp" required></label>
            <button class="btn btn-primary product-submit"><i class="fa-solid fa-plus"></i> Add product</button>
        </form>
    </section>

    <section class="admin-panel catalogue-panel">
        <div class="section-head">
            <div><h2>Your products</h2><p><?= count($products) ?> saved product<?= count($products) === 1 ? '' : 's' ?> · hidden products remain safely in order history.</p></div>
            <a href="products.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> Customer view</a>
        </div>
        <div class="catalogue-grid">
            <?php foreach ($products as $product): ?>
                <article class="catalogue-card <?= $product['is_active'] ? '' : 'is-hidden' ?>">
                    <div class="catalogue-image">
                        <?php if ($product['image_path']): ?>
                            <img src="../<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                        <?php else: ?>
                            <i class="fa-solid fa-bottle-water"></i>
                        <?php endif; ?>
                        <span class="visibility-badge <?= $product['is_active'] ? 'visible' : 'hidden' ?>"><?= $product['is_active'] ? 'Visible' : 'Hidden' ?></span>
                    </div>
                    <div class="catalogue-content">
                        <div class="catalogue-title"><div><h3><?= htmlspecialchars($product['product_name']) ?></h3><p><?= htmlspecialchars($product['size']) ?></p></div><span class="stock-count <?= (int)$product['stock'] <= 10 ? 'low' : '' ?>"><?= (int)$product['stock'] ?> in stock</span></div>
                        <div class="catalogue-price"><?= number_format((float)$product['price'], 2) ?> <small>Ks</small></div>
                        <div class="catalogue-actions">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="var el=document.getElementById('edit<?= (int)$product['productID'] ?>');el.style.display=el.style.display==='none'?'block':'none';"><i class="fa-solid fa-pen"></i> Edit</button>
                            <?php if ($product['is_active']): ?>
                                <form method="post"><input type="hidden" name="token" value="<?= product_token() ?>"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="product_id" value="<?= (int)$product['productID'] ?>"><button class="btn btn-light btn-sm text-danger" onclick="return confirm('Hide this product from customers?')"><i class="fa-solid fa-eye-slash"></i> Hide</button></form>
                            <?php else: ?>
                                <form method="post"><input type="hidden" name="token" value="<?= product_token() ?>"><input type="hidden" name="action" value="activate"><input type="hidden" name="product_id" value="<?= (int)$product['productID'] ?>"><button class="btn btn-success btn-sm"><i class="fa-solid fa-eye"></i> Unhide</button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="edit<?= (int)$product['productID'] ?>" style="display:none; padding:15px; border-top:1px solid #e5edf3; background:#f9fbfd;">
                        <form method="post" enctype="multipart/form-data" class="edit-grid">
                            <input type="hidden" name="token" value="<?= product_token() ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= (int)$product['productID'] ?>"><input type="hidden" name="stock" value="<?= (int)$product['stock'] ?>">
                            <label><span>Name</span><input class="form-control" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required></label>
                            <label><span>Size</span><input class="form-control" name="size" value="<?= htmlspecialchars($product['size']) ?>" required></label>
                            <label><span>Price</span><input class="form-control" name="price" type="number" min="0" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required></label>
                            <label><span>Replace image</span><input class="form-control" name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                            <button class="btn btn-primary">Save changes</button>
                        </form>
                        <small><i class="fa-solid fa-circle-info"></i> Change stock quantity from Stock Management so every change is recorded.</small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
