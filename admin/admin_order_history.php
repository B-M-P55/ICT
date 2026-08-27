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

$stmt = $db->query(
    "SELECT COUNT(*) AS total_orders FROM tbl_order"
);

$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$totalOrders = (int)($summary['total_orders'] ?? 0);

$resultsPerPage = 10;

$page = isset($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;

$offset = ($page - 1) * $resultsPerPage;

$totalPages = max(
    1,
    (int)ceil($totalOrders / $resultsPerPage)
);

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
    ORDER BY o.order_ID DESC
    LIMIT :limit OFFSET :offset"
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

    <title>Order History | H2O2U Admin</title>

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
</head>

<body class="admin-app">

<?php include __DIR__ . '/admin_sidebar.php'; ?>

<main class="container">

    <header class="dashboard-top mb-4">
        <div>
            <p class="eyebrow">Operations</p>

            <h1>Order History</h1>

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

    <?php if ($message): ?>
        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
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
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <section class="summary mb-4">

        <div
            class="summary-card position-relative
                   p-4 bg-white rounded shadow-sm border"
        >

            <p class="text-muted mb-1 fw-medium">
                Total Orders
            </p>

            <h2 class="fw-bold mb-0">
                <?= number_format($totalOrders) ?>
            </h2>

            <small class="text-secondary">
                All customer orders
            </small>

            <span
                class="card-icon position-absolute
                       top-50 end-0
                       translate-middle-y
                       me-4 fs-2 text-muted"
            >
                <i class="fa-solid fa-cart-shopping"></i>
            </span>

        </div>

    </section>

    <section class="admin-panel mb-4">

        <div
            class="section-head p-3 border-bottom
                   d-flex justify-content-between
                   align-items-center"
        >

            <div>
                <h2 class="h5 mb-1">
                    Customer Orders
                </h2>

                <p class="text-muted small mb-0">
                    View and manage customer orders.
                </p>
            </div>

            <div style="width: 250px;">
                <input
                    type="text"
                    id="orderSearch"
                    class="form-control form-control-sm"
                    placeholder="Search order or customer..."
                >
            </div>

        </div>

        <div class="table-responsive">

            <table
                class="table align-middle text-center mb-0"
                id="ordersTable"
            >

                <thead class="table-dark">
                    <tr>
                        <th>Order No.</th>
                        <th>Customer Name</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($orders)): ?>

                    <?php foreach ($orders as $row): ?>

                        <?php
                        $customerName = trim(
                            ($row['first_name'] ?? '') . ' ' .
                            ($row['last_name'] ?? '')
                        );

                        if ($customerName === '') {
                            $customerName = 'Guest';
                        }
                        ?>

                        <tr>

                            <td class="fw-semibold">
                                #<?= (int)$row['order_ID'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customerName) ?>
                            </td>

                            <td>
                                <?= (int)($row['total_order'] ?? 0) ?>
                            </td>

                            <td class="fw-bold">
                                <?= number_format(
                                    (float)($row['total_amount'] ?? 0),
                                    2
                                ) ?>
                                Ks
                            </td>

                            <td>
                                <form
                                    method="post"
                                    class="d-inline"
                                    onsubmit="return confirm(
                                        'Are you sure you want to delete Order #<?= (int)$row['order_ID'] ?>?'
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
                                        value="<?= (int)$row['order_ID'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                    >
                                        <i class="fa-solid fa-trash me-1"></i>
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
                            class="text-center py-5 text-muted"
                        >
                            <i
                                class="fa-solid
                                       fa-cart-shopping
                                       fs-2
                                       mb-3"
                            ></i>

                            <div>
                                No orders found.
                            </div>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div
            class="d-flex
                   justify-content-between
                   align-items-center
                   p-3
                   border-top"
        >

            <span class="text-muted small">
                Showing
                <?= count($orders) ?>
                of
                <?= $totalOrders ?>
                orders
            </span>

            <div class="pagination pagination-sm m-0">

                <?php if ($totalPages > 1): ?>

                    <?php if ($page > 1): ?>

                        <a
                            href="?page=<?= $page - 1 ?>"
                            class="btn btn-sm btn-outline-secondary mx-1"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>

                    <?php endif; ?>

                    <?php for (
                        $i = 1;
                        $i <= $totalPages;
                        $i++
                    ): ?>

                        <a
                            href="?page=<?= $i ?>"
                            class="btn btn-sm
                                <?= $page === $i
                                    ? 'btn-primary'
                                    : 'btn-outline-secondary'
                                ?>
                                mx-1"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>

                        <a
                            href="?page=<?= $page + 1 ?>"
                            class="btn btn-sm btn-outline-secondary mx-1"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

    </section>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('orderSearch');
    const tableRows = document.querySelectorAll(
        '#ordersTable tbody tr'
    );

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function () {

        const searchValue = searchInput.value
            .toLowerCase()
            .trim();

        tableRows.forEach(function (row) {

            if (row.cells.length < 5) {
                return;
            }

            const orderNumber = row.cells[0]
                .textContent
                .toLowerCase();

            const customerName = row.cells[1]
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
        });
    });
});
</script>

</body>
</html>