<?php

header("Content-Type: application/json");

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "h2o2u_db";

$conn = new mysqli($host, $username, $password, $database);

// Check database connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

// Get JSON data sent from JavaScript
$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (
    !isset($data["payment_amount"]) ||
    !isset($data["payment_method"]) ||
    !isset($data["order_ID"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required payment information."
    ]);
    exit;
}

$payment_amount = $data["payment_amount"];
$payment_method = $data["payment_method"];
$order_ID = $data["order_ID"];

// Validate payment method
$allowed_methods = ["Kpay", "Cash on Delivery"];

if (!in_array($payment_method, $allowed_methods)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid payment method."
    ]);
    exit;
}

// Check whether the order exists
$order_check = $conn->prepare(
    "SELECT order_ID FROM tbl_order WHERE order_ID = ?"
);

$order_check->bind_param("i", $order_ID);
$order_check->execute();

$result = $order_check->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Order does not exist."
    ]);
    exit;
}

$order_check->close();

// Insert payment
$sql = "INSERT INTO tbl_payment
        (payment_amount, payment_method, payment_status, order_ID)
        VALUES (?, ?, 'pending', ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isi",
    $payment_amount,
    $payment_method,
    $order_ID
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Payment created successfully.",
        "paymentID" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create payment."
    ]);
}

$stmt->close();
$conn->close();

?>