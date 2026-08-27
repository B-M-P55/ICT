<?php
session_start();
require_once __DIR__ . '/adminauth.php'; 
$activePage = 'delivery';

// Fallback: If $conn isn't set, grab database() instance
if (!isset($conn) && function_exists('database')) {
    $db = database();
}

// ------------------------------------------------------------------
// 0. Handle Status Update Form Submissions
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliveryID'], $_POST['status'])) {
    $id = intval($_POST['deliveryID']);
    $status = strtolower(trim($_POST['status']));

    $allowed_statuses = ['pending', 'shipping', 'delivered'];

    if (in_array($status, $allowed_statuses, true)) {
        // Fixed: changed $pdo to $db and $deliveryID to $id
        $stmt = $db->prepare("UPDATE tbl_delivery SET status = ? WHERE deliveryID = ?");
        $stmt->execute([$status, $id]);

        // Redirect to preserve pagination/search query parameters
        $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        header("Location: admin_delivery.php" . $queryString);
        exit();
    }
}

// ------------------------------------------------------------------
// 1. Get Search Input & Pagination
// ------------------------------------------------------------------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$resultsPerPage = 5;
$offset = ($page - 1) * $resultsPerPage;

// ------------------------------------------------------------------
// 2. Count Total Records
// ------------------------------------------------------------------
if (!empty($search)) {
    $countSql = "SELECT COUNT(*) AS total FROM tbl_delivery d
                 JOIN tbl_order o ON d.orderID = o.order_ID
                 JOIN tbl_user u ON o.userID = u.userID
                 WHERE CONCAT_WS(' ', u.first_name, u.last_name) LIKE ?";
    $stmt = $db->prepare($countSql);
    $stmt->execute(["%" . $search . "%"]);
    $total_deliveries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    $countSql = "SELECT COUNT(*) AS total FROM tbl_delivery";
    $total_deliveries = $db->query($countSql)->fetch(PDO::FETCH_ASSOC)['total'];
}

$totalPages = max(1, ceil($total_deliveries / $resultsPerPage));

// ------------------------------------------------------------------
// 3. Main Query with LIMIT & OFFSET
// ------------------------------------------------------------------
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
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, "%" . $search . "%", PDO::PARAM_STR);
    $stmt->bindValue(2, $resultsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = $baseSelect . " ORDER BY d.deliveryID DESC LIMIT ? OFFSET ?";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $resultsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ------------------------------------------------------------------
// 4. Summary Counter (Case-insensitive matching for status)
// ------------------------------------------------------------------
$total_delivered = $db->query("SELECT COUNT(*) AS count FROM tbl_delivery WHERE LOWER(status) = 'delivered'")->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery History | H2O2U Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-dashboard.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body class="admin-app">

<?php 
if (file_exists(__DIR__ . '/admin_sidebar.php')) {
    include __DIR__ . '/admin_sidebar.php';
} else {
?>
    <aside class="sidebar">
        <div class="logo-area">
            <img src="../img/logo.png" class="logo" alt="Logo">
            <span class="logo-name">H2O2U</span>
        </div>
        <div class="admin-title">ADMIN PANEL</div>
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
<?php } ?>

<main class="container">

    <header class="dashboard-top mb-4">
        <div>
            <p class="eyebrow">Deliveries</p>
            <h1>Delivery History</h1>
            <p>Track order dispatch records and driver performance.</p>
        </div>
        <div class="admin-user">
            <i class="fa-solid fa-user-shield"></i>
            <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
        </div>
    </header>

    <section class="summary">
        <div class="summary-card">
            <h6>
                Total Deliveries
            </h6>
            <h2 class="fw-bold mb-0"><?php echo number_format($total_deliveries); ?></h2>
            <p>
                All time
            </p>
            <span class="card-icon">
                <i class="fa-solid fa-truck"></i>
            </span>
        </div>

        <div class="summary-card">
            <h6>
                Delivered
            </h6>
            <h2 class="fw-bold mb-0"><?php echo number_format($total_delivered); ?></h2>
            <p>
                All time
            </p>
            <span class="card-icon">
                <i class="fa-solid fa-truck-fast"></i>
            </span>
        </div>
        <div class="summary-card">
            <h6>
                Avaliable Address
            </h6>
            <h2 class="fw-bold mb-0">Townships</h2>
            <p>
                Yangon
            </p>
            <span class="card-icon">
                <i class="fa-solid fa-location-dot"></i>
            </span>
        </div>
        
    </section>

    <section class="admin-panel">
        <div class="row mb-4 align-items-center p-3">
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

        <div class="table-responsive">
            <table class="table align-middle text-center">
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
                <tbody>
    <?php if (!empty($deliveries)): ?>
        <?php foreach ($deliveries as $row): ?>
            <?php $statusVal = strtolower($row['status']); ?>
            <tr>
                <td><?= htmlspecialchars($row['deliveryID']); ?></td>
                <td><?= htmlspecialchars($row['orderID']); ?></td>
                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                <td><?= nl2br(htmlspecialchars($row['address'])); ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['driver_name']); ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($row['driver_phone']); ?></small>
                </td>
                <td>
                    <?= htmlspecialchars($row['delivery_date']); ?><br>
                    <small class="text-muted"><?= htmlspecialchars($row['delivery_time']); ?></small>
                </td>
                <td>
                    <!-- Updated form action to post directly to admin_delivery.php -->
                    <form action="admin_delivery.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" method="POST" class="d-inline">
                        <input type="hidden" name="deliveryID" value="<?= htmlspecialchars($row['deliveryID']); ?>">
                        <select name="status" class="form-select form-select-sm fw-semibold 
                            <?php 
                                switch ($statusVal) {
                                    case 'delivered': echo 'text-success border-success bg-success-subtle'; break;
                                    case 'shipping':  echo 'text-primary border-primary bg-primary-subtle'; break;
                                    default:          echo 'text-warning border-warning bg-warning-subtle'; break;
                                }
                            ?>" 
                            onchange="this.form.submit()">
                            
                            <option value="pending" <?= $statusVal === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="shipping" <?= $statusVal === 'shipping' ? 'selected' : ''; ?>>Shipping</option>
                            <option value="delivered" <?= $statusVal === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            
                        </select>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="py-4 text-muted">No records found.</td></tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center m-2 p-2 border-top">
            <span class="text-muted m-0 p-2">
                Showing <?php echo !empty($deliveries) ? count($deliveries) : 0; ?> of <?php echo $total_deliveries; ?> entries
            </span>

            <div class="pagination m-0 p-2">
                <?php if ($totalPages > 1): ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="admin_delivery.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo ($page == $i) ? 'btn-primary active' : 'btn-outline-secondary'; ?> mx-1">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php else: ?>
                    <button class="btn btn-sm btn-primary active" disabled>1</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>