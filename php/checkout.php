<?php
include 'db_connect.php'; 

$successMessage = "";
$errorMessage = "";

// When the user clicks place order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName        = $conn->real_escape_string($_POST['customer_name'] ?? '');
    $phone           = $conn->real_escape_string($_POST['phone_number'] ?? '');
    $township        = $conn->real_escape_string($_POST['township'] ?? '');
    $detailedAddress = $conn->real_escape_string($_POST['detailed_address'] ?? '');
    $deliveryDate    = $conn->real_escape_string($_POST['delivery_date'] ?? '');
    $note            = $conn->real_escape_string($_POST['note'] ?? '');
    $paymentMethod   = $conn->real_escape_string($_POST['payment'] ?? 'Cash on Delivery');
    $quantity        = intval($_POST['quantity'] ?? 1);
    
    // Split Full Name into first and last name for user table
    $nameParts = explode(' ', trim($fullName), 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '';

    // Fetch location and delivery fee based on selected township
    $locQuery = $conn->query("SELECT location_ID, delivery_fee FROM tbl_location WHERE address = '$township' LIMIT 1");
    if ($locQuery && $locQuery->num_rows > 0) {
        $locRow = $locQuery->fetch_assoc();
        $locationID = $locRow['location_ID'];
        $deliveryFee = floatval($locRow['delivery_fee']);
    } else {
        $locationID = 1; 
        $deliveryFee = 0.00;
    }

    // Check if user exists by phone number, otherwise create a new user record
    $userCheck = $conn->query("SELECT userID FROM tbl_user WHERE phone_number = '$phone' LIMIT 1");
    if ($userCheck && $userCheck->num_rows > 0) {
        $userRow = $userCheck->fetch_assoc();
        $userID = $userRow['userID'];
    } else {
        $fullAddress = $township . ", " . $detailedAddress;
        $conn->query("INSERT INTO tbl_user (first_name, last_name, phone_number, address, account_status) 
                      VALUES ('$firstName', '$lastName', '$phone', '$fullAddress', 'active')");
        $userID = $conn->insert_id;
    }

    // Handle KBZ Pay screenshot upload if provided
    $paymentProofPath = '';
    if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['payment_slip']['name']);
        $paymentProofPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['payment_slip']['tmp_name'], $paymentProofPath);
    }

    // Begin database transaction for data consistency
    $conn->begin_transaction();

    try {
        $orderDate = date('Y-m-d H:i:s');
        $productUnitPrice = 1000;
        $productTotal = $productUnitPrice * $quantity;
        $totalAmount = $productTotal + $deliveryFee;
        
        // Insert into tbl_order
        $conn->query("INSERT INTO tbl_order (order_date, total_order, total_amount, order_status, userID, location_ID) 
                      VALUES ('$orderDate', $quantity, $totalAmount, 'Pending', $userID, $locationID)");
        $orderID = $conn->insert_id;

        // Insert into tbl_order_details 
        $conn->query("INSERT INTO tbl_order_details (quantity, price, productID, orderID) 
                      VALUES ($quantity, $productUnitPrice, 1, $orderID)");

        // Insert into tbl_delivery history tracking table
        $trackingNumber = rand(100000, 999999);
        $conn->query("INSERT INTO tbl_delivery (date, status, tracking_number, orderID, driverID, vehicleID) 
                      VALUES ('$orderDate', 'pending', $trackingNumber, $orderID, 1, 1)");

        $conn->commit();
        $successMessage = "Order placed successfully! Tracking #: " . $trackingNumber;
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = "Failed to place order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>H2O2U - Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/order.css">
  <link rel="stylesheet" href="../css/nav&footer.css">
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="logo-section">
      <img src="../img/logo.png" alt="H2O2U Logo">
      <h2>H2O2U</h2>
    </div>
    <ul class="nav-links">
      <li><a href="homepage.html">Home</a></li>
      <li><a href="homepage.html#products">Products</a></li>
      <li><a href="homepage.html#reviews">Reviews</a></li>
      <li><a href="contact.html">Contact Us</a></li>
    </ul>
    <div class="nav-buttons">
      <button class="order-btn" onclick="window.location.href='user_orders.html'">ORDER NOW</button>
      <a href="user_pf.html" class="profile"><i class="fa-solid fa-user"></i></a>
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
                <h1>Order Water</h1>
                <p>Fresh and clean water delivered directly to your door.</p>
              </div>

              <?php if(!empty($successMessage)): ?>
                  <div class="alert alert-success"><?php echo $successMessage; ?></div>
              <?php endif; ?>
              <?php if(!empty($errorMessage)): ?>
                  <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
              <?php endif; ?>

              <!-- SINGLE FORM WRAPPER FOR ENTIRE CHECKOUT -->
              <form id="orderForm" method="POST" enctype="multipart/form-data">
                <div class="row g-5">
                  <!-- FORM INPUTS -->
                  <div class="col-lg-7">
                    <div class="form-group">
                      <label for="customerName">Full Name</label>
                      <input type="text" id="customerName" name="customer_name" class="order-input" placeholder="Enter your name" required>
                    </div>

                    <div class="form-group">
                      <label for="phone">Phone Number</label>
                      <input type="tel" id="phone" name="phone_number" class="order-input" placeholder="Enter your phone number" required>
                    </div>

                    <div class="form-group">
                      <label for="township">Township / City</label>
                      <select id="township" name="township" class="order-input" required>
                        <option value="">Select Township / City</option>
                        <option value="Insein">Insein</option>
                        <option value="Mayangone">Mayangone</option>
                        <option value="Hlaing">Hlaing</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="address">Delivery Address</label>
                      <textarea id="address" name="detailed_address" class="order-input" rows="4" placeholder="Enter your delivery address" required></textarea>
                    </div>

                    <div class="arrival-time-box">
                      <div class="arrival-icon"><i class="fa-solid fa-truck"></i></div>
                      <div>
                        <span class="arrival-label">Estimated arrival time</span>
                        <strong id="arrivalTime">Please select your township</strong>
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="deliveryDate">Delivery Date</label>
                      <input type="date" id="deliveryDate" name="delivery_date" class="order-input" required>
                    </div>

                    <div class="form-group">
                      <label for="note">Additional Note</label>
                      <textarea id="note" name="note" class="order-input" rows="3" placeholder="Optional"></textarea>
                    </div>
                  </div>

                  <!-- ORDER SUMMARY -->
                  <div class="col-lg-5">
                    <div class="summary-card">
                      <h3>Order Summary</h3>
                      <div class="summary-product">
                        <div class="product-image">
                          <img src="../images/two-btl.jpg" alt="Bottled Water">
                        </div>
                        <div class="product-info">
                          <h4>Bottled Water</h4>
                          <p>1000 Ks. / bottle</p>
                          <div class="quantity-box">
                            <button type="button" id="minusBtn">−</button>
                            <span id="quantity">1</span>
                            <!-- Hidden input to dynamically post quantity count -->
                            <input type="hidden" id="quantityInput" name="quantity" value="1">
                            <button type="button" id="plusBtn">+</button>
                          </div>
                        </div>
                      </div>
                      
                      <hr>
                      <div class="price-row">
                        <span>Product Price</span>
                        <span id="productPrice">1000 Ks.</span>
                      </div>
                      <div class="price-row">
                        <span>Delivery Fee</span>
                        <span id="deliveryFee">0 Ks.</span>
                      </div>

                      <hr>
                      <div class="total-row">
                        <span>Total</span>
                        <strong id="totalPrice">1000 Ks.</strong>
                      </div>

                      <!-- PAYMENT SECTION -->
                      <div class="payment-title">Payment Method</div>
                      <div class="payment-options">
                        <label>
                          <input type="radio" name="payment" value="Cash on Delivery" checked>
                          <span>Cash on Delivery</span>
                        </label>
                        <label>
                          <input type="radio" name="payment" value="KBZ Pay" id="kbzPayRadio">
                          <span>KBZ Pay</span>
                        </label>
                      </div>

                      <!-- KBZ PAY ATTACHMENT FIELD -->
                      <div class="kbz-upload-container" id="kbzUploadContainer" style="display: none;">
                        <label for="paymentSlip" class="kbz-upload-label">
                          <i class="fa-solid fa-paperclip"></i> Attach the photo of payment
                        </label>
                        <input type="file" id="paymentSlip" name="payment_slip" accept="image/*" class="order-input-file">
                      </div>

                      <button type="submit" class="place-order-btn">PLACE ORDER</button>
                      <p id="orderMessage" class="order-message"></p>
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
      <img src="../img/logo.png" alt="H2O2U Logo">
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
      <a href="homepage.html#products">Products</a>
      <a href="user_orders.html">Order</a>
      <a href="user_payment.html">Payment</a>
    </div>
    <div class="footer-column">
      <h3>ABOUT US</h3>
      <a href="contact.html">Contact</a>
      <a href="homepage.html#reviews">Reviews</a>
      <a href="#">Our story</a>
    </div>
    <div class="footer-column">
      <h3>INFORMATION</h3>
      <a href="user_delivery.html">Delivery History</a>
      <a href="#">Vouchers</a>
      <a href="user_pf.html">User Profile</a>
    </div>
    <div class="copyright">© 2026 All Right Reserved</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const township = document.getElementById("township");
    const arrivalTime = document.getElementById("arrivalTime");
    const minusBtn = document.getElementById("minusBtn");
    const plusBtn = document.getElementById("plusBtn");
    const quantityElement = document.getElementById("quantity");
    const quantityInput = document.getElementById("quantityInput");
    const productPrice = document.getElementById("productPrice");
    const totalPrice = document.getElementById("totalPrice");
    const orderForm = document.getElementById("orderForm");
    const orderMessage = document.getElementById("orderMessage");
    const deliveryDateInput = document.getElementById("deliveryDate");
    const paymentRadios = document.querySelectorAll('input[name="payment"]');
    const kbzUploadContainer = document.getElementById("kbzUploadContainer");
    const paymentSlip = document.getElementById("paymentSlip");

    const pricePerItem = 1000;
    let quantity = 1;

    const arrivalTimes = {
      Insein: "30 minutes",
      Mayangone: "45 minutes",
      Hlaing: "50 minutes"
    };

    // Restrict calendar past dates
    if (deliveryDateInput) {
      deliveryDateInput.min = new Date().toISOString().split("T")[0];
    }

    // Update arrival time based on Township selection
    township.addEventListener("change", function () {
      arrivalTime.textContent = arrivalTimes[township.value] || "Please select your township";
    });

    // Calculate prices
    function updateTotal() {
      const productTotal = pricePerItem * quantity;
      productPrice.textContent = `${productTotal.toLocaleString()} Ks.`;
      totalPrice.textContent = `${productTotal.toLocaleString()} Ks.`;
      quantityElement.textContent = quantity;
      if (quantityInput) {
        quantityInput.value = quantity;
      }
    }

    plusBtn.addEventListener("click", () => {
      quantity++;
      updateTotal();
    });

    minusBtn.addEventListener("click", () => {
      if (quantity > 1) {
        quantity--;
        updateTotal();
      }
    });

    // Toggle KBZ Pay attachment input field
    paymentRadios.forEach(radio => {
      radio.addEventListener("change", function () {
        if (this.value === "KBZ Pay") {
          kbzUploadContainer.style.display = "block";
          paymentSlip.setAttribute("required", "required");
        } else {
          kbzUploadContainer.style.display = "none";
          paymentSlip.removeAttribute("required");
          paymentSlip.value = ""; 
        }
      });
    });

    // Form validation logic 
    orderForm.addEventListener("submit", function (e) {
      if (!township.value) {
        e.preventDefault();
        arrivalTime.textContent = "Please select your township";
        township.focus();
        return;
      }

      const selectedPayment = document.querySelector('input[name="payment"]:checked').value;
      if (selectedPayment === "KBZ Pay" && !paymentSlip.files.length) {
        e.preventDefault();
        orderMessage.textContent = "Please attach your KBZ Pay payment screenshot.";
        orderMessage.style.color = "#d9534f";
        return;
      }
    });

    updateTotal();
  });
  </script>

</body>
</html>