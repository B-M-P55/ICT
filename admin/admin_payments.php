<?php
declare(strict_types=1);

session_start();

$activePage = 'payments';
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Payment Transactions | H2O2U Admin</title>

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Dashboard/Admin navigation style -->
    <link
        rel="stylesheet"
        href="admin-nav.css"
    >

    <!-- Your payment page style -->
    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

</head>


<body class="admin-app">


<!-- =========================================
     SAME SIDEBAR AS ADMIN DASHBOARD
========================================= -->

<?php include __DIR__ . '/admin_sidebar.php'; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<main class="container">


    <!-- =====================================
         TOP BAR
    ====================================== -->

    <header class="dashboard-top">

        <div>

            <p class="eyebrow">
                H2O2U control centre
            </p>

            <h1>
                Payment Transactions
            </h1>

            <p>
                View and manage customer payment transactions.
            </p>

        </div>


        <div class="admin-user">

            <i class="fa-solid fa-user-shield"></i>

            <span>
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
            </span>

        </div>

    </header>


    <!-- =====================================
         PAYMENT SUMMARY
    ====================================== -->

    <section class="summary">


        <!-- TOTAL ORDERS -->

        <div class="summary-card">

            <h6>
                Total Orders
            </h6>

            <h2 id="totalOrders">
                0
            </h2>

            <p>
                All time
            </p>

            <span class="card-icon">

                <i class="fa-solid fa-clipboard-list"></i>

            </span>

        </div>


        <!-- TOTAL SALES -->

        <div class="summary-card">

            <h6>
                Total Sales
            </h6>

            <h2 id="totalSales">
                Ks 0
            </h2>

            <p>
                All time
            </p>

            <span class="card-icon">

                <i class="fa-solid fa-money-bill-wave"></i>

            </span>

        </div>


        <!-- PAID ORDERS -->

        <div class="summary-card">

            <h6>
                Paid Orders
            </h6>

            <h2 id="paidOrders">
                0
            </h2>

            <p>
                All time
            </p>

            <span class="card-icon">

                <i class="fa-solid fa-circle-check"></i>

            </span>

        </div>


        <!-- PENDING ORDERS -->

        <div class="summary-card">

            <h6>
                Pending Orders
            </h6>

            <h2 id="pendingOrders">
                0
            </h2>

            <p>
                All time
            </p>

            <span class="card-icon">

                <i class="fa-solid fa-hourglass-half"></i>

            </span>

        </div>


    </section>


    <!-- =====================================
         PAYMENT TABLE
    ====================================== -->

    <section class="table-container">


        <!-- SEARCH -->

        <div class="mb-3">

            <input
                type="text"
                id="paymentSearch"
                class="form-control"
                placeholder="Search payment transactions..."
            >

        </div>


        <div class="table-responsive">

            <table
                class="admin-table"
                style="text-align:center;"
            >

                <thead>

                    <tr>

                        <th>
                            Order No.
                        </th>

                        <th>
                            Customer Name
                        </th>

                        <th>
                            Payment Amount
                        </th>

                        <th>
                            Payment Date & Time
                        </th>

                        <th>
                            Payment Method
                        </th>

                        <th>
                            Payment Status
                        </th>

                        <th>
                            Proof of Payment
                        </th>

                    </tr>

                </thead>


                <tbody
                    id="paymentTableBody"
                    style="text-align:center;"
                >

                    <!-- adminPayment.js loads payment records here -->

                </tbody>

            </table>

        </div>


        <!-- =================================
             TABLE FOOTER
        ================================== -->

        <div class="table-footer">

            <span id="paymentShowingText">
                Showing entries
            </span>


            <div class="pagination">

                <button
                    class="active"
                    type="button"
                >
                    1
                </button>

                <button type="button">
                    2
                </button>

                <button type="button">
                    3
                </button>

                <button type="button">

                    <i class="fa-solid fa-chevron-right"></i>

                </button>

            </div>

        </div>


    </section>


</main>


<!-- =========================================
     JAVASCRIPT
========================================= -->

<script src="../js/admin.js"></script>

<script src="../js/adminPayment.js"></script>


</body>

</html>