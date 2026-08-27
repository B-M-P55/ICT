<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your MySQLi connection from the parent directory
require_once __DIR__ . '/../php/db_connect.php';

$jsMessage = "";
$jsType = "";

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $deleteID = intval($_GET['delete_id']);
    
    $conn->begin_transaction();
    try {
        // Delete child items first, then delivery, then parent order using tbl_order
        $conn->query("DELETE FROM tbl_order_details WHERE orderID = $deleteID");
        $conn->query("DELETE FROM tbl_delivery WHERE orderID = $deleteID");
        $resOrder = $conn->query("DELETE FROM tbl_order WHERE order_ID = $deleteID");
        
        if (!$resOrder) {
            throw new Exception("Delete failed: " . $conn->error);
        }
        
        $conn->commit();
        $jsMessage = "Order #{$deleteID} deleted successfully!";
        $jsType = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $jsMessage = "Delete Error: " . $e->getMessage();
        $jsType = "error";
    }
}

// Fetch Summary (All time total orders count) using tbl_order
$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS total_order FROM tbl_order");
$summaryData = $totalOrdersQuery ? $totalOrdersQuery->fetch_assoc() : ['total_order' => 0];
$totalOrders = $summaryData['total_order'] ?? 0;

// Pagination Configuration
$resultsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $resultsPerPage;

$totalPages = ceil($totalOrders / $resultsPerPage);

// Fetch Order History using prepared statement for MySQLi
$orderQuery = $conn->prepare("
    SELECT o.order_ID, o.order_date, o.total_order, o.total_amount, o.userID, o.location_ID, 
           u.first_name, u.last_name 
    FROM tbl_order o
    JOIN tbl_user u ON o.userID = u.userID
    ORDER BY o.order_ID ASC
    LIMIT ?, ?
");
$orderQuery->bind_param("ii", $offset, $resultsPerPage);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();
$activePage = 'order_history';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | H2O2U Admin</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-dashboard.css">
    <link rel="stylesheet" href="../css/admin.css">
    
    <style>
        #jsToast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            min-width: 280px;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body class="admin-app">

    <!-- JS Toast Notification Container -->
    <div id="jsToast" class="alert alert-dismissible fade show" role="alert">
        <span id="jsToastMsg"></span>
        <button type="button" class="btn-close" onclick="hideToast()"></button>
    </div>

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
                <a href="admin_order_history.php" class="menu-item active">• Order History</a>
                <a href="admin_payment.php" class="menu-item">• Payment Transactions</a>
            </div>
            <div class="menu-section">
                <div class="menu-title">
                    <h5>Deliveries</h5>
                </div>
                <a href="admin_delivery.php" class="menu-item">• Delivery History</a>
            </div>
            <a href="#" class="logout">Log Out</a>
        </aside>
    <?php } ?>

    <main class="container">

        <header class="dashboard-top mb-4">
            <div>
                <p class="eyebrow">Vouchers</p>
                <h1>Order History</h1>
                <p>Track order records and customer purchases.</p>
            </div>
            <div class="admin-user">
                <i class="fa-solid fa-user-shield"></i>
                <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
            </div>
        </header>

        <section class="summary">
            <div class="summary-card">
                 <p class="text-muted mb-1">Total Orders</p>
                 <h2 class="fw-bold mb-0"><?php echo number_format($totalOrders); ?></h2>
                 <small class="text-secondary">All time</small>
            </div>
        </section>

        <section class="admin-panel">
            <div class="row mb-4 align-items-center p-3">
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <input
                            type="text"
                            class="form-control filter-input"
                            placeholder="Search by Order No., Customer..."
                        >
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Date & Time</th>
                            <th>Total Items (Qty)</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orderResult && $orderResult->num_rows > 0): ?>
                            <?php while($row = $orderResult->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['order_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo date('F d, Y', strtotime($row['order_date'])); ?><br><small class="text-muted"><?php echo date('h:i A', strtotime($row['order_date'])); ?></small></td>
                                    <td><?php echo $row['total_order']; ?></td>
                                    <td><?php echo number_format($row['total_amount']); ?> Ks</td>
                                    <td>
                                        <a href="admin_order_history.php?delete_id=<?php echo $row['order_ID']; ?>&page=<?php echo $page; ?>" class="delete-btn text-decoration-none d-inline-block px-2 py-1 bg-danger text-white rounded" onclick="return confirm('Are you sure you want to delete this order history?');">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 text-muted">No order history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center m-2 p-2 border-top">
                <span class="text-muted m-0 p-2">
                    Showing <?php echo $orderResult ? $orderResult->num_rows : 0; ?> of <?php echo $totalOrders; ?> entries
                </span>

                <div class="pagination m-0 p-2">
                    <?php if ($totalPages > 1): ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="admin_order_history.php?page=<?php echo $i; ?>" class="btn btn-sm <?php echo ($page == $i) ? 'btn-primary active' : 'btn-outline-secondary'; ?> mx-1">
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
    <script src="../js/admin.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('jsToast');
            const toastMsg = document.getElementById('jsToastMsg');
            
            toastMsg.innerText = message;
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            toast.style.display = 'block';

            setTimeout(() => {
                hideToast();
            }, 5000);
        }

        function hideToast() {
            document.getElementById('jsToast').style.display = 'none';
        }

        <?php if (!empty($jsMessage)): ?>
            document.addEventListener("DOMContentLoaded", function () {
                showToast("<?php echo addslashes($jsMessage); ?>", "<?php echo $jsType; ?>");
            });
        <?php endif; ?>

        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.querySelector(".filter-input");
            
            if (searchInput) {
                searchInput.addEventListener("keyup", function () {
                    const filterValue = searchInput.value.toLowerCase();
                    const tableRows = document.querySelectorAll(".table tbody tr");

                    tableRows.forEach(row => {
                        if (row.cells.length < 6) return;

                        const orderNo = row.cells[0].textContent.toLowerCase();
                        const customer = row.cells[1].textContent.toLowerCase();
                        const dateText = row.cells[2].textContent.toLowerCase();

                        if (orderNo.includes(filterValue) || customer.includes(filterValue) || dateText.includes(filterValue)) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>