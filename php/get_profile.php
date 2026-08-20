<?php
session_start();
require_once 'db_connect.php';

// Redirect to login if user isn't logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.html");
    exit();
}

$userID = $_SESSION['userID'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone_number, address FROM tbl_user WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch();

// Returns details as JSON for your frontend JS
header('Content-Type: application/json');
echo json_encode($user);
?>