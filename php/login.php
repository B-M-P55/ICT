<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Check User Table
    $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE email = ? AND account_status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['userID']    = $user['userID'];
        $_SESSION['role']      = 'user';
        $_SESSION['user_name'] = $user['first_name'];
        header("Location: user_pf.html");
        exit();
    }

    // 2. Check Admin Table
    $stmt = $pdo->prepare("SELECT * FROM tbl_admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['adminID']    = $admin['adminID'];
        $_SESSION['role']       = 'admin';
        $_SESSION['admin_name'] = $admin['name'];
        header("Location: admin_order.html");
        exit();
    }

    echo "<script>alert('Invalid Email or Password'); window.location.href='login.html';</script>";
}
?>