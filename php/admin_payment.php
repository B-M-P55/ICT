<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Payment Transactions</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Because this PHP file is inside /php/ -->
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

        <div class="menu-title">
            Vouchers
        </div>

        <a href="../order-history.html" class="menu-item">
            • Order History
        </a>

        <a href="admin_payment.php" class="menu-item active">
            • Payment Transactions
        </a>

    </div>

    <div class="menu-section">

        <div class="menu-title">
            <h5>Deliveries</h5>
        </div>

        <a href="admin_delivery.php" class="menu-item">
            • Delivery History
        </a>

    </div>

    <a href="#" class="logout">
        Log Out
    </a>

</aside>

<!-- =========================================
     MAIN CONTENT
========================================= -->

<main class="main-content">


    <!-- =====================================
         TOP BAR
    ====================================== -->

    <header class="topbar">

        <h1 class="page-title">
            Payment Transactions
        </h1>


        <div class="top-actions">


            <!-- DATE -->

            <div class="date-box">

                <i class="fa-regular fa-calendar"></i>

                <?php echo date('F j, Y'); ?>

            </div>


            <!-- SEARCH -->

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                Search anything..

            </div>


            <!-- ADMIN -->

            <div class="admin-box">

                <i class="fa-solid fa-shield-halved"></i>

                Admin

            </div>

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

            <h2>
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

            <h2>
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

            <h2>
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

            <h2>
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


        <table class="admin-table"
               style="text-align: center;">

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


            <tbody id="paymentTableBody"
                   style="text-align: center;">

            </tbody>

        </table>


        <!-- =================================
             TABLE FOOTER
        ================================== -->

        <div class="table-footer">

            <span id="paymentShowingText">
                Showing entries
            </span>


            <div class="pagination">

                <button class="active">
                    1
                </button>

                <button>
                    2
                </button>

                <button>
                    3
                </button>

                <button>

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