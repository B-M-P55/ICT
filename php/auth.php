<?php
session_start();
require_once 'db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifier = trim($_POST['login_identifier'] ?? '');
    $password   = $_POST['login_password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {

        // CHECK ADMIN FIRST
        $adminStmt = $conn->prepare("SELECT adminID, name, email, password FROM tbl_admin WHERE email = ? LIMIT 1");
        $adminStmt->bind_param("s", $identifier);
        $adminStmt->execute();
        $adminResult = $adminStmt->get_result();

        if ($adminResult->num_rows === 1) {
            $admin = $adminResult->fetch_assoc();

            if ($password === $admin['password']) {
                $_SESSION['adminID']    = $admin['adminID'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['role']       = 'admin';

                header("Location: ../admin_order.html");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {

            // CHECK CUSTOMER
            $userStmt = $conn->prepare("SELECT userID, first_name, last_name, email, password FROM tbl_user WHERE email = ? AND account_status = 'active' LIMIT 1");
            $userStmt->bind_param("s", $identifier);
            $userStmt->execute();
            $userResult = $userStmt->get_result();

            if ($userResult->num_rows === 1) {
                $user = $userResult->fetch_assoc();

                if ($password === $user['password']) {
                    $_SESSION['userID']    = $user['userID'];
                    $_SESSION['user_name'] = $user['first_name'];
                    $_SESSION['role']      = 'user';

                    header("Location: ../homepage.html");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }

    $error = urlencode($error);
    header("Location: admin_login.php?error=" . $error);
    exit();
}
?>
