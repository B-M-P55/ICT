<?php
$orderSuccess = isset($_GET['order']) && $_GET['order'] === 'success';
$orderID = $_GET['order_id'] ?? '';
$trackingNumber = $_GET['tracking'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>H2O2U - Home</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <link
        rel="stylesheet"
        href="../css/nav&footer.css"
    >

</head>

<body>


<!-- =========================================
     NAVBAR
========================================= -->

<nav class="navbar">

    <div class="logo-section">

        <img
            src="../img/logo.png"
            alt="H2O2U Logo"
        >

        <h2>H2O2U</h2>

    </div>


    <ul class="nav-links">

        <li>
            <a href="index.php">
                Home
            </a>
        </li>

        <li>
            <a href="products.php">
                Products
            </a>
        </li>

        <li>
            <a href="#reviews">
                Reviews
            </a>
        </li>

        <li>
            <a href="contact.html">
                Contact Us
            </a>
        </li>

    </ul>


    <div class="nav-buttons">

        <button
            class="order-btn"
            onclick="window.location.href='checkout.php'"
        >
            ORDER NOW
        </button>

    </div>

</nav>


<!-- =========================================
     ORDER SUCCESS MESSAGE
========================================= -->

<?php if ($orderSuccess): ?>

    <div class="container mt-4">

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <h5 class="alert-heading">
                <i class="fa-solid fa-circle-check"></i>
                Order Successful!
            </h5>

            <p class="mb-1">
                Your order has been placed successfully.
            </p>

            <?php if ($orderID !== ''): ?>

                <p class="mb-1">
                    <strong>Order No:</strong>
                    <?php echo htmlspecialchars($orderID); ?>
                </p>

            <?php endif; ?>

            <?php if ($trackingNumber !== ''): ?>

                <p class="mb-0">
                    <strong>Tracking Number:</strong>
                    <?php echo htmlspecialchars($trackingNumber); ?>
                </p>

            <?php endif; ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    </div>

<?php endif; ?>


<!-- =========================================
     HERO SECTION
========================================= -->

<section class="hero-section">

    <div class="container hero-overlay">

        <div class="row align-items-center w-100">


            <div class="col-lg-7">

                <div class="hero-content">

                    <h1>

                        Delivered Right
                        <br>
                        To Your Home &
                        <br>
                        Office Door.

                    </h1>


                    <p>

                        Fresh and clean drinking water
                        <br>
                        delivered directly to you.

                    </p>


                    <button
                        class="order-btn"
                        onclick="window.location.href='checkout.php'"
                    >
                        ORDER NOW
                    </button>

                </div>

            </div>


            <div
                class="col-lg-5 d-flex justify-content-center justify-content-lg-end mt-4 mt-lg-0"
            >

                <div class="offer-circle">

                    <span>
                        Limited Offer
                    </span>

                    <strong>
                        30% OFF
                    </strong>

                    <img
                        src="images/two-btl.jpg"
                        class="offer-bottle"
                        alt="Water Bottles"
                    >

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     SERVICE SECTION
========================================= -->

<section class="service-section">

    <div class="container">

        <div class="row text-center">


            <div class="col-6 col-md-3 service-item">

                <div class="service-icon">

                    <i class="fa-solid fa-droplet"></i>

                </div>

                <span>
                    Fresh Water
                </span>

            </div>


            <div class="col-6 col-md-3 service-item">

                <div class="service-icon">

                    <i class="fa-solid fa-cart-shopping"></i>

                </div>

                <span>
                    Easy Order
                </span>

            </div>


            <div class="col-6 col-md-3 service-item">

                <div class="service-icon">

                    <i class="fa-solid fa-truck"></i>

                </div>

                <span>
                    Fast Delivery
                </span>

            </div>


            <div class="col-6 col-md-3 service-item">

                <div class="service-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>

                <span>
                    Safe & Clean
                </span>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     PRODUCTS SECTION
========================================= -->

<section
    class="products-section"
    id="products"
>

    <div class="container">


        <div class="section-heading">

            <span>
                PRODUCTS
            </span>

            <h2>
                We proudly provide water products.
            </h2>

        </div>


        <div
            id="productRow"
            class="row g-4"
        >

            <!-- Products will be loaded by JavaScript -->

        </div>

    </div>

</section>


<!-- =========================================
     DELIVERY BANNER
========================================= -->

<section class="delivery-banner">

    <div class="delivery-content">

        <h2>
            Delivered fresh to your door
        </h2>

        <p>
            by our team.
        </p>

    </div>

</section>


<!-- =========================================
     REVIEWS
========================================= -->

<section
    class="reviews-section"
    id="reviews"
>

    <div class="container">


        <div class="section-heading">

            <span>
                REVIEWS
            </span>

        </div>


        <div class="row g-4 justify-content-center">


            <!-- REVIEW 1 -->

            <div class="col-md-6 col-lg-5">

                <div class="review-card">

                    <div class="review-header">

                        <div class="review-user">

                            <div class="user-circle"></div>

                            <span>
                                John Smith
                            </span>

                        </div>

                        <div class="stars">
                            ★★★★★
                        </div>

                    </div>


                    <p>

                        Very satisfied with the water
                        delivery service. The water is fresh
                        and the delivery is always on time.

                    </p>

                </div>

            </div>


            <!-- REVIEW 2 -->

            <div class="col-md-6 col-lg-5">

                <div class="review-card">

                    <div class="review-header">

                        <div class="review-user">

                            <div class="user-circle"></div>

                            <span>
                                Jane Davis
                            </span>

                        </div>

                        <div class="stars">
                            ★★★★★
                        </div>

                    </div>


                    <p>

                        Excellent service and very convenient
                        ordering system. Highly recommended.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     FOOTER
========================================= -->

<footer class="footer">


    <div class="footer-logo">

        <img
            src="../img/logo.png"
            alt="H2O2U Logo"
        >

        <h2>
            H2O2U
        </h2>

    </div>


    <div class="footer-column">

        <h3>
            PRIVACY
        </h3>

        <a href="#">
            Terms of use
        </a>

        <a href="#">
            Privacy policy
        </a>

        <a href="#">
            Cookies
        </a>

    </div>


    <div class="footer-column">

        <h3>
            SERVICES
        </h3>

        <a href="products.php">
            Products
        </a>

        <a href="checkout.php">
            Order
        </a>

        <a href="#">
            Payment
        </a>

    </div>


    <div class="footer-column">

        <h3>
            ABOUT US
        </h3>

        <a href="contact.html">
            Contact
        </a>

        <a href="#reviews">
            Reviews
        </a>

        <a href="#">
            Our story
        </a>

    </div>


    <div class="footer-column">

        <h3>
            INFORMATION
        </h3>

        <a href="delivery.html">
            Delivery History
        </a>

        <a href="vouchers.html">
            Vouchers
        </a>

        <a href="profile.html">
            User Profile
        </a>

    </div>


    <div class="copyright">

        © 2026 All Right Reserved

    </div>

</footer>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Custom JS -->

<script src="js/script.js"></script>

<script src="js/navbar.js"></script>


</body>

</html>
