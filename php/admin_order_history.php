<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to your database
include 'db_connect.php'; 

$jsMessage = "";
$jsType = "";

// Handle Delete Request (Triggers automatically handle cascading updates/deletes in database)
if (isset($_GET['delete_id'])) {
    $deleteID = intval($_GET['delete_id']);
    
    $conn->begin_transaction();
    try {
        // Delete child items first, then delivery, then parent order
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

// Fetch Summary (All time total orders count)
$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS total_order FROM tbl_order");
$summaryData = $totalOrdersQuery ? $totalOrdersQuery->fetch_assoc() : ['total_order' => 0];
$totalOrders = $summaryData['total_order'] ?? 0;

// Pagination Configuration
$resultsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $resultsPerPage;

$totalPages = ceil($totalOrders / $resultsPerPage);

// Fetch Order History matching your database columns with LIMIT and OFFSET for 5 items per page
$orderQuery = $conn->prepare("
    SELECT o.order_ID, o.order_date, o.total_order, o.total_amount, o.userID, o.location_ID, 
           u.first_name, u.last_name 
    FROM tbl_order o
    JOIN tbl_user u ON o.userID = u.userID
    ORDER BY o.order_ID ASC
    LIMIT ? OFFSET ?
");
$orderQuery->bind_param("ii", $resultsPerPage, $offset);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Admin Panel</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
<body>

    <!-- JS Toast Notification Container -->
    <div id="jsToast" class="alert alert-dismissible fade show" role="alert">
        <span id="jsToastMsg"></span>
        <button type="button" class="btn-close" onclick="hideToast()"></button>
    </div>

    <aside class="sidebar">
        <div class="logo-area">
            <img src="../img/logo.png" class="logo" alt="H2O2U Logo">
            <span class="logo-name">H2O2U</span>
        </div>
        <div class="admin-title">ADMIN PANEL</div>
        <div class="menu-section">
            <div class="menu-title">Vouchers</div>
            <a href="admin_order_history.php" class="menu-item active">• Order History</a>
            <a href="admin_payment.html" class="menu-item">• Payment Transactions</a>
        </div>
        <div class="menu-section">
            <div class="menu-title">Deliveries</div>
            <a href="admin_delivery.html" class="menu-item">• Delivery History</a>
            <a href="#" class="menu-item">• Delivery Staff</a>
        </div>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1 class="page-title">Order History</h1>
            <div class="top-actions">
                <div class="date-box">
                    <i class="fa-regular fa-calendar"></i> <?php echo date('F d, Y'); ?>
                </div>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i> Search anything..
                </div>
                <div class="admin-box">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </div>
            </div>
        </header>

        <!-- SUMMARY CARD -->
        <section class="summary order-summary" style="grid-template-columns: repeat(1, 1fr); max-width: 300px;">
            <div class="summary-card">
                <h6>Total Orders</h6>
                <h2><?php echo number_format($totalOrders); ?></h2>
                <p>All time</p>
                <span class="card-icon"><i class="fa-solid fa-clipboard-list"></i></span>
            </div>
        </section>

        <section class="table-container">
            <div class="filter-area">
                <input type="text" class="filter-input" placeholder="Search by Order No., Customer...">
            </div>

            <table class="admin-table">
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
                                <td><?php echo date('F d, Y', strtotime($row['order_date'])); ?><br><?php echo date('h:i A', strtotime($row['order_date'])); ?></td>
                                <td><?php echo $row['total_order']; ?></td>
                                <td><?php echo number_format($row['total_amount']); ?> Ks</td>
                                <td>
                                    <!-- Delete Action Link pointing to admin_order_history.php -->
                                    <a href="admin_order_history.php?delete_id=<?php echo $row['order_ID']; ?>&page=<?php echo $page; ?>" class="delete-btn text-decoration-none d-inline-block px-2 py-1 bg-danger text-white rounded" onclick="return confirm('Are you sure you want to delete this order history?');">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No order history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
            <div class="table-footer">
                <span>
                    Showing <?php echo $orderResult ? $orderResult->num_rows : 0; ?> of <?php echo $totalOrders; ?> entries
                </span>

                <!-- PAGINATION CONTROLS -->
                <div class="pagination">
                    <?php if ($totalPages > 1): ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="admin_order_history.php?page=<?php echo $i; ?>" class="btn btn-sm <?php echo ($page == $i) ? 'btn-primary active' : 'btn-outline-secondary'; ?> mx-1">
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

    <!-- SCRIPTS -->
    <script src="../js/admin.js"></script>
    <script>
        // JavaScript Toast Popup functions
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

        // Trigger JavaScript notification automatically if PHP processed an action
        <?php if (!empty($jsMessage)): ?>
            document.addEventListener("DOMContentLoaded", function () {
                showToast("<?php echo addslashes($jsMessage); ?>", "<?php echo $jsType; ?>");
            });
        <?php endif; ?>

        // Live Search Filter functionality
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.querySelector(".filter-input");
            
            if (searchInput) {
                searchInput.addEventListener("keyup", function () {
                    const filterValue = searchInput.value.toLowerCase();
                    const tableRows = document.querySelectorAll(".admin-table tbody tr");

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