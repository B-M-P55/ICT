<?php
// Start session to access logged-in user details 
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; 

// 1. DETERMINE LOGGED IN USER
$userID = $_SESSION['userID'] ?? 1; 

// Fetch User Profile & Address Info
$userQuery = $conn->prepare("SELECT first_name, last_name, address FROM tbl_user WHERE userID = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc() ?: ['first_name' => 'Guest', 'last_name' => '', 'address' => 'No Address Provided'];
$customerName = trim($userData['first_name'] . ' ' . $userData['last_name']);
$customerAddress = $userData['address'];

// 2. DETERMINE WHICH ORDER TO DISPLAY IN VOUCHER DETAILS
$selectedOrderID = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($selectedOrderID > 0) {
    $orderQuery = $conn->prepare("SELECT * FROM tbl_order WHERE order_ID = ? AND userID = ?");
    $orderQuery->bind_param("ii", $selectedOrderID, $userID);
} else {
    $orderQuery = $conn->prepare("SELECT * FROM tbl_order WHERE userID = ? ORDER BY order_ID DESC LIMIT 1");
    $orderQuery->bind_param("i", $userID);
}
$orderQuery->execute();
$orderResult = $orderQuery->get_result();
$currentOrder = $orderResult->fetch_assoc();

// Variables for current order display
$orderIDDisplay = "";
$orderDateFormatted = "";
$orderTimeFormatted = "";
$orderIDRaw = 0;

if ($currentOrder) {
    $orderIDRaw = $currentOrder['order_ID'];
    $orderIDDisplay = "#H0" . $orderIDRaw;
    $orderDateFormatted = date('F d, Y', strtotime($currentOrder['order_date']));
    $orderTimeFormatted = date('h:i A', strtotime($currentOrder['order_date']));
}

// 3. FETCH ORDER DETAILS (ITEMS PURCHASED) FOR THE SELECTED ORDER
$orderDetailsItems = [];
$deliveryFee = 500; // Fixed delivery fee

if ($orderIDRaw > 0) {
    $detailsStmt = $conn->prepare("SELECT quantity, price, productID FROM tbl_order_details WHERE orderID = ?");
    $detailsStmt->bind_param("i", $orderIDRaw);
    $detailsStmt->execute();
    $detailsResult = $detailsStmt->get_result();
    while ($item = $detailsResult->fetch_assoc()) {
        $orderDetailsItems[] = $item;
    }
}

// 4. PAYMENT INFORMATION MAPPING
$paymentData = [
    'status' => 'Paid',
    'amount' => $currentOrder['total_amount'] ?? 0,
    'method' => 'KBZ Pay',
    'date_time' => $currentOrder['order_date'] ?? date('Y-m-d H:i:s')
];

// 5. PAGINATION SETUP FOR ORDER HISTORY (5 ORDERS PER PAGE)
$resultsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $resultsPerPage;

// Count total orders for pagination links
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM tbl_order WHERE userID = ?");
$countStmt->bind_param("i", $userID);
$countStmt->execute();
$totalCountResult = $countStmt->get_result()->fetch_assoc();
$totalOrders = $totalCountResult['total'] ?? 0;
$totalPages = ceil($totalOrders / $resultsPerPage);

// Fetch paginated order history (Limited to 5 per page)
$historyStmt = $conn->prepare("SELECT order_ID, order_date, total_order, total_amount FROM tbl_order WHERE userID = ? ORDER BY order_ID DESC LIMIT ? OFFSET ?");
$historyStmt->bind_param("iii", $userID, $resultsPerPage, $offset);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vouchers - H2O2U</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/nav&footer.css">
    <link rel="stylesheet" href="../css/vouchers.css">
</head>
<body>

<!--Navbar-->
<nav class="navbar">
    <div class="logo-section">
        <img src="../img/logo.png" alt="H2O2U Logo">
        <h2>H2O2U</h2>
    </div>

    <ul class="nav-links">
        <li><a href="../homepage.html">Home</a></li>
        <li><a href="../homepage.html#products">Products</a></li>
        <li><a href="../homepage.html#reviews">Reviews</a></li>
        <li><a href="../contact.html">Contact Us</a></li>
    </ul>

    <div class="nav-buttons">
        <button class="order-btn">ORDER NOW</button>
        <a href="../user_pf.html" class="profile">
            <i class="fa-solid fa-user"></i>
        </a>
    </div>
</nav>

<!-- Vouchers -->
<section class="voucher-section">
    <div class="voucher-container">

        <!-- PAGE TITLE -->
        <div class="page-title">
            <div class="title-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <h1>Vouchers</h1>
                <p>Order & Payment History</p>
            </div>
        </div>

        <?php if ($currentOrder): ?>
        <!-- Order Detail -->
        <div class="voucher-card">
            <div class="card-title">
                <i class="fa-solid fa-clipboard-list"></i>
                <h2>Order Details</h2>
            </div>

            <div class="customer-order">
                <!-- CUSTOMER -->
                <div class="information-box">
                    <h3>Customer Information</h3>
                    <div class="information-item">
                        <i class="fa-regular fa-user"></i>
                        <div>
                            <p>Customer Name</p>
                            <strong><?php echo htmlspecialchars($customerName); ?></strong>
                        </div>
                    </div>
                    <div class="information-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <p>Address</p>
                            <strong><?php echo nl2br(htmlspecialchars($customerAddress)); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- ORDER -->
                <div class="information-box">
                    <h3>Order Information</h3>
                    <div class="information-item">
                        <i class="fa-solid fa-arrow-down-1-9"></i>
                        <div>
                            <p>Order No.</p>
                            <strong><?php echo htmlspecialchars($orderIDDisplay); ?></strong>
                        </div>
                    </div>
                    <div class="information-item">
                        <i class="fa-solid fa-calendar-days"></i>
                        <div>
                            <p>Order Date</p>
                            <strong><?php echo htmlspecialchars($orderDateFormatted); ?></strong>
                        </div>
                    </div>
                    <div class="information-item">
                        <i class="fa-regular fa-clock"></i>
                        <div>
                            <p>Order Time</p>
                            <strong><?php echo htmlspecialchars($orderTimeFormatted); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRODUCT TABLE -->
            <div class="order-table">
                <div class="table-header">
                    <span>Item / Product ID</span>
                    <span>Quantity</span>
                    <span>Unit Price</span>
                    <span>Delivery Fee</span>
                    <span>Total</span>
                </div>
                
                <?php if (!empty($orderDetailsItems)): ?>
                    <?php foreach ($orderDetailsItems as $rowItem): 
                        $itemTotal = ($rowItem['quantity'] * $rowItem['price']) + $deliveryFee;
                    ?>
                    <div class="table-row">
                        <span>
                            Product #<?php echo $rowItem['productID']; ?>
                        </span>
                        <span><?php echo $rowItem['quantity']; ?></span>
                        <span><?php echo number_format($rowItem['price']); ?> Ks</span>
                        <span><?php echo number_format($deliveryFee); ?> Ks</span>
                        <strong><?php echo number_format($itemTotal); ?> Ks</strong>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">No items found for this order.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment -->
        <div class="voucher-card payment-card">
            <div class="card-title">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h2>Payment Information</h2>
            </div>

            <div class="payment-information">
                <div>
                    <p>Payment Status</p>
                    <span class="paid"><?php echo htmlspecialchars($paymentData['status']); ?></span>
                </div>
                <div>
                    <p>Payment Amount</p>
                    <strong><?php echo number_format($currentOrder['total_amount']); ?> Ks</strong>
                </div>
                <div>
                    <p>Payment Method</p>
                    <strong><?php echo htmlspecialchars($paymentData['method']); ?></strong>
                </div>
                <div>
                    <p>Payment Date & Time</p>
                    <strong><?php echo date('F d, Y h:i A', strtotime($paymentData['date_time'])); ?></strong>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">No order vouchers found for your account.</div>
        <?php endif; ?>

        <!-- Order History -->
        <div class="voucher-card history-card">
            <div class="card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h2>Order History</h2>
            </div>

            <div class="history-table">
                <?php if ($historyResult && $historyResult->num_rows > 0): ?>
                    <?php while($hist = $historyResult->fetch_assoc()): ?>
                    <div class="history-row" style="cursor: pointer;" onclick="window.location.href='voucher.php?order_id=<?php echo $hist['order_ID']; ?>'">
                        <span>#H0<?php echo $hist['order_ID']; ?></span>
                        <span><?php echo date('F d, Y', strtotime($hist['order_date'])); ?></span>
                        <span><?php echo $hist['total_order']; ?> items</span>
                        <span><?php echo number_format($hist['total_amount']); ?> Ks</span>
                        <span class="paid">Paid</span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">No past order history available.</div>
                <?php endif; ?>
            </div>

            <!-- PAGINATION CONTROLS -->
            <?php if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-center mt-3">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="voucher.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

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
        <a href="homepage.html#products">Products</a>
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
        <a href="voucher.php">Vouchers</a>
        <a href="user_pf.html">User Profile</a>
    </div>

    <div class="copyright">
        © 2026 All Right Reserved
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>