<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypts password
    $phone     = trim($_POST['phone_number']);
    $address   = trim($_POST['address']);

    // Check if email is taken
    $checkEmail = $pdo->prepare("SELECT userID FROM tbl_user WHERE email = ?");
    $checkEmail->execute([$email]);

    if ($checkEmail->fetch()) {
        echo "<script>alert('Email already registered!'); window.location.href='register.html';</script>";
        exit();
    }

    // Insert user into tbl_user
    $sql = "INSERT INTO tbl_user (first_name, last_name, email, password, phone_number, address, account_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$firstName, $lastName, $email, $password, $phone, $address])) {
        echo "<script>alert('Registration Successful!'); window.location.href='login.html';</script>";
    }
}
?>