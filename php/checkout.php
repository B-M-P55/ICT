<?php
// Connect to the database
include 'db.php';

$successMessage = "";
$errorMessage = "";

// When the user clicks place order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName       = $conn->real_escape_string($_POST['first_name']);
    $lastName        = $conn->real_escape_string($_POST['last_name']);
    $phone           = $conn->real_escape_string($_POST['phone_number']);
    $email           = $conn->real_escape_string($_POST['email']);
    $township        = $conn->real_escape_string($_POST['township']);
    $detailedAddress = $conn->real_escape_string($_POST['detailed_address']);
    $paymentMethod   = $conn->real_escape_string($_POST['payment_method'] ?? 'cod');

    // Look up location id based on township
    $locQuery = $conn->query("SELECT location_ID FROM tbl_location WHERE address = '$township' LIMIT 1");
    if ($locQuery && $locQuery->num_rows > 0) {
        $locRow = $locQuery->fetch_assoc();
        $locationID = $locRow['location_ID'];
    } else {
        $locationID = 1; 
    }

    // Check if user already exists, if not create them
    $userCheck = $conn->query("SELECT userID FROM tbl_user WHERE email = '$email' LIMIT 1");
    if ($userCheck && $userCheck->num_rows > 0) {
        $userRow = $userCheck->fetch_assoc();
        $userID = $userRow['userID'];
    } else {
        $fullAddress = $township . ", " . $detailedAddress;
        $conn->query("INSERT INTO tbl_user (first_name, last_name, email, phone_number, address, account_status) 
                      VALUES ('$firstName', '$lastName', '$email', '$phone', '$fullAddress', 'active')");
        $userID = $conn->insert_id;
    }

    // Handle payment screenshot upload if they choose KBZ Pay
    $paymentProofPath = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['payment_proof']['name']);
        $paymentProofPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $paymentProofPath);
    }

    // Start transaction to save order safely
    $conn->begin_transaction();

    try {
        $orderDate = date('Y-m-d H:i:s');
        
        // Save order info
        $conn->query("INSERT INTO tbl_order (order_date, total_order, total_amount, order_status, userID, location_ID) 
                      VALUES ('$orderDate', 1, 1000, 'Pending', $userID, $locationID)");
        $orderID = $conn->insert_id;

        // Save order items (Product ID 1)
        $conn->query("INSERT INTO tbl_order_details (quantity, price, productID, orderID) 
                      VALUES (1, 1000, 1, $orderID)");

        // Save delivery tracking info
        $trackingNumber = rand(100000, 999999);
        $conn->query("INSERT INTO tbl_delivery (date, status, tracking_number, orderID, driverID, vehicleID) 
                      VALUES ('$orderDate', 'pending', $trackingNumber, $orderID, 1, 1)");

        $conn->commit();
        $successMessage = "Order placed successfully! Tracking #: " . $trackingNumber;
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = "Oops, something went wrong. Please try again.";
    }
}

// Fetch townships for dropdown
$locationResult = $conn->query("SELECT address, estimated_arrival FROM tbl_location");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Payment - H2O2U</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="css/nav&footer.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo-section">
            <img src="img/logo.png" alt="H2O2U Logo">
            <h2>H2O2U</h2>
        </div>

        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="products.html">Products</a></li>
            <li><a href="#reviews">Reviews</a></li>
            <li><a href="contact.html">Contact Us</a></li>
        </ul>

        <div class="nav-buttons">
            <a href="checkout.php" class="order-btn text-decoration-none">ORDER NOW</a>
            <a href="user_pf.html" class="profile">
                <i class="fa-solid fa-user"></i>
            </a>
        </div>
    </nav>

    <div class="wave-header"></div>

    <!-- Main Content Area -->
    <main class="container my-5" style="max-width: 850px;">

        <!-- Show alerts if order succeeds or fails -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success mb-4"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mb-4"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Main form wrapping everything -->
        <form id="billingForm" method="POST" action="" enctype="multipart/form-data">

            <!-- Order Summary Section -->
            <section class="checkout-card mb-5">
                <div class="section-banner">
                    <h2>Your Order</h2>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table order-table align-middle">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th class="text-center">QUANTITY</th>
                                <th class="text-end">SUB TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BOTTLED WATER</td>
                                <td class="text-center">
                                    <div class="quantity-control d-inline-flex align-items-center">
                                        <button type="button" class="btn btn-qty" id="btnMinus">-</button>
                                        <span class="qty-display mx-3 fw-bold" id="itemQty">1</span>
                                        <button type="button" class="btn btn-qty" id="btnPlus">+</button>
                                    </div>
                                </td>
                                <td class="text-end" id="itemSubtotal">5000 Ks.</td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="2">SUB TOTAL</td>
                                <td class="text-end" id="orderSubtotal">5000 Ks.</td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-end" id="orderTotal">5000 Ks.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- User Billing Details -->
            <section class="checkout-card mb-5">
                <div class="section-banner mb-4">
                    <h2>Billing Details</h2>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Phone <span class="required">*</span></label>
                        <input type="tel" class="form-control" name="phone_number" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <select class="form-select custom-select mb-2" id="townshipSelect" name="township" required>
                            <option selected disabled value="">Township / City</option>
                            <?php 
                            // Loop through database locations to fill dropdown
                            if ($locationResult && $locationResult->num_rows > 0) {
                                while ($row = $locationResult->fetch_assoc()) {
                                    $locName = htmlspecialchars($row['address']);
                                    $arrival = htmlspecialchars($row['estimated_arrival']);
                                    echo '<option value="' . $locName . '" data-arrival="' . $arrival . '">' . $locName . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <small class="text-muted">Estimated arrival time: <span id="arrivalDisplay" class="fw-bold text-primary">Select a township</span></small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Detailed Address</label>
                        <textarea class="form-control" rows="3" name="detailed_address"></textarea>
                    </div>
                </div>

                <div class="privacy-box mt-4">
                    <p class="privacy-text mb-3">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#">privacy policy</a>.
                    </p>
                </div>
            </section>

            <!-- Payment Selection Section -->
            <section class="checkout-card">
                <div class="section-banner mb-4">
                    <h2>Payment Method</h2>
                </div>

                <div class="modal-body p-0">
                    <h5 class="fw-bold mb-3">Choose your payment method</h5>

                    <div class="mb-3">
                        <select class="form-select custom-select" id="paymentMethodSelect" name="payment_method" required>
                            <option value="" selected disabled>Select one...</option>
                            <option value="kbz">KBZ Pay</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <!-- Hidden by default, shows up if KBZ Pay is picked -->
                    <div class="mb-4 d-none" id="attachmentField">
                        <div class="file-upload-wrapper">
                            <input type="file" id="paymentProof" name="payment_proof" class="form-control file-input" accept="image/*">
                            <div class="file-dummy">
                                <span>Attach the photo of payment</span>
                                <span class="required fs-5">*</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-action mt-2 w-100 py-3" id="placeOrderBtn">Place order</button>
                </div>
            </section>

        </form>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-logo">
            <img src="img/logo.png" alt="H2O2U Logo">
            <h2>H2O2U</h2>
        </div>

        <div class="footer-column">
            <h3>PRIVACY</h3>
            <a href="#">Terms of use</a>
            <a href="#">Privacy policy</a>
            <a href="#">Cookies</a>
        </div>

        <div class="footer-column">
            <h3>SERVICES</h3>
            <a href="products.html">Products</a>
            <a href="#">Order</a>
            <a href="#">Payment</a>
        </div>

        <div class="footer-column">
            <h3>ABOUT US</h3>
            <a href="contact.html">Contact</a>
            <a href="#reviews">Reviews</a>
            <a href="#">Our story</a>
        </div>

        <div class="footer-column">
            <h3>INFORMATION</h3>
            <a href="delivery.html">Delivery History</a>
            <a href="vouchers.html">Vouchers</a>
            <a href="profile.html">User Profile</a>
        </div>

        <div class="copyright">
            © 2026 All Right Reserved
        </div>
    </footer>

    <!-- Script to toggle screenshot field and update delivery time -->
    <script>
        document.getElementById('paymentMethodSelect').addEventListener('change', function() {
            let attachmentField = document.getElementById('attachmentField');
            if (this.value === 'kbz') {
                attachmentField.classList.remove('d-none');
            } else {
                attachmentField.classList.add('d-none');
            }
        });

        document.getElementById('townshipSelect').addEventListener('change', function() {
            let arrivalTime = this.options[this.selectedIndex].getAttribute('data-arrival');
            document.getElementById('arrivalDisplay').textContent = arrivalTime ? arrivalTime : 'Select a township';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/order.js"></script>
</body>

</html>