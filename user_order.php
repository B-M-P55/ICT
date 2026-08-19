<?php
include 'db.php';

$sql = "SELECT address, estimated_arrival FROM tbl_location";
$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Payment - H2O2U</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/order.css">

    <link rel="stylesheet" href="css/nav&footer.css">
</head>

<body>

    <!-- =========================================
         NAVIGATION BAR
    ========================================== -->
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
            <button class="order-btn">ORDER NOW</button>
            <a href="profile.html" class="profile">
                <i class="fa-solid fa-user"></i>
            </a>
        </div>
    </nav>

    <!-- Decorative Wave Header -->
    <div class="wave-header"></div>

    <!-- =========================================
         MAIN CHECKOUT CONTENT
    ========================================== -->
    <main class="container my-5" style="max-width: 850px;">

        <!-- YOUR ORDER SECTION -->
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

        <!-- BILLING DETAILS SECTION -->
        <section class="checkout-card">
            <div class="section-banner mb-4">
                <h2>Billing Details</h2>
            </div>

            <form id="billingForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" class="form-control" required>
                    </div>
             <div class="col-md-6">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Phone <span class="required">*</span></label>
                        <input type="tel" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" required>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Address</label>
                          <select class="form-select custom-select mb-2" id="townshipSelect"           name="township">
                            <option selected disabled>Township / City</option>
                            <?php
                              if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                  $name = htmlspecialchars($row['address']);
                                  $days = htmlspecialchars($row['estimated_arrival']);
                
                                  echo '<option value="' . $name . '" data-arrival="' . $days . '">' . $name . '</option>';
                                }
                              } else {
                                echo '<option disabled>No townships available</option>';
                            }
                            ?>
                        </select>
                        <div style="margin-top: 15px;">
                          Estimated Arrival: <strong id="arrivalDisplay" class="text-primary">Please select a township</strong>
                        </div>
                    </div>

                    <script>
                      document.getElementById('townshipSelect').addEventListener('change', function() {
                      const selectedOption = this.options[this.selectedIndex];
                      const arrivalTime = selectedOption.getAttribute('data-arrival');
    
                      document.getElementById('arrivalDisplay').textContent = arrivalTime ? arrivalTime : 'Please select a township';
                    });
                    </script>

                    <div class="col-12">
                        <label class="form-label">Detailed Address</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <!-- Privacy Box & Place Order -->
                <div class="privacy-box mt-4">
                    <p class="privacy-text mb-3">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#">privacy policy</a>.
                    </p>
                    <button type="button" class="btn btn-action" id="placeOrderBtn">Place order</button>
                </div>
            </form>
        </section>
    </main>

    <!-- =========================================
         PAYMENT MODAL
    ========================================== -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
            <div class="modal-content payment-modal-content">
                
                <div class="section-banner mb-4">
                    <h2>Payment Method</h2>
                </div>

                <div class="modal-body p-0">
                    <p class="text-muted mb-4">Estimated arrival time : 30 minutes</p>
                    <h5 class="fw-bold mb-3">Choose your payment method</h5>

                    <div class="mb-3">
                        <select class="form-select custom-select" id="paymentMethodSelect">
                            <option value="" selected disabled>Select one...</option>
                            <option value="kbz">KBZ Pay</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <!-- File Upload Input (Shows for KBZ Pay) -->
                    <div class="mb-4 d-none" id="attachmentField">
                        <div class="file-upload-wrapper">
                            <input type="file" id="paymentProof" class="form-control file-input" accept="image/*">
                            <div class="file-dummy">
                                <span>Attach the photo of payment</span>
                                <span class="required fs-5">*</span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-action mt-2" id="confirmPaymentBtn">Confirm</button>
                </div>

            </div>
        </div>
    </div>
 <!-- =========================================
         FOOTER
    ========================================== -->
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

    <!-- Bootstrap & Custom Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/order.js"></script>
</body>

</html>