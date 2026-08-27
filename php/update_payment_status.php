<?php

header("Content-Type: application/json");

require_once "db_connect.php";


/* =========================================
   ONLY ALLOW POST REQUEST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/* =========================================
   GET DATA
========================================= */

$paymentID = isset($_POST["paymentID"])
    ? (int) $_POST["paymentID"]
    : 0;

$status = isset($_POST["status"])
    ? trim($_POST["status"])
    : "";


/* =========================================
   VALIDATE PAYMENT ID
========================================= */

if ($paymentID <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid payment ID."
    ]);

    exit;
}


/* =========================================
   VALIDATE STATUS
========================================= */

$allowedStatuses = [
    "pending",
    "completed",
    "unpaid"
];

if (!in_array($status, $allowedStatuses)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid payment status."
    ]);

    exit;
}


/* =========================================
   CHECK PAYMENT EXISTS
========================================= */

$checkPayment = $conn->prepare(
    "SELECT paymentID
     FROM tbl_payment
     WHERE paymentID = ?"
);

$checkPayment->bind_param(
    "i",
    $paymentID
);

$checkPayment->execute();

$result = $checkPayment->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Payment not found."
    ]);

    $checkPayment->close();

    exit;
}

$checkPayment->close();


/* =========================================
   UPDATE PAYMENT STATUS
========================================= */

$stmt = $conn->prepare(
    "UPDATE tbl_payment
     SET payment_status = ?
     WHERE paymentID = ?"
);

$stmt->bind_param(
    "si",
    $status,
    $paymentID
);


/* =========================================
   EXECUTE UPDATE
========================================= */

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Payment status updated successfully.",
        "paymentID" => $paymentID,
        "status" => $status
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to update payment status."
    ]);
}


$stmt->close();

$conn->close();