
<?php
include 'db.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Delivery</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<!-- Side bar -->
<aside class="sidebar">
    <div class="logo-area">
        <img src="img/logo.png" class="logo" alt="Logo">
        <span class="logo-name">H2O2U</span>
    </div>

    <div class="admin-title">
        ADMIN PANEL
    </div>

    <div class="menu-section">
        <div class="menu-title">
            Vouchers
        </div>
        <a href="order-history.html" class="menu-item">
            • Order History
        </a>
        <a href="payment-transactions.html" class="menu-item">
            • Payment Transactions
        </a>
    </div>

    <div class="menu-section">
        <div class="menu-title">
            <h5>Deliveries</h5>
        </div>
        <a href="delivery-history.html" class="menu-item active">
            • Delivery History
        </a>
        <!-- <a href="#" class="menu-item">
            • Delivery Staff
        </a> -->
    </div>

    <a href="#" class="logout">
        Log Out
    </a>
</aside>

<!-- main -->
<main class="main-content">

    <!-- TOP BAR -->
    <header class="topbar">
        <h1 class="page-title">
            Delivery History
        </h1>

        <div class="top-actions">
            <div class="date-box">
                <i class="fa-regular fa-calendar"></i> July 31, 2026
            </div>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i> Search anything..
            </div>

            <div class="admin-box">
                <i class="fa-solid fa-shield-halved"></i> Admin
            </div>
        </div>
    </header>

    <!-- Summary -->
    <section class="summary">
        <div class="summary-card">
            <h6>Total Deliveries</h6>
            <h2>1849</h2>
            <p>All time</p>
            <span class="card-icon"><i class="fa-solid fa-truck"></i></span>
        </div>

        <div class="summary-card">
            <h6>Delivered</h6>
            <h2>1,004</h2>
            <p>All time</p>
            <span class="card-icon"><i class="fa-solid fa-box"></i></span>
        </div>

        <div class="summary-card">
            <h6>Cancelled</h6>
            <h2>95</h2>
            <p>All time</p>
            <span class="card-icon"><i class="fa-solid fa-xmark"></i></span>
        </div>

        <div class="summary-card">
            <h6>Available Address</h6>
            <h2>3 Townships</h2>
            <p>Yangon</p>
            <span class="card-icon"><i class="fa-solid fa-location-dot"></i></span>
        </div>
    </section>

    <!-- Table -->
    <section class="table-container">

        <div class="filter-area">
            <input
                type="text"
                id="deliverySearch"
                class="filter-input"
                placeholder="Search..."
            >

            <select class="status-select" id="statusFilter">
                <option>All Status</option>
                <option>Delivered</option>
                <option>Pending</option>
                <option>Cancelled</option>
            </select>
        </div>

        <table class="admin-table" style="text-align: center;">
            <thead>
                <tr>
                    <th>Delivery No.</th>
                    <th>Order No.</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Rider</th>
                    <th>Delivered on</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody style="text-align: center;">
                <?php
            // Execute query ONLY inside tbody
            $sql = "SELECT 
                        d.deliveryID,
                        d.orderID,
                        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
                        u.address,
                        drv.name AS driver_name,
                        drv.phone_number AS driver_phone,
                        DATE_FORMAT(d.date, '%M %e, %Y') AS delivery_date,
                        DATE_FORMAT(d.date, '%h:%i %p') AS delivery_time,
                        d.status
                    FROM tbl_delivery d
                    JOIN tbl_order o ON d.orderID = o.order_ID
                    JOIN tbl_user u ON o.userID = u.userID
                    JOIN tbl_driver drv ON d.driverID = drv.driverID
                    ORDER BY d.deliveryID ASC";

            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['deliveryID']); ?></td>
                        <td><?php echo htmlspecialchars($row['orderID']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['address'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['driver_name']); ?><br>
                            <?php echo htmlspecialchars($row['driver_phone']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['delivery_date']); ?><br>
                            <?php echo htmlspecialchars($row['delivery_time']); ?>
                        </td>
                        <td>
                            <span><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span>
                        </td>
                        <td>
                            <button class="edit-btn">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='8'>No records found.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <div class="table-footer">
            <span>
                Showing 3 of 12 entries
            </span>

            <div class="pagination">
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

    </section>

</main>

<script src="js/admin.js"></script>

</body>
</html>