<?php
// Connect to the database
include 'db.php';

// 1. Fetch Summary Statistics dynamically
$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS count, SUM(total_amount) AS sales FROM tbl_order");
$summaryData = $totalOrdersQuery ? $totalOrdersQuery->fetch_assoc() : ['count' => 0, 'sales' => 0];

$pendingQuery = $conn->query("SELECT COUNT(*) AS count FROM tbl_order WHERE order_status = 'Pending'");
$pendingData = $pendingQuery ? $pendingQuery->fetch_assoc() : ['count' => 0];

$paidQuery = $conn->query("SELECT COUNT(*) AS count FROM tbl_order WHERE order_status = 'Paid'");
$paidData = $paidQuery ? $paidQuery->fetch_assoc() : ['count' => 0];

$cancelledQuery = $conn->query("SELECT COUNT(*) AS count FROM tbl_order WHERE order_status = 'Cancelled'");
$cancelledData = $cancelledQuery ? $cancelledQuery->fetch_assoc() : ['count' => 0];

// 2. Fetch Order History joined with User, Order Details, and Products
$ordersQuery = "
    SELECT 
        o.orderID, 
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name, 
        o.order_date, 
        o.order_status,
        p.product_name, 
        p.size, 
        od.price, 
        od.quantity, 
        (od.price * od.quantity) AS subtotal,
        o.total_amount
    FROM tbl_order o
    JOIN tbl_user u ON o.userID = u.userID
    JOIN tbl_order_details od ON o.orderID = od.orderID
    JOIN tbl_product p ON od.productID = p.productID
    ORDER BY o.order_date DESC
";
$result = $conn->query($ordersQuery);
$totalEntries = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order History - H2O2U</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <img src="img/logo.png" class="logo" alt="H2O2U Logo">
            <span class="logo-name">H2O2U</span>
        </div>
        <div class="admin-title">ADMIN PANEL</div>
        <div class="menu-section">
            <div class="menu-title">Vouchers</div>
            <a href="admin_order.php" class="menu-item active">• Order History</a>
            <a href="payment-transactions.php" class="menu-item">• Payment Transactions</a>
        </div>
        <div class="menu-section">
            <div class="menu-title">Deliveries</div>
            <a href="delivery-history.php" class="menu-item">• Delivery History</a>
            <a href="#" class="menu-item">• Delivery Staff</a>
        </div>
        <a href="#" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1 class="page-title">Order History</h1>
            <div class="top-actions">
                <div class="date-box">
                    <i class="fa-regular fa-calendar"></i> <?php echo date('F j, Y'); ?>
                </div>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i> Search anything..
                </div>
                <div class="admin-box">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </div>
            </div>
        </header>

        <!-- Dynamic Summary Cards -->
        <section class="summary order-summary">
            <div class="summary-card">
                <h6>Total Orders</h6>
                <h2><?php echo number_format($summaryData['count'] ?? 0); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-clipboard-list"></i></span>
            </div>
            <div class="summary-card">
                <h6>Total Sales</h6>
                <h2>Ks <?php echo number_format($summaryData['sales'] ?? 0); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-wallet"></i></span>
            </div>
            <div class="summary-card">
                <h6>Paid Orders</h6>
                <h2><?php echo number_format($paidData['count'] ?? 0); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="summary-card">
                <h6>Pending Orders</h6>
                <h2><?php echo number_format($pendingData['count'] ?? 0); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-hourglass-half"></i></span>
            </div>
            <div class="summary-card">
                <h6>Cancelled Orders</h6>
                <h2><?php echo number_format($cancelledData['count'] ?? 0); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-circle-xmark"></i></span>
            </div>
        </section>

        <!-- Main Orders Table Container -->
        <section class="table-container">
            <div class="filter-area">
                <input type="text" class="filter-input" placeholder="Search by Order No., Customer...">
                <select class="status-select">
                    <option>All Status</option>
                    <option>Pending</option>
                    <option>Paid</option>
                    <option>Cancelled</option>
                </select>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $formattedDate = date("F j, Y<br>g:i A", strtotime($row['order_date']));
                            echo '<tr>';
                            echo '<td>#H0' . htmlspecialchars($row['orderID']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                            echo '<td>' . $formattedDate . '</td>';
                            echo '<td>' . htmlspecialchars($row['product_name']) . '<br>(' . htmlspecialchars($row['size']) . ')</td>';
                            echo '<td>' . number_format($row['price']) . ' Ks</td>';
                            echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                            echo '<td>' . number_format($row['subtotal']) . ' Ks</td>';
                            echo '<td>
                                    <a href="edit-order.php?id=' . $row['orderID'] . '" class="edit-btn text-decoration-none">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center py-4 text-muted">No orders found in the database.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        
            <div class="table-footer">
                <span>
                    Showing <?php echo $totalEntries; ?> entries
                </span>

                <div class="pagination">
                    <button class="active">1</button>
                    <button><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </section>
    </main>

    <script src="js/admin.js"></script>
</body>
</html>