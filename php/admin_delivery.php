<?php
session_start();
include 'db_connect.php';

// 1. Get Search Input & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$resultsPerPage = 5;
$offset = ($page - 1) * $resultsPerPage;

// 2. Count Total Records (For Pagination & Display)
if (!empty($search)) {
    $countSql = "SELECT COUNT(*) AS total FROM tbl_delivery d 
                 JOIN tbl_order o ON d.orderID = o.order_ID 
                 JOIN tbl_user u ON o.userID = u.userID 
                 WHERE CONCAT_WS(' ', u.first_name, u.last_name) LIKE ?";
    $stmt = $conn->prepare($countSql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $total_deliveries = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $countSql = "SELECT COUNT(*) AS total FROM tbl_delivery";
    $total_deliveries = $conn->query($countSql)->fetch_assoc()['total'];
}

$totalPages = max(1, ceil($total_deliveries / $resultsPerPage));

// 3. Main Query with LIMIT & OFFSET
$baseSelect = "SELECT 
                d.deliveryID, d.orderID, u.address, d.status,
                CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
                drv.name AS driver_name, drv.phone_number AS driver_phone,
                DATE_FORMAT(d.date, '%M %e, %Y') AS delivery_date,
                DATE_FORMAT(d.date, '%h:%i %p') AS delivery_time
            FROM tbl_delivery d
            JOIN tbl_order o ON d.orderID = o.order_ID
            JOIN tbl_user u ON o.userID = u.userID
            JOIN tbl_driver drv ON d.driverID = drv.driverID";

if (!empty($search)) {
    $sql = $baseSelect . " WHERE CONCAT_WS(' ', u.first_name, u.last_name) LIKE ? ORDER BY d.deliveryID ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $searchTerm, $resultsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = $baseSelect . " ORDER BY d.deliveryID DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $resultsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// 4. Summary Counter (Delivered status)
$row_delivered = $conn->query("SELECT COUNT(*) AS count FROM tbl_delivery WHERE status = 'Delivered'")->fetch_assoc();
$total_delivered = $row_delivered['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Delivery</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="logo-area">
        <img src="../img/logo.png" class="logo" alt="Logo">
        <span class="logo-name">H2O2U</span>
    </div>

    <div class="admin-title">
        ADMIN PANEL
    </div>

    <div class="menu-section">
        <div class="menu-title">Vouchers</div>
        <a href="admin_order_history.php" class="menu-item">• Order History</a>
        <a href="admin_payment.php" class="menu-item">• Payment Transactions</a>
    </div>

    <div class="menu-section">
        <div class="menu-title">
            <h5>Deliveries</h5>
        </div>
        <a href="admin_delivery.php" class="menu-item active">• Delivery History</a>
    </div>

    <a href="#" class="logout">Log Out</a>
</aside>

<!-- Main -->
<main class="main-content">

    <!-- TOP BAR -->
    <header class="topbar">
        <h1 class="page-title">Delivery History</h1>

        <div class="top-actions">
            <div class="date-box">
                <i class="fa-regular fa-calendar"></i> <?php echo date('F j, Y'); ?>
            </div>

            <div class="admin-box">
                <i class="fa-solid fa-shield-halved"></i> Admin
            </div>
        </div>
    </header>

    <!-- Summary -->
    <section class="summary">
        <div class="summary-card">
            <p class="text-muted">Total Deliveries</p>
            <h3 class="fw-bold"><?php echo number_format($total_deliveries); ?></h3> 
            <small>All time</small>
        </div>

        <div class="summary-card">
            <p class="text-muted">Delivered</p>
            <h3 class="fw-bold text-primary"><?php echo number_format($total_delivered); ?></h3>
            <small>All time</small>
        </div>

        <div class="summary-card">
            <h6>Available Address</h6>
            <h2>3 Townships</h2>
            <p>Yangon</p>
            <span class="card-icon"><i class="fa-solid fa-location-dot"></i></span>
        </div>
    </section>

    <!-- Table Section -->
    <section class="table-container">

        <div class="row mb-3">
            <div class="col-md-6">
                <form action="admin_delivery.php" method="GET" class="d-flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search by customer name..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="admin_delivery.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
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
                </tr>
            </thead>

            <tbody style="text-align: center;">
                <?php
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
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='7'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="table-footer">
            <span>
                Showing <?php echo $result ? $result->num_rows : 0; ?> of <?php echo $total_deliveries; ?> entries
                </span>

                <!-- PAGINATION CONTROLS -->
                <div class="pagination">
                    <?php if ($totalPages > 1): ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="admin_delivery.php?page=<?php echo $i; ?>" class="btn btn-sm <?php echo ($page == $i) ? 'btn-primary active' : 'btn-outline-secondary'; ?> mx-1">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    <?php else: ?>
                        <button class="active">1</button>
                    <?php endif; ?>
                </div>
        </div>

    </section>

</main>

<script src="js/admin.js"></script>

</body>
</html>