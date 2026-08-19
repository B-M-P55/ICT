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

// Check whether order_ID was provided
if (!isset($_GET["order_ID"])) {
    echo json_encode([
        "success" => false,
        "message" => "Order ID is required."
    ]);
    exit;
}

$order_ID = intval($_GET["order_ID"]);

// Get payment history for the order
$sql = "SELECT
            paymentID,
            payment_amount,
            payment_date,
            payment_method,
            payment_status,
            order_ID
        FROM tbl_payment
        WHERE order_ID = ?
        ORDER BY payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_ID);
$stmt->execute();

$result = $stmt->get_result();

$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode([
    "success" => true,
    "payments" => $payments
]);

$stmt->close();
$conn->close();

?>