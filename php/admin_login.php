<?php
session_start();
require_once 'db_connect.php';

$error = isset($_GET['error']) ? urldecode($_GET['error']) : "";
$signupError = "";
$signupSuccess = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $firstName       = trim($_POST['first_name']);
    $lastName        = trim($_POST['last_name']);
    $phone           = trim($_POST['phone_number']);
    $email           = trim($_POST['email']);
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $signupError = "Passwords do not match.";
    } else {
        $emailCheck = $conn->prepare("SELECT userID FROM tbl_user WHERE email = ? LIMIT 1");
        $emailCheck->bind_param("s", $email);
        $emailCheck->execute();
        $emailResult = $emailCheck->get_result();

        if ($emailResult->num_rows > 0) {
            $signupError = "Email address is already registered.";
        } else {
            $insertStmt = $conn->prepare("INSERT INTO tbl_user (first_name, last_name, email, password, phone_number, address, account_status) VALUES (?, ?, ?, ?, ?, '', 'active')");
            $insertStmt->bind_param("sssss", $firstName, $lastName, $email, $password, $phone);

            if ($insertStmt->execute()) {

                header("Location: ../homepage.html");
                exit();


                header("Location: ../homepage.html");
                exit();


                header("Location: ../homepage.html");
                exit();

                $signupSuccess = "Account created successfully! You can now log in.";
               // header("Location: ../homepage.html");
                //exit();




            } else {
                $signupError = "Failed to create account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - H2O2U</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/nav&footer.css">

    <style>
        body {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 50%, #7dd3fc 100%);
            min-height: 100vh;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px 0 rgba(0, 100, 180, 0.15);
            width: 100%;
            max-width: 480px;
        }

        .nav-pills .nav-link {
            color: #0369a1;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background-color: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.85);
            border-color: #0284c7;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.25);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.8);
            color: #0284c7;
        }

        .btn-action {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            color: #fff;
            transform: translateY(-1px);
        }

        .required {
            color: #e11d48;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo-section">
            <img src="../img/logo.png" alt="H2O2U Logo">
            <h2>H2O2U</h2>
        </div>
        <ul class="nav-links">
            <li><a href="../homepage.html">Home</a></li>
            <li><a href="../homepage.html#products">Products</a></li>
            <li><a href="../homepage.html#reviews">Reviews</a></li>
            <li><a href="../contact.html">Contact Us</a></li>
        </ul>
        <div class="nav-buttons">
            <button class="order-btn" onclick="window.location.href='../user_orders.html'">ORDER NOW</button>

            <a href="../user_pf.html" class="profile"><i class="fa-solid fa-user"></i></a>

        </div>
    </nav>

    <main class="container my-5 d-flex justify-content-center">
        <div class="auth-card">
            
            <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo empty($signupError) && empty($signupSuccess) ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-pane" type="button" role="tab">Log In</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo !empty($signupError) || !empty($signupSuccess) ? 'active' : ''; ?>" id="signup-tab" data-bs-toggle="pill" data-bs-target="#signup-pane" type="button" role="tab">Sign Up</button>
                </li>
            </ul>

            <div class="tab-content" id="authTabsContent">
                
                <!-- LOG IN -->
                <div class="tab-pane fade <?php echo empty($signupError) && empty($signupSuccess) ? 'show active' : ''; ?>" id="login-pane" role="tabpanel">
                    <div class="mb-4 text-center">
                        <h4 class="fw-bold m-0">Welcome Back</h4>
                        <p class="text-muted small mt-1">Enter your credentials to access your account.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form id="loginForm" method="POST" action="auth.php">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="login_identifier" class="form-control" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="login_password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('loginPassword', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label text-muted small" for="rememberMe">Remember Me</label>
                            </div>
                            <a href="#" class="small fw-semibold" style="color: #0284c7;">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-action w-100 mb-3">LOG IN</button>
                    </form>
                </div>

                <!-- SIGN UP -->
                <div class="tab-pane fade <?php echo !empty($signupError) || !empty($signupSuccess) ? 'show active' : ''; ?>" id="signup-pane" role="tabpanel">
                    <div class="mb-4 text-center">
                        <h4 class="fw-bold m-0">Create Account</h4>
                        <p class="text-muted small mt-1">Start your water delivery with H2O2U.</p>
                    </div>

                    <?php if (!empty($signupError)): ?>
                        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($signupError); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($signupSuccess)): ?>
                        <div class="alert alert-success py-2"><?php echo htmlspecialchars($signupSuccess); ?></div>
                    <?php endif; ?>

                    <form id="signupForm" method="POST" action="">
                        <input type="hidden" name="action" value="signup">

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" name="phone_number" class="form-control" placeholder="09-xxxxxxxxx" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" id="signupPassword" placeholder="Create password" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm Password <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" placeholder="Confirm password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-action w-100 mb-3">CREATE ACCOUNT</button>
                    </form>
                </div>

            </div>
        </div>
    </main>

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
            <a href="../homepage.html#products">Products</a>
            <a href="../user_orders.html">Order</a>
            <a href="#">Payment</a>
        </div>
        <div class="footer-column">
            <h3>ABOUT US</h3>
            <a href="../contact.html">Contact</a>
            <a href="../homepage.html#reviews">Reviews</a>
            <a href="#">Our story</a>
        </div>
        <div class="footer-column">
            <h3>INFORMATION</h3>
            <a href="../user_delivery.html">Delivery History</a>
            <a href="../voucher.html">Vouchers</a>
            <a href="../user_pf.html">User Profile</a>
        </div>
        <div class="copyright">&copy; 2026 All Right Reserved</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
