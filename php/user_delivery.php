<?php
session_start();
include 'db_connect.php'; 

//user ID 
$userID = $_SESSION['userID'] ?? 1;

$sql = "SELECT 
            d.deliveryID,
            d.orderID,
            o.order_date,
            u.address,
            d.status,
            loc.estimated_arrival,
            DATE_FORMAT(d.date, '%M %e, %Y %h:%i %p') AS delivered_time,
            (SELECT SUM(quantity) FROM tbl_order_details WHERE orderID = o.order_ID) AS total_items,
            (SELECT GROUP_CONCAT(p.product_name SEPARATOR ', ') 
             FROM tbl_order_details od 
             JOIN tbl_product p ON od.productID = p.productID 
             WHERE od.orderID = o.order_ID) AS item_names
        FROM tbl_delivery d
        JOIN tbl_order o ON d.orderID = o.order_ID
        JOIN tbl_user u ON o.userID = u.userID
        LEFT JOIN tbl_location loc ON o.location_ID = loc.location_ID
        WHERE u.userID = ?
        ORDER BY d.deliveryID DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$deliveries = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DUser Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/nav&footer.css">
    <link rel="stylesheet" href="../css/delivery.css">
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo-section">
        <img src="../img/logo.png" alt="H2O2U Logo">
        <h2>H2O2U</h2>
    </div>

    <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="products.html">Products</a></li>
        <li><a href="#reviews">Reviews</a></li>
        <li><a href="contact.html">Contact Us</a></li>
    </ul>

    <div class="nav-buttons">
        <button class="order-btn">ORDER NOW</button>
        <a href="profile.html" class="profile">
            <i class="fa-solid fa-user"></i>
        </a>
    </div>
</nav>

<!-- Delivery History -->
<section class="delivery-section">
    <div class="delivery-container">

        <!-- Page Title -->
       <div class="page-title">
            <div class="title-icon">
                <i class="fa-solid fa-truck"></i>
            </div>
            <div>
                <h1>Delivery History</h1>
                <p>Track your deliveries</p>
            </div>
       </div>

       <?php if (!empty($deliveries)): ?>
          <?php foreach ($deliveries as $delivery): ?>
              <?php $statusLower = strtolower($delivery['status']); ?>
                 <div class="delivery-card">
                     <div class="delivery-left">
                        <h2>#H<?php echo str_pad($delivery['deliveryID'], 2, '0', STR_PAD_LEFT); ?></h2>
                        <div class="delivery-info">
                            <i class="fa-regular fa-user"></i>
                            <span><?php echo date('F j, Y g:i A', strtotime($delivery['order_date'])); ?></span>
                        </div>
                     <div class="delivery-info">
                       <i class="fa-solid fa-location-dot"></i>
                    <span>
                        <?php echo nl2br(htmlspecialchars($delivery['address'])); ?>
                    </span>
                </div>
            </div>

            <div class="delivery-middle">
    <!-- Display estimated arrival for all statuses -->
    <span class="estimated-badge">Estimated Arrival</span>
    <span class="estimated-time">
        within <?php echo !empty($delivery['estimated_arrival']) ? htmlspecialchars($delivery['estimated_arrival']) : '50 minutes'; ?>
    </span>
</div>

            <div class="delivery-right">
                <span class="status <?php echo $statusLower; ?>"><?php echo ucfirst($delivery['status']); ?></span>
                <p><?php echo $delivery['delivered_time']; ?></p>
                <p><?php echo $delivery['total_items'] ?? 0; ?> items</p>
                <p><?php echo htmlspecialchars($delivery['item_names'] ?? 'H2O2U Drinking Water'); ?></p>
                 </div>
              </div>
        
              <?php endforeach; ?>
          <?php else: ?>
          <p style="text-align: center; padding: 20px;">No delivery records found.</p>
       <?php endif; ?>

    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-logo">
        <img src="../img/logo.png" alt="H2O2U Logo">
        <h2>H2O2U</h2>
    </div>

    <div class="footer-column">
        <h3>PRIVACY</h3>
        <a href="#">Terms of use</a>
        <a href="#">Privacy policy</a>
        <a href="#">Cookies</a>
    </div>

    <div class="footer-column">
        <h3>SERVICES</h3>
        <a href="products.html">Products</a>
        <a href="#">Order</a>
        <a href="#">Payment</a>
    </div>

    <div class="footer-column">
        <h3>ABOUT US</h3>
        <a href="contact.html">Contact</a>
        <a href="#reviews">Reviews</a>
        <a href="#">Our story</a>
    </div>

    <div class="footer-column">
        <h3>INFORMATION</h3>
        <a href="delivery.html">Delivery History</a>
        <a href="vouchers.html">Vouchers</a>
        <a href="profile.html">User Profile</a>
    </div>

    <div class="copyright">
        © 2026 All Right Reserved
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>