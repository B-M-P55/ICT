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

// 3. Fetch User Information for Sidebar Display
$userStmt = $pdo->prepare("SELECT * FROM tbl_user WHERE userID = :id LIMIT 1");
$userStmt->execute([':id' => $userID]);
$user = $userStmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$displayName = $user['first_name'] . ' ' . $user['last_name'];

// 4. Handle Password Update (POST Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = "Please fill in all password fields.";
        $messageType = "danger";
    } elseif (strlen($newPassword) < 6) {
        $message = "New password must be at least 6 characters long.";
        $messageType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New password and Confirm password do not match.";
        $messageType = "danger";
    } else {
        // Verify current password (supports password_verify or direct match if plain text)
        $passwordMatches = false;
        if (isset($user['password'])) {
            if (password_verify($currentPassword, $user['password']) || $currentPassword === $user['password']) {
                $passwordMatches = true;
            }
        } else {
            // Default check if column is missing or unhashed
            $passwordMatches = true; 
        }

        if (!$passwordMatches) {
            $message = "Incorrect current password.";
            $messageType = "danger";
        } else {
            // Check if password column exists before updating
            $columnCheck = $pdo->query("SHOW COLUMNS FROM tbl_user LIKE 'password'");
            if ($columnCheck->rowCount() === 0) {
                // Add password column dynamically if missing from tbl_user
                $pdo->exec("ALTER TABLE tbl_user ADD COLUMN password VARCHAR(255) NULL AFTER account_status");
            }

            // Hash new password and update
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE tbl_user SET password = :password WHERE userID = :id");
            $updated = $updateStmt->execute([
                ':password' => $newPasswordHash,
                ':id'       => $userID
            ]);

            if ($updated) {
                // Logout user so they sign in again with the new password
                session_destroy();
                header("Refresh: 2; URL=login.php");
                $message = "Password updated successfully! Redirecting to login page...";
                $messageType = "success";
            } else {
                $message = "Failed to update password. Please try again.";
                $messageType = "danger";
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
    <title>User Profile - Password</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/user_pw.css">
</head>

<body>
    <!-- MAIN PASSWORD PAGE -->
    <div class="profile-page">

        <!-- LEFT SIDEBAR -->
        <div class="sidebar">

            <!-- USER HEADER -->
            <div class="user-header">

                <!-- Water Logo -->
                <div class="water-logo">
                    <svg viewBox="0 0 60 70" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 3 C30 3 8 29 8 43 C8 57 18 66 30 66 C42 66 52 57 52 43 C52 29 30 3 30 3Z" fill="#179ED5"/>
                        <path d="M30 12 C30 12 15 32 15 43 C15 53 21 59 30 59 C39 59 45 53 45 43 C45 32 30 12 30 12Z" fill="white"/>
                        <path d="M30 20 C30 20 20 35 20 43 C20 49 24 53 30 53 C36 53 40 49 40 43 C40 35 30 20 30 20Z" fill="#179ED5"/>
                    </svg>
                </div>

                <!-- User text -->
                <div class="user-details">
                    <div class="user-name" id="displayHeaderName"><?= htmlspecialchars($displayName) ?></div>
                    <button type="button" class="view-profile-btn" id="viewProfileBtn" onclick="window.location.href='user_pf.php'">View Profile</button>
                </div>
            </div>

            <!-- SIDEBAR MENU -->
            <div class="sidebar-menu">

                <!-- My Profile -->
                <button type="button" class="sidebar-item" id="profileBtn" onclick="window.location.href='user_pf.php'">
                    My Profile
                </button>

                <!-- Password -->
                <button type="button" class="sidebar-item selected" id="passwordBtn">
                    <span>Password</span>
                    <span class="right-arrow">▶</span>
                </button>

                <!-- Notifications -->
                <button type="button" class="sidebar-item" id="notiBtn" onclick="window.location.href='user_noti.php'">
                    Notifications
                </button>

                <!-- My Order -->
                <button type="button" class="sidebar-item" id="orderBtn" onclick="window.location.href='user_orders.php'">
                    My Order
                </button>

                <!-- Delivery History -->
                <button type="button" class="sidebar-item" id="historyBtn" onclick="window.location.href='user_delivery.php'">
                    Delivery History
                </button>

                <!-- Back to Homepage -->
                <button type="button" class="back-button" id="backBtn" onclick="window.location.href='homepage.php'">
                    Back
                </button>

            </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="content">

            <!-- PASSWORD CONTENT -->
            <div id="passwordContent" class="password-content">
                <div class="password-card">

                    <!-- TITLE -->
                    <h2 class="password-title">Change Password</h2>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType ?> text-center fs-6 py-2 mb-3" role="alert">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORM -->
                    <form id="passwordForm" action="user_pw.php" method="POST">

                        <!-- CURRENT PASSWORD -->
                        <div class="password-row">
                            <label for="currentPassword">CURRENT PASSWORD</label>
                            <input type="password" name="current_password" id="currentPassword" class="password-input" required>
                        </div>

                        <!-- NEW PASSWORD -->
                        <div class="password-row">
                            <label for="newPassword">NEW PASSWORD</label>
                            <input type="password" name="new_password" id="newPassword" class="password-input" required>
                        </div>

                        <!-- CONFIRM PASSWORD -->
                        <div class="password-row">
                            <label for="confirmPassword">CONFIRM PASSWORD</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="password-input" required>
                        </div>

                        <!-- SAVE -->
                        <div class="password-save-area">
                            <button type="submit" class="password-save-button">SAVE CHANGES</button>
                        </div>

                        <!-- NOTE -->
                        <div class="password-note">
                            YOU WILL BE ASKED TO LOG IN AGAIN WITH YOUR NEW PASSWORD AFTER YOU SAVE YOUR CHANGES.
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>