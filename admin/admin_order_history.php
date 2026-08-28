<?php
declare(strict_types=1);

require_once __DIR__ . '/adminauth.php';

require_admin();

$db = database();

function order_token(): string
{
    start_session();

    if (!isset($_SESSION['order_token'])) {
        $_SESSION['order_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['order_token'];
}

function verify_order_token(): void
{
    start_session();

    $sessionToken = $_SESSION['order_token'] ?? '';
    $postedToken = $_POST['token'] ?? '';

    if (
        empty($sessionToken) ||
        empty($postedToken) ||
        !hash_equals($sessionToken, $postedToken)
    ) {
        throw new RuntimeException(
            'Your session expired. Please refresh the page and try again.'
        );
    }
}

$message = '';
$error = '';

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        verify_order_token();

        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {

            $orderId = (int)($_POST['order_id'] ?? 0);

            if ($orderId <= 0) {
                throw new RuntimeException('Invalid order ID.');
            }

            $db->beginTransaction();

            $stmt = $db->prepare(
                "DELETE FROM tbl_order_details WHERE orderID = ?"
            );

            $stmt->execute([$orderId]);


            $stmt = $db->prepare(
                "DELETE FROM tbl_payment WHERE order_ID = ?"
            );

            $stmt->execute([$orderId]);


            $stmt = $db->prepare(
                "DELETE FROM tbl_delivery WHERE orderID = ?"
            );

            $stmt->execute([$orderId]);


            $stmt = $db->prepare(
                "DELETE FROM tbl_order WHERE order_ID = ?"
            );

            $stmt->execute([$orderId]);


            $db->commit();

            $message = "Order #{$orderId} deleted successfully.";
        }
    }

} catch (Throwable $exception) {

    if ($db->inTransaction()) {
        $db->rollBack();
    }

    $error = $exception->getMessage();
}


/*
 * Total orders
 */

$stmt = $db->query(
    "SELECT COUNT(*) AS total_orders
     FROM tbl_order"
);

$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$totalOrders = (int)($summary['total_orders'] ?? 0);


/*
 * Pagination
 */

$resultsPerPage = 5;

$page = isset($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;

$totalPages = max(
    1,
    (int)ceil($totalOrders / $resultsPerPage)
);

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $resultsPerPage;


/*
 * Get orders
 *
 * ASCENDING ORDER
 */

$stmt = $db->prepare(
    "SELECT
        o.order_ID,
        o.total_order,
        o.total_amount,
        u.first_name,
        u.last_name
    FROM tbl_order o

    LEFT JOIN tbl_user u
        ON o.userID = u.userID

    ORDER BY o.order_ID ASC

    LIMIT :limit
    OFFSET :offset"
);

$stmt->bindValue(
    ':limit',
    $resultsPerPage,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activePage = 'orders';

?>


<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Order History | H2O2U Admin
    </title>


    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="admin-dashboard.css"
    >


    <style>

        /*
         * PAGE
         */

        .orders-page {
            padding-bottom: 40px;
        }


        /*
         * HEADER
         */

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
        }

        .orders-header h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            color: #172033;
        }

        .orders-header p {
            margin: 6px 0 0;
            color: #7a8494;
            font-size: 14px;
        }


        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: #ffffff;
            border: 1px solid #e8ebf0;
            border-radius: 10px;
            color: #273142;
            font-weight: 600;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        }

        .admin-user i {
            font-size: 17px;
        }


        /*
         * SUMMARY CARD
         */

        .order-summary-card {
            position: relative;
            background: #ffffff;
            border: 1px solid #e7eaf0;
            border-radius: 14px;
            padding: 22px 24px;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04);
        }

        .order-summary-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #0d6efd;
        }

        .summary-label {
            color: #7c8798;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .summary-number {
            font-size: 28px;
            font-weight: 700;
            color: #172033;
            margin: 0;
        }

        .summary-description {
            color: #929baa;
            font-size: 13px;
            margin: 4px 0 0;
        }

        .summary-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #eef5ff;
            color: #0d6efd;
            font-size: 20px;
        }


        /*
         * ALERT
         */

        .custom-alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }


        /*
         * MAIN PANEL
         */

        .orders-panel {
            background: #ffffff;
            border: 1px solid #e7eaf0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
        }


        /*
         * PANEL HEADER
         */

        .orders-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f4;
        }

        .orders-panel-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: #202938;
            margin: 0;
        }

        .orders-panel-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #8a94a4;
        }


        /*
         * SEARCH
         */

        .search-box {
            position: relative;
            width: 260px;
        }

        .search-box i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b1;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            height: 38px;
            border: 1px solid #dfe3e9;
            border-radius: 8px;
            padding: 0 12px 0 36px;
            font-size: 13px;
            outline: none;
            transition: 0.2s;
        }

        .search-box input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.08);
        }


        /*
         * TABLE
         */

        .orders-table {
            margin: 0;
        }

        .orders-table thead th {
            background: #f8f9fb;
            color: #667085;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 14px 18px;
            border-bottom: 1px solid #e9ecf1;
            white-space: nowrap;
        }

        .orders-table tbody td {
            padding: 17px 18px;
            border-bottom: 1px solid #f0f2f5;
            color: #394150;
            font-size: 14px;
            vertical-align: middle;
        }

        .orders-table tbody tr {
            transition: background 0.2s ease;
        }

        .orders-table tbody tr:hover {
            background: #f9fbff;
        }

        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }


        /*
         * ORDER NUMBER
         */

        .order-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 70px;
            padding: 6px 10px;
            background: #f0f5ff;
            color: #0d6efd;
            border-radius: 7px;
            font-weight: 700;
            font-size: 13px;
        }


        /*
         * CUSTOMER
         */

        .customer-name {
            font-weight: 600;
            color: #273142;
        }


        /*
         * QUANTITY
         */

        .quantity-badge {
            display: inline-flex;
            min-width: 35px;
            justify-content: center;
            padding: 5px 9px;
            border-radius: 6px;
            background: #f2f4f7;
            color: #475467;
            font-weight: 600;
        }


        /*
         * AMOUNT
         */

        .amount {
            font-weight: 700;
            color: #202938;
        }


        /*
         * DELETE BUTTON
         */

        .delete-btn {
            border-radius: 7px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 600;
        }


        /*
         * EMPTY STATE
         */

        .empty-state {
            padding: 60px 20px !important;
            text-align: center;
            color: #98a2b3 !important;
        }

        .empty-state i {
            display: block;
            font-size: 36px;
            margin-bottom: 12px;
            color: #c4cad3;
        }


        /*
         * FOOTER
         */

        .orders-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-top: 1px solid #edf0f4;
        }

        .showing-text {
            color: #8a94a4;
            font-size: 13px;
        }


        /*
         * PAGINATION
         */

        .order-pagination {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }

        .order-pagination a,
        .order-pagination span {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #e0e4ea;
            color: #667085;
            background: #ffffff;
            transition: 0.2s;
        }

        .order-pagination a:hover {
            background: #f1f5ff;
            border-color: #b9cdfc;
            color: #0d6efd;
        }

        .order-pagination .active {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .order-pagination .disabled {
            color: #c5cad2;
            background: #f8f9fb;
            cursor: not-allowed;
        }


        /*
         * RESPONSIVE
         */

        @media (max-width: 768px) {

            .orders-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .orders-panel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .orders-footer {
                flex-direction: column;
                gap: 15px;
            }

        }

    </style>

</head>


<body class="admin-app">


<?php include __DIR__ . '/admin_sidebar.php'; ?>


<main class="container orders-page">


    <!-- HEADER -->

    <header class="orders-header">

        <div>

            <p class="eyebrow">
                Operations
            </p>

            <h1>
                Order History
            </h1>

            <p>
                View and manage customer orders.
            </p>

        </div>


        <div class="admin-user">

            <i class="fa-solid fa-user-shield"></i>

            <span>
                <?= htmlspecialchars(
                    $_SESSION['admin_name'] ?? 'Admin'
                ) ?>
            </span>

        </div>

    </header>


    <!-- ALERTS -->

    <?php if ($message): ?>

        <div
            class="alert alert-success custom-alert alert-dismissible fade show"
            role="alert"
        >

            <i class="fa-solid fa-circle-check me-2"></i>

            <?= htmlspecialchars($message) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div
            class="alert alert-danger custom-alert alert-dismissible fade show"
            role="alert"
        >

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- SUMMARY -->

    <section class="order-summary-card">

        <div class="summary-label">
            TOTAL ORDERS
        </div>

        <h2 class="summary-number">
            <?= number_format($totalOrders) ?>
        </h2>

        <p class="summary-description">
            All customer orders
        </p>


        <div class="summary-icon">

            <i class="fa-solid fa-cart-shopping"></i>

        </div>

    </section>


    <!-- ORDERS PANEL -->

    <section class="orders-panel">


        <!-- PANEL HEADER -->

        <div class="orders-panel-header">

            <div>

                <h2>
                    Customer Orders
                </h2>

                <p>
                    View and manage recent customer orders.
                </p>

            </div>


            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="orderSearch"
                    placeholder="Search order or customer..."
                >

            </div>

        </div>


        <!-- TABLE -->

        <div class="table-responsive">

            <table
                class="table orders-table"
                id="ordersTable"
            >

                <thead>

                    <tr>

                        <th class="text-center">
                            Order No.
                        </th>

                        <th>
                            Customer Name
                        </th>

                        <th class="text-center">
                            Quantity
                        </th>

                        <th class="text-end">
                            Amount
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!empty($orders)): ?>


                    <?php foreach ($orders as $row): ?>


                        <?php

                        $customerName = trim(
                            ($row['first_name'] ?? '') .
                            ' ' .
                            ($row['last_name'] ?? '')
                        );

                        if ($customerName === '') {
                            $customerName = 'Guest';
                        }

                        $orderId = (int)$row['order_ID'];

                        ?>


                        <tr>


                            <!-- ORDER NUMBER -->

                            <td class="text-center">

                                <span class="order-number">

                                    #<?= $orderId ?>

                                </span>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <span class="customer-name">

                                    <?= htmlspecialchars(
                                        $customerName
                                    ) ?>

                                </span>

                            </td>


                            <!-- QUANTITY -->

                            <td class="text-center">

                                <span class="quantity-badge">

                                    <?= (int)(
                                        $row['total_order'] ?? 0
                                    ) ?>

                                </span>

                            </td>


                            <!-- AMOUNT -->

                            <td class="text-end">

                                <span class="amount">

                                    <?= number_format(
                                        (float)(
                                            $row['total_amount'] ?? 0
                                        ),
                                        2
                                    ) ?>

                                    Ks

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td class="text-center">

                                <form
                                    method="post"
                                    class="d-inline"
                                    onsubmit="return confirm(
                                        'Are you sure you want to delete Order #<?= $orderId ?>?'
                                    );"
                                >

                                    <input
                                        type="hidden"
                                        name="token"
                                        value="<?= htmlspecialchars(
                                            order_token()
                                        ) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >


                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= $orderId ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger delete-btn"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                        Delete

                                    </button>

                                </form>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="5"
                            class="empty-state"
                        >

                            <i class="fa-solid fa-cart-shopping"></i>

                            No orders found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


        <!-- FOOTER -->

        <div class="orders-footer">


            <span class="showing-text">

                Showing

                <?= $totalOrders > 0
                    ? $offset + 1
                    : 0
                ?>

                -

                <?= min(
                    $offset + $resultsPerPage,
                    $totalOrders
                ) ?>

                of

                <?= $totalOrders ?>

                orders

            </span>


            <!-- PAGINATION -->

            <div class="order-pagination">


                <!-- BACKWARD -->

                <?php if ($page > 1): ?>

                    <a
                        href="?page=<?= $page - 1 ?>"
                        title="Previous page"
                    >

                        <i class="fa-solid fa-chevron-left"></i>

                    </a>

                <?php else: ?>

                    <span class="disabled">

                        <i class="fa-solid fa-chevron-left"></i>

                    </span>

                <?php endif; ?>


                <!-- PAGE NUMBERS -->

                <?php for (
                    $i = 1;
                    $i <= $totalPages;
                    $i++
                ): ?>

                    <?php if ($i === $page): ?>

                        <span class="active">

                            <?= $i ?>

                        </span>

                    <?php else: ?>

                        <a
                            href="?page=<?= $i ?>"
                        >

                            <?= $i ?>

                        </a>

                    <?php endif; ?>

                <?php endfor; ?>


                <!-- FORWARD -->

                <?php if ($page < $totalPages): ?>

                    <a
                        href="?page=<?= $page + 1 ?>"
                        title="Next page"
                    >

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                <?php else: ?>

                    <span class="disabled">

                        <i class="fa-solid fa-chevron-right"></i>

                    </span>

                <?php endif; ?>


            </div>

        </div>


    </section>


</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchInput =
            document.getElementById('orderSearch');

        const tableRows =
            document.querySelectorAll(
                '#ordersTable tbody tr'
            );


        if (!searchInput) {
            return;
        }


        searchInput.addEventListener(
            'input',
            function () {

                const searchValue =
                    searchInput.value
                        .toLowerCase()
                        .trim();


                tableRows.forEach(
                    function (row) {

                        if (row.cells.length < 5) {
                            return;
                        }


                        const orderNumber =
                            row.cells[0]
                                .textContent
                                .toLowerCase();


                        const customerName =
                            row.cells[1]
                                .textContent
                                .toLowerCase();


                        if (
                            orderNumber.includes(searchValue) ||
                            customerName.includes(searchValue)
                        ) {

                            row.style.display = '';

                        } else {

                            row.style.display = 'none';

                        }

                    }
                );

            }
        );

    }
);

</script>


</body>

</html>