<?php
declare(strict_types=1);

require_once __DIR__ . '/adminauth.php';
require_admin();
$db = database(); // Returns PDO instance
start_session();

$_SESSION['stock_token'] ??= bin2hex(random_bytes(32));
$message = '';
$error = '';

// Handle Status Update Post Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $deliveryID = intval($_POST['delivery_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    
    $allowedStatuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
    if ($deliveryID > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $updateStmt = $db->prepare("UPDATE tbl_delivery SET status = ? WHERE deliveryID = ?");
        if ($updateStmt->execute([$newStatus, $deliveryID])) {
            $message = "Delivery #{$deliveryID} status updated to '{$newStatus}'.";
        } else {
            $error = "Failed to update delivery status.";
        }
    }
}

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
    $stmt = $db->prepare($countSql);
    $searchTerm = "%" . $search . "%";
    $stmt->execute([$searchTerm]);
    $total_deliveries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    $countSql = "SELECT COUNT(*) AS total FROM tbl_delivery";
    $total_deliveries = (int)$db->query($countSql)->fetch(PDO::FETCH_ASSOC)['total'];
}

$totalPages = max(1, (int)ceil($total_deliveries / $resultsPerPage));

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
            LEFT JOIN tbl_driver drv ON d.driverID = drv.driverID";

if (!empty($search)) {
    $sql = $baseSelect . " WHERE CONCAT_WS(' ', u.first_name, u.last_name) LIKE ? ORDER BY d.deliveryID ASC LIMIT ? OFFSET ?";
    $stmt = $db->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
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

// 4. Summary Counters
$row_delivered = $db->query("SELECT COUNT(*) AS count FROM tbl_delivery WHERE status = 'Delivered'")->fetch(PDO::FETCH_ASSOC);
$total_delivered = (int)($row_delivered['count'] ?? 0);
$activePage = 'deliveries';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deliveries | H2O2U Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-dashboard.css">
</head>
<body class="admin-app">

<?php 
$sidebar_file = __DIR__ . '/admin_sidebar.php';
if (!file_exists($sidebar_file)) {
    $sidebar_file = dirname(__DIR__) . '/admin_sidebar.php';
}
if (file_exists($sidebar_file)) {
    include $sidebar_file;
}
?>

<main class="container py-4">
    <header class="dashboard-top mb-4">
        <div>
            <p class="eyebrow">Deliveries</p>
            <h1>Delivery history</h1>
            <p>Track customer orders, rider assignments, and fulfillment status across all regions.</p>
        </div>
        <div class="admin-user"><i class="fa-solid fa-user-shield"></i> <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span></div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <section class="admin-panel mb-4">
        <div class="row text-center g-3">
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <p class="text-muted mb-1">Total Deliveries</p>
                    <h3 class="fw-bold mb-0"><?= number_format((float)$total_deliveries) ?></h3>
                    <small class="text-secondary">All time</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <p class="text-muted mb-1">Delivered</p>
                    <h3 class="fw-bold text-primary mb-0"><?= number_format((float)$total_delivered) ?></h3>
                    <small class="text-secondary">All time</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <p class="text-muted mb-1">Coverage Area</p>
                    <h3 class="fw-bold mb-0">3 Townships</h3>
                    <small class="text-secondary">Yangon Region</small>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-panel">
        <div class="section-head mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h2>Delivery records</h2>
                <p class="mb-0 text-muted">Showing <?= count($deliveries) ?> of <?= $total_deliveries ?> entries</p>
            </div>
            <form action="admin_delivery.php" method="GET" class="d-flex gap-2">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control form-control-sm" 
                    placeholder="Search customer name..." 
                    value="<?= htmlspecialchars($search) ?>"
                >
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if (!empty($search)): ?>
                    <a href="admin_delivery.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Delivery No.</th>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Rider</th>
                        <th>Delivered on</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($deliveries)): ?>
                        <?php foreach ($deliveries as $row): ?>
                            <tr>
                                <td class="fw-bold">#<?= htmlspecialchars((string)$row['deliveryID']) ?></td>
                                <td>#<?= htmlspecialchars((string)$row['orderID']) ?></td>
                                <td><?= htmlspecialchars((string)$row['customer_name']) ?></td>
                                <td class="text-start"><?= nl2br(htmlspecialchars((string)$row['address'])) ?></td>
                                <td>
                                    <div><?= htmlspecialchars((string)($row['driver_name'] ?? 'Unassigned')) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars((string)($row['driver_phone'] ?? '-')) ?></small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars((string)$row['delivery_date']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars((string)$row['delivery_time']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $statusClass = match(strtolower((string)$row['status'])) {
                                            'delivered' => 'bg-success',
                                            'in transit' => 'bg-info text-dark',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-warning text-dark'
                                        };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst((string)$row['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="admin_delivery.php<?= !empty($search) ? '?search=' . urlencode($search) . '&page=' . $page : '?page=' . $page ?>" method="POST" class="d-inline-flex gap-1">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="delivery_id" value="<?= $row['deliveryID'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="In Transit" <?= $row['status'] === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                            <option value="Delivered" <?= $row['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="py-4 text-muted">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                <nav class="pagination mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="admin_delivery.php?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-sm <?= ($page == $i) ? 'btn-primary active' : 'btn-outline-secondary' ?> mx-1">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>