```php
<?php

include 'db_connect.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $township = trim($_POST['township'] ?? '');
    $detailedAddress = trim($_POST['detailed_address'] ?? '');
    $deliveryDate = trim($_POST['delivery_date'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $paymentMethod = trim($_POST['payment'] ?? 'Cash on Delivery');
    $quantity = (int)($_POST['quantity'] ?? 1);

    /*
     * Product
     */
    $productID = 1;

    /*
     * Delivery fee is 1,000 Ks for every location
     */
    $deliveryFee = 1000;


    /*
     * Validate required fields
     */
    if (
        $fullName === '' ||
        $phone === '' ||
        $township === '' ||
        $detailedAddress === '' ||
        $deliveryDate === ''
    ) {

        $errorMessage = "Please complete all required fields.";

    } elseif ($quantity < 1) {

        $errorMessage = "Invalid quantity.";

    } elseif (
        !in_array(
            $paymentMethod,
            ['Cash on Delivery', 'KBZ Pay'],
            true
        )
    ) {

        $errorMessage = "Invalid payment method.";

    } else {

        $paymentProofPath = null;

        try {

            /*
             * Start database transaction
             */
            $conn->begin_transaction();


            /*
             * 1. Find selected location
             */
            $locationStmt = $conn->prepare("
                SELECT location_ID
                FROM tbl_location
                WHERE address = ?
                LIMIT 1
            ");

            if (!$locationStmt) {
                throw new Exception(
                    "Failed to prepare location query."
                );
            }

            $locationStmt->bind_param(
                "s",
                $township
            );

            $locationStmt->execute();

            $locationResult = $locationStmt->get_result();

            if ($locationResult->num_rows === 0) {
                throw new Exception(
                    "Selected township was not found."
                );
            }

            $location = $locationResult->fetch_assoc();

            $locationID = (int)$location['location_ID'];

            $locationStmt->close();


            /*
             * 2. Get product information
             */
            $productStmt = $conn->prepare("
                SELECT
                    product_name,
                    price,
                    stock
                FROM tbl_product
                WHERE productID = ?
                  AND is_active = 1
                LIMIT 1
            ");

            if (!$productStmt) {
                throw new Exception(
                    "Failed to prepare product query."
                );
            }

            $productStmt->bind_param(
                "i",
                $productID
            );

            $productStmt->execute();

            $productResult = $productStmt->get_result();

            if ($productResult->num_rows === 0) {
                throw new Exception(
                    "Product not found."
                );
            }

            $product = $productResult->fetch_assoc();

            $productPrice = (int)$product['price'];
            $stock = (int)$product['stock'];

            $productStmt->close();


            /*
             * 3. Check stock
             */
            if ($quantity > $stock) {

                throw new Exception(
                    "Only " .
                    $stock .
                    " item(s) are available in stock."
                );
            }


            /*
             * 4. Split customer's full name
             */
            $nameParts = explode(
                ' ',
                $fullName,
                2
            );

            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';


            /*
             * 5. Create complete address
             */
            $fullAddress =
                $township .
                ", " .
                $detailedAddress;


            /*
             * 6. Find user by phone number
             */
            $userStmt = $conn->prepare("
                SELECT userID
                FROM tbl_user
                WHERE phone_number = ?
                LIMIT 1
            ");

            if (!$userStmt) {
                throw new Exception(
                    "Failed to prepare user query."
                );
            }

            $userStmt->bind_param(
                "s",
                $phone
            );

            $userStmt->execute();

            $userResult = $userStmt->get_result();


            if ($userResult->num_rows > 0) {

                /*
                 * Existing user
                 */
                $user = $userResult->fetch_assoc();

                $userID = (int)$user['userID'];

                $userStmt->close();


                /*
                 * Update customer's latest information
                 */
                $updateUserStmt = $conn->prepare("
                    UPDATE tbl_user
                    SET
                        first_name = ?,
                        last_name = ?,
                        address = ?
                    WHERE userID = ?
                ");

                if (!$updateUserStmt) {
                    throw new Exception(
                        "Failed to prepare user update."
                    );
                }

                $updateUserStmt->bind_param(
                    "sssi",
                    $firstName,
                    $lastName,
                    $fullAddress,
                    $userID
                );

                $updateUserStmt->execute();

                $updateUserStmt->close();

            } else {

                /*
                 * New user
                 *
                 * tbl_user requires email and password,
                 * so temporary values are created.
                 */
                $userStmt->close();

                $email = $phone . "@h2o2u.local";
                $password = password_hash(
                    bin2hex(random_bytes(16)),
                    PASSWORD_DEFAULT
                );

                $insertUserStmt = $conn->prepare("
                    INSERT INTO tbl_user
                    (
                        first_name,
                        last_name,
                        email,
                        phone_number,
                        password,
                        address,
                        account_status
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, 'active')
                ");

                if (!$insertUserStmt) {
                    throw new Exception(
                        "Failed to prepare user insert."
                    );
                }

                $insertUserStmt->bind_param(
                    "ssssss",
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $password,
                    $fullAddress
                );

                $insertUserStmt->execute();

                $userID = $conn->insert_id;

                $insertUserStmt->close();
            }


            /*
             * 7. Handle KBZ Pay payment screenshot
             */
            if ($paymentMethod === 'KBZ Pay') {

                if (
                    !isset($_FILES['payment_slip']) ||
                    $_FILES['payment_slip']['error'] !== UPLOAD_ERR_OK
                ) {
                    throw new Exception(
                        "Please upload your KBZ Pay payment screenshot."
                    );
                }


                /*
                 * Upload folder
                 */
                $uploadDir = 'uploads/';

                if (!is_dir($uploadDir)) {

                    if (!mkdir(
                        $uploadDir,
                        0777,
                        true
                    )) {
                        throw new Exception(
                            "Failed to create upload folder."
                        );
                    }
                }


                /*
                 * Allowed image types
                 */
                $allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                $fileType = mime_content_type(
                    $_FILES['payment_slip']['tmp_name']
                );

                if (!in_array(
                    $fileType,
                    $allowedTypes,
                    true
                )) {
                    throw new Exception(
                        "Only JPG, PNG, and WEBP images are allowed."
                    );
                }


                /*
                 * Maximum 5 MB
                 */
                if (
                    $_FILES['payment_slip']['size'] >
                    5 * 1024 * 1024
                ) {
                    throw new Exception(
                        "Payment screenshot must be less than 5 MB."
                    );
                }


                /*
                 * Use safe file extension
                 */
                $extension = match ($fileType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };


                /*
                 * Generate unique filename
                 */
                $fileName =
                    'payment_' .
                    bin2hex(random_bytes(16)) .
                    '.' .
                    $extension;


                $paymentProofPath =
                    $uploadDir .
                    $fileName;


                /*
                 * Move uploaded file
                 */
                if (!move_uploaded_file(
                    $_FILES['payment_slip']['tmp_name'],
                    $paymentProofPath
                )) {
                    throw new Exception(
                        "Failed to upload payment screenshot."
                    );
                }
            }


            /*
             * 8. Calculate order amount
             */
            $productTotal =
                $productPrice *
                $quantity;

            $totalAmount =
                $productTotal +
                $deliveryFee;


            /*
             * 9. Insert order
             *
             * IMPORTANT:
             * tbl_order does NOT contain order_status.
             */
            $orderStmt = $conn->prepare("
                INSERT INTO tbl_order
                (
                    order_date,
                    total_order,
                    total_amount,
                    userID,
                    location_ID
                )
                VALUES
                (NOW(), ?, ?, ?, ?)
            ");

            if (!$orderStmt) {
                throw new Exception(
                    "Failed to prepare order query."
                );
            }

            $orderStmt->bind_param(
                "iiii",
                $quantity,
                $totalAmount,
                $userID,
                $locationID
            );

            $orderStmt->execute();

            $orderID = $conn->insert_id;

            $orderStmt->close();


            /*
             * 10. Insert order details
             */
            $detailStmt = $conn->prepare("
                INSERT INTO tbl_order_details
                (
                    quantity,
                    price,
                    productID,
                    orderID
                )
                VALUES
                (?, ?, ?, ?)
            ");

            if (!$detailStmt) {
                throw new Exception(
                    "Failed to prepare order details query."
                );
            }

            $detailStmt->bind_param(
                "iiii",
                $quantity,
                $productPrice,
                $productID,
                $orderID
            );

            $detailStmt->execute();

            $detailStmt->close();


            /*
             * 11. Insert payment
             */
            $paymentStatus = 'pending';

            /*
             * Database ENUM is:
             * 'Kpay'
             *
             * Therefore convert the form value
             * 'KBZ Pay' to 'Kpay'.
             */
            $databasePaymentMethod =
                ($paymentMethod === 'KBZ Pay')
                ? 'Kpay'
                : 'Cash on Delivery';


            $paymentStmt = $conn->prepare("
                INSERT INTO tbl_payment
                (
                    payment_amount,
                    payment_date,
                    payment_method,
                    payment_status,
                    payment_photo,
                    order_ID
                )
                VALUES
                (?, NOW(), ?, ?, ?, ?)
            ");

            if (!$paymentStmt) {
                throw new Exception(
                    "Failed to prepare payment query."
                );
            }

            $paymentStmt->bind_param(
                "isssi",
                $totalAmount,
                $databasePaymentMethod,
                $paymentStatus,
                $paymentProofPath,
                $orderID
            );

            $paymentStmt->execute();

            $paymentStmt->close();


            /*
             * 12. Generate unique tracking number
             */
            do {

                $trackingNumber =
                    random_int(
                        100000,
                        999999
                    );

                $trackingStmt = $conn->prepare("
                    SELECT deliveryID
                    FROM tbl_delivery
                    WHERE tracking_number = ?
                    LIMIT 1
                ");

                if (!$trackingStmt) {
                    throw new Exception(
                        "Failed to prepare tracking query."
                    );
                }

                $trackingStmt->bind_param(
                    "i",
                    $trackingNumber
                );

                $trackingStmt->execute();

                $trackingResult =
                    $trackingStmt->get_result();

                $trackingExists =
                    $trackingResult->num_rows > 0;

                $trackingStmt->close();

            } while ($trackingExists);


            /*
             * 13. Insert delivery
             */
            $deliveryStatus = 'pending';

            /*
             * These are existing driver and vehicle IDs
             * from your database.
             */
            $driverID = 1;
            $vehicleID = 1;


            $deliveryStmt = $conn->prepare("
                INSERT INTO tbl_delivery
                (
                    date,
                    status,
                    tracking_number,
                    orderID,
                    driverID,
                    vehicleID
                )
                VALUES
                (NOW(), ?, ?, ?, ?, ?)
            ");

            if (!$deliveryStmt) {
                throw new Exception(
                    "Failed to prepare delivery query."
                );
            }

            $deliveryStmt->bind_param(
                "siiii",
                $deliveryStatus,
                $trackingNumber,
                $orderID,
                $driverID,
                $vehicleID
            );

            $deliveryStmt->execute();

            $deliveryStmt->close();


            /*
             * 14. Reduce product stock
             */
            $stockStmt = $conn->prepare("
                UPDATE tbl_product
                SET stock = stock - ?
                WHERE productID = ?
                  AND stock >= ?
            ");

            if (!$stockStmt) {
                throw new Exception(
                    "Failed to prepare stock update."
                );
            }

            $stockStmt->bind_param(
                "iii",
                $quantity,
                $productID,
                $quantity
            );

            $stockStmt->execute();


            if ($stockStmt->affected_rows === 0) {
                throw new Exception(
                    "The product is no longer available in the requested quantity."
                );
            }

            $stockStmt->close();


            /*
             * 15. Commit everything
             */
            $conn->commit();


            /*
             * 16. Redirect to home page
             */
            header(
                "Location: index.php?order=success" .
                "&order_id=" .
                urlencode((string)$orderID) .
                "&tracking=" .
                urlencode((string)$trackingNumber)
            );

            exit;


        } catch (Throwable $e) {

            /*
             * Rollback database changes
             */
            if ($conn->errno === 0 || true) {
                $conn->rollback();
            }


            /*
             * Delete uploaded screenshot
             * if database operation failed
             */
            if (
                $paymentProofPath !== null &&
                file_exists($paymentProofPath)
            ) {
                unlink($paymentProofPath);
            }


            $errorMessage =
                $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>H2O2U - Order</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="../css/order.css"
    >

    <link
        rel="stylesheet"
        href="../css/nav&footer.css"
    >

</head>

<body>


<!-- NAVBAR -->

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
            <a href="index.php#products">
                Products
            </a>
        </li>

        <li>
            <a href="index.php#reviews">
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


        <a
            href="user_pf.html"
            class="profile"
        >
            <i class="fa-solid fa-user"></i>
        </a>

    </div>

</nav>


<!-- ORDER PAGE -->

<section class="order-page">

    <div class="order-overlay">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-xl-10 col-lg-11 col-md-12">

                    <div class="order-card">


                        <div class="order-title">

                            <h1>
                                Order Water
                            </h1>

                            <p>
                                Fresh and clean water delivered
                                directly to your door.
                            </p>

                        </div>


                        <?php if (!empty($errorMessage)): ?>

                            <div class="alert alert-danger">

                                <?= htmlspecialchars(
                                    $errorMessage
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <form
                            id="orderForm"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <div class="row g-5">


                                <!-- CUSTOMER INFORMATION -->

                                <div class="col-lg-7">


                                    <div class="form-group">

                                        <label for="customerName">
                                            Full Name
                                        </label>

                                        <input
                                            type="text"
                                            id="customerName"
                                            name="customer_name"
                                            class="order-input"
                                            placeholder="Enter your name"
                                            required
                                        >

                                    </div>


                                    <div class="form-group">

                                        <label for="phone">
                                            Phone Number
                                        </label>

                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone_number"
                                            class="order-input"
                                            placeholder="Enter your phone number"
                                            required
                                        >

                                    </div>


                                    <div class="form-group">

                                        <label for="township">
                                            Township / City
                                        </label>

                                        <select
                                            id="township"
                                            name="township"
                                            class="order-input"
                                            required
                                        >

                                            <option value="">
                                                Select Township / City
                                            </option>

                                            <option value="Insein">
                                                Insein
                                            </option>

                                            <option value="Hlaing">
                                                Hlaing
                                            </option>

                                            <option value="MayangGone">
                                                MayangGone
                                            </option>

                                        </select>

                                    </div>


                                    <div class="form-group">

                                        <label for="address">
                                            Delivery Address
                                        </label>

                                        <textarea
                                            id="address"
                                            name="detailed_address"
                                            class="order-input"
                                            rows="4"
                                            placeholder="Enter your delivery address"
                                            required
                                        ></textarea>

                                    </div>


                                    <div class="arrival-time-box">

                                        <div class="arrival-icon">

                                            <i class="fa-solid fa-truck"></i>

                                        </div>

                                        <div>

                                            <span class="arrival-label">
                                                Estimated arrival time
                                            </span>

                                            <strong id="arrivalTime">
                                                Please select your township
                                            </strong>

                                        </div>

                                    </div>


                                    <div class="form-group">

                                        <label for="deliveryDate">
                                            Delivery Date
                                        </label>

                                        <input
                                            type="date"
                                            id="deliveryDate"
                                            name="delivery_date"
                                            class="order-input"
                                            required
                                        >

                                    </div>


                                    <div class="form-group">

                                        <label for="note">
                                            Additional Note
                                        </label>

                                        <textarea
                                            id="note"
                                            name="note"
                                            class="order-input"
                                            rows="3"
                                            placeholder="Optional"
                                        ></textarea>

                                    </div>

                                </div>


                                <!-- ORDER SUMMARY -->

                                <div class="col-lg-5">

                                    <div class="summary-card">

                                        <h3>
                                            Order Summary
                                        </h3>


                                        <div class="summary-product">

                                            <div class="product-image">

                                                <img
                                                    src="../images/two-btl.jpg"
                                                    alt="Purified Drinking Water"
                                                >

                                            </div>


                                            <div class="product-info">

                                                <h4>
                                                    Purified Drinking Water
                                                </h4>

                                                <p>
                                                    1000 Ks. / bottle
                                                </p>


                                                <div class="quantity-box">

                                                    <button
                                                        type="button"
                                                        id="minusBtn"
                                                    >
                                                        −
                                                    </button>


                                                    <span id="quantity">
                                                        1
                                                    </span>


                                                    <input
                                                        type="hidden"
                                                        id="quantityInput"
                                                        name="quantity"
                                                        value="1"
                                                    >


                                                    <button
                                                        type="button"
                                                        id="plusBtn"
                                                    >
                                                        +
                                                    </button>

                                                </div>

                                            </div>

                                        </div>


                                        <hr>


                                        <div class="price-row">

                                            <span>
                                                Product Price
                                            </span>

                                            <span id="productPrice">
                                                1000 Ks.
                                            </span>

                                        </div>


                                        <div class="price-row">

                                            <span>
                                                Delivery Fee
                                            </span>

                                            <span id="deliveryFee">
                                                1000 Ks.
                                            </span>

                                        </div>


                                        <hr>


                                        <div class="total-row">

                                            <span>
                                                Total
                                            </span>

                                            <strong id="totalPrice">
                                                2000 Ks.
                                            </strong>

                                        </div>


                                        <!-- PAYMENT -->

                                        <div class="payment-title">
                                            Payment Method
                                        </div>


                                        <div class="payment-options">

                                            <label>

                                                <input
                                                    type="radio"
                                                    name="payment"
                                                    value="Cash on Delivery"
                                                    checked
                                                >

                                                <span>
                                                    Cash on Delivery
                                                </span>

                                            </label>


                                            <label>

                                                <input
                                                    type="radio"
                                                    name="payment"
                                                    value="KBZ Pay"
                                                    id="kbzPayRadio"
                                                >

                                                <span>
                                                    KBZ Pay
                                                </span>

                                            </label>

                                        </div>


                                        <!-- KBZ PAY UPLOAD -->

                                        <div
                                            class="kbz-upload-container"
                                            id="kbzUploadContainer"
                                            style="display: none;"
                                        >

                                            <label
                                                for="paymentSlip"
                                                class="kbz-upload-label"
                                            >

                                                <i class="fa-solid fa-paperclip"></i>

                                                Attach the photo of payment

                                            </label>


                                            <input
                                                type="file"
                                                id="paymentSlip"
                                                name="payment_slip"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="order-input-file"
                                            >

                                        </div>


                                        <button
                                            type="submit"
                                            class="place-order-btn"
                                        >
                                            PLACE ORDER
                                        </button>


                                        <p
                                            id="orderMessage"
                                            class="order-message"
                                        ></p>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

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

        <a href="index.php#products">
            Products
        </a>

        <a href="checkout.php">
            Order
        </a>

        <a href="user_payment.html">
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

        <a href="index.php#reviews">
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

        <a href="user_delivery.html">
            Delivery History
        </a>

        <a href="#">
            Vouchers
        </a>

        <a href="user_pf.html">
            User Profile
        </a>

    </div>


    <div class="copyright">

        © 2026 All Right Reserved

    </div>

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const township =
            document.getElementById("township");

        const arrivalTime =
            document.getElementById("arrivalTime");

        const minusBtn =
            document.getElementById("minusBtn");

        const plusBtn =
            document.getElementById("plusBtn");

        const quantityElement =
            document.getElementById("quantity");

        const quantityInput =
            document.getElementById("quantityInput");

        const productPrice =
            document.getElementById("productPrice");

        const deliveryFeeElement =
            document.getElementById("deliveryFee");

        const totalPrice =
            document.getElementById("totalPrice");

        const orderForm =
            document.getElementById("orderForm");

        const orderMessage =
            document.getElementById("orderMessage");

        const deliveryDateInput =
            document.getElementById("deliveryDate");

        const paymentRadios =
            document.querySelectorAll(
                'input[name="payment"]'
            );

        const kbzUploadContainer =
            document.getElementById(
                "kbzUploadContainer"
            );

        const paymentSlip =
            document.getElementById(
                "paymentSlip"
            );


        /*
         * Product price
         */
        const pricePerItem = 1000;


        /*
         * Delivery fee
         * Same for every township
         */
        const fixedDeliveryFee = 1000;


        let quantity = 1;


        /*
         * Township information
         */
        const townshipData = {

            Insein: {
                arrival: "30 minutes"
            },

            Hlaing: {
                arrival: "50 minutes"
            },

            MayangGone: {
                arrival: "45 minutes"
            }

        };


        /*
         * Prevent past delivery dates
         */
        if (deliveryDateInput) {

            const today =
                new Date()
                    .toISOString()
                    .split("T")[0];

            deliveryDateInput.min = today;
        }


        /*
         * Township change
         */
        township.addEventListener(
            "change",
            function () {

                const selected =
                    townshipData[
                        township.value
                    ];


                if (selected) {

                    arrivalTime.textContent =
                        selected.arrival;

                } else {

                    arrivalTime.textContent =
                        "Please select your township";
                }


                updateTotal();
            }
        );


        /*
         * Update total
         */
        function updateTotal() {

            const productTotal =
                pricePerItem *
                quantity;

            const total =
                productTotal +
                fixedDeliveryFee;


            quantityElement.textContent =
                quantity;

            quantityInput.value =
                quantity;


            productPrice.textContent =
                productTotal.toLocaleString() +
                " Ks.";


            deliveryFeeElement.textContent =
                fixedDeliveryFee.toLocaleString() +
                " Ks.";


            totalPrice.textContent =
                total.toLocaleString() +
                " Ks.";
        }


        /*
         * Increase quantity
         */
        plusBtn.addEventListener(
            "click",
            function () {

                quantity++;

                updateTotal();
            }
        );


        /*
         * Decrease quantity
         */
        minusBtn.addEventListener(
            "click",
            function () {

                if (quantity > 1) {

                    quantity--;

                    updateTotal();
                }
            }
        );


        /*
         * Payment method
         */
        paymentRadios.forEach(
            function (radio) {

                radio.addEventListener(
                    "change",
                    function () {

                        if (
                            this.value ===
                            "KBZ Pay"
                        ) {

                            kbzUploadContainer.style.display =
                                "block";

                            paymentSlip.required =
                                true;

                        } else {

                            kbzUploadContainer.style.display =
                                "none";

                            paymentSlip.required =
                                false;

                            paymentSlip.value =
                                "";
                        }
                    }
                );
            }
        );


        /*
         * Form validation
         */
        orderForm.addEventListener(
            "submit",
            function (event) {

                if (!township.value) {

                    event.preventDefault();

                    arrivalTime.textContent =
                        "Please select your township";

                    township.focus();

                    return;
                }


                const selectedPayment =
                    document.querySelector(
                        'input[name="payment"]:checked'
                    );


                if (
                    selectedPayment &&
                    selectedPayment.value ===
                    "KBZ Pay" &&
                    !paymentSlip.files.length
                ) {

                    event.preventDefault();

                    orderMessage.textContent =
                        "Please attach your KBZ Pay payment screenshot.";

                    orderMessage.style.color =
                        "#d9534f";

                    paymentSlip.focus();

                    return;
                }
            }
        );


        /*
         * Initial total
         */
        updateTotal();

    }
);

</script>

</body>

</html>
```
