<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/database.php';

$isAdminView = isset($_GET['admin']);

$db = database();
$products = $db->query(
    "SELECT productID, product_name, size, price, stock,
            COALESCE(image_path, 'productImage/water_one.jpg') AS image_path
     FROM tbl_product
     WHERE is_active = 1
     ORDER BY productID DESC"
)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | H2O2U</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php if (!$isAdminView): ?>
    <link rel="stylesheet" href="css/nav&footer.css">
    <?php endif; ?>
    <link rel="stylesheet" href="customer-products.css">
</head>
<body class="customer-products">
    <?php if (!$isAdminView): ?>
    <nav class="navbar">
        <div class="logo-section">
            <img src="img/logo.png" alt="H2O2U Logo">
            <h2>H2O2U</h2>
        </div>
        <ul class="nav-links">
            <li><a href="homepage.html">Home</a></li>
            <li><a class="active" href="products.php">Products</a></li>
            <li><a href="homepage.html#reviews">Reviews</a></li>
            <li><a href="contact.html">Contact Us</a></li>
        </ul>
        <div class="nav-buttons">
            <button class="order-btn" onclick="window.location.href='user_orders.html'">ORDER NOW</button>
        </div>
    </nav>
    <?php endif; ?>

    <header class="customer-hero">
        <p>FRESH WATER, DELIVERED</p>
        <h1>Our water products</h1>
        <span>Choose the water size that fits your home or office. Every product below is live from our catalogue.</span>
    </header>

    <main class="customer-products-main">
        <div class="customer-products-title">
            <div>
                <h2>Available products</h2>
                <p>Fresh, clean water ready for delivery.</p>
            </div>
            <span class="customer-count"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> available</span>
        </div>
        <section class="customer-product-grid home-style-products">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-top-banner"></div>
                    <div class="product-img-wrapper">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    </div>
                    <div class="product-body">
                        <h4><?= htmlspecialchars($product['product_name']) ?></h4>
                        <p><?= htmlspecialchars($product['size']) ?> purified water, prepared for convenient delivery to your home or office.</p>
                        <div class="product-footer">
                            <span class="product-price">
                                <span class="arrow-icon">▶</span>
                                <?= number_format((float)$product['price'], 2) ?> Ks.
                            </span>
                            <?php if ((int)$product['stock'] > 0): ?>
                                <a class="btn-buy" href="user_orders.html">Order now</a>
                            <?php else: ?>
                                <button class="btn-buy" disabled>Out of stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <?php if (!$isAdminView): ?>
    <footer class="footer">
        <div class="footer-logo"><img src="img/logo.png" alt="H2O2U Logo"><h2>H2O2U</h2></div>
        <div class="footer-column"><h3>PRIVACY</h3><a href="#">Terms of use</a><a href="#">Privacy policy</a><a href="#">Cookies</a></div>
        <div class="footer-column"><h3>SERVICES</h3><a href="products.php">Products</a><a href="user_orders.html">Order</a><a href="#">Payment</a></div>
        <div class="footer-column"><h3>ABOUT US</h3><a href="contact.html">Contact</a><a href="homepage.html#reviews">Reviews</a><a href="#">Our story</a></div>
        <div class="footer-column"><h3>INFORMATION</h3><a href="user_delivery.html">Delivery History</a><a href="voucher.html">Vouchers</a><a href="user_pf.html">User Profile</a></div>
        <div class="copyright">&copy; 2026 All Right Reserved</div>
    </footer>
    <script src="js/navbar.js"></script>
    <?php endif; ?>
</body>
</html>
