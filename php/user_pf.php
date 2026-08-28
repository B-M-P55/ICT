<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['userID']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'] ?? $_SESSION['user_id'];

// 2. Database Connection
$dbHost = "localhost";
$dbName = "h2o2u_db";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = "";
$messageType = "";

// 3. Handle Form Submission (Update Profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName   = trim($_POST['first_name'] ?? '');
    $lastName    = trim($_POST['last_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $address     = trim($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($address)) {
        $message = "All fields are required.";
        $messageType = "danger";
    } else {
        // Check for duplicate email or phone belonging to another user
        $checkStmt = $pdo->prepare("SELECT userID FROM tbl_user WHERE (email = :email OR phone_number = :phone) AND userID != :id LIMIT 1");
        $checkStmt->execute([
            ':email' => $email,
            ':phone' => $phoneNumber,
            ':id'    => $userID
        ]);

        if ($checkStmt->rowCount() > 0) {
            $message = "Email or Phone number is already in use by another user.";
            $messageType = "danger";
        } else {
            // Update user record in tbl_user
            $updateStmt = $pdo->prepare("
                UPDATE tbl_user 
                SET first_name = :fname, 
                    last_name = :lname, 
                    email = :email, 
                    phone_number = :phone, 
                    address = :address 
                WHERE userID = :id
            ");

            $updated = $updateStmt->execute([
                ':fname'   => $firstName,
                ':lname'   => $lastName,
                ':email'   => $email,
                ':phone'   => $phoneNumber,
                ':address' => $address,
                ':id'      => $userID
            ]);

            if ($updated) {
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['user_email'] = $email;

                $message = "Profile updated successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to update profile. Please try again.";
                $messageType = "danger";
            }
        }
    }
}

// 4. Fetch User Information from tbl_user
$userStmt = $pdo->prepare("SELECT * FROM tbl_user WHERE userID = :id LIMIT 1");
$userStmt->execute([':id' => $userID]);
$user = $userStmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Defined Township Options
$townships = ['Insein', 'Hlaing', 'Mayangone'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - My Profile</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fixed CSS Path -->
    <link rel="stylesheet" href="../css/user_pf.css">
</head>

<body>

    <!-- MAIN PROFILE PAGE -->
    <div class="profile-page">

        <!-- LEFT SIDEBAR -->
        <div class="sidebar">

            <!-- USER HEADER -->
            <div class="user-header">

                <!-- Water Logo -->
                <div class="water-logo">
                    <svg viewBox="0 0 60 70" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 3 C30 3 8 29 8 43 C8 57 18 66 30 66 C42 66 52 57 52 43 C52 29 30 3 30 3Z" fill="#179ED5"/>
                        <path d="M30 12 C30 12 15 32 15 43 C15 53 21 59 30 59 C45 53 45 43 C45 32 30 12 30 12Z" fill="white"/>
                        <path d="M30 20 C30 20 20 35 20 43 C20 49 24 53 30 53 C36 53 40 49 40 43 C40 35 30 20 30 20Z" fill="#179ED5"/>
                    </svg>
                </div>

                <!-- User text -->
                <div class="user-details">
                    <div class="user-name" id="displayHeaderName"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                    <button type="button" class="view-profile-btn" id="viewProfileBtn">View Profile</button>
                </div>
            </div>

            <!-- SIDEBAR MENU -->
            <div class="sidebar-menu">

                <!-- My Profile -->
                <button type="button" class="sidebar-item selected" id="profileBtn">
                    <span>My Profile</span>
                    <span class="right-arrow">▶</span>
                </button>

                <!-- Password -->
                <button type="button" class="sidebar-item" id="passwordBtn" onclick="window.location.href='user_pw.php'">
                    Password
                </button>

                <!-- Notifications -->
                <button type="button" class="sidebar-item" id="notiBtn" onclick="window.location.href='user_noti.php'">
                    Notifications
                </button>

                <!-- My Order -->
                <button type="button" class="sidebar-item" id="orderBtn" onclick="window.location.href='voucher.php'">
                    My Order
                </button>

                <!-- Delivery History -->
                <button type="button" class="sidebar-item" id="historyBtn" onclick="window.location.href='user_delivery.php'">
                    Delivery History
                </button>

                <!-- Log Out -->
                <a href="logout.php" class="back-button text-center text-decoration-none" id="backBtn">
                    Log Out
                </a>

            </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="content">

            <!-- Profile Form -->
            <div id="profileContent">

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form id="profileForm" action="user_pf.php" method="POST">

                    <!-- FIRST NAME -->
                    <div class="form-row">
                        <label for="firstName">First Name</label>
                        <input type="text" name="first_name" id="firstName" class="profile-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>

                    <!-- LAST NAME -->
                    <div class="form-row">
                        <label for="lastName">Last Name</label>
                        <input type="text" name="last_name" id="lastName" class="profile-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>

                    <!-- TOWNSHIP / CITY DROPDOWN -->
                    <div class="form-row">
                        <label for="address">Township / City</label>
                        <div class="select-box">
                            <select name="address" id="address" class="profile-input" required>
                                <option value="">Select Location</option>
                                <?php foreach ($townships as $township): ?>
                                    <option value="<?= htmlspecialchars($township) ?>" <?= ($user['address'] === $township) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($township) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                    </div>

                    <!-- PHONE -->
                    <div class="form-row">
                        <label for="phone">Phone number</label>
                        <input type="text" name="phone_number" id="phone" class="profile-input" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
                    </div>

                    <!-- EMAIL -->
                    <div class="form-row">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="profile-input" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <!-- SAVE BUTTON -->
                    <div class="save-area">
                        <button type="submit" class="save-button">Save</button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/user_pf.js"></script>
</body>
</html>