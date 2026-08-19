<?php

header("Content-Type: application/json");

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "h2o2u_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

// Get all payment records
$sql = "SELECT
            paymentID,
            payment_amount,
            payment_date,
            payment_method,
            payment_status,
            order_ID
        FROM tbl_payment
        ORDER BY payment_date DESC";

$result = $conn->query($sql);

$payments = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }

    echo json_encode([
        "success" => true,
        "payments" => $payments
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve payment records."
    ]);
}

$conn->close();

?>