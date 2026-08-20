<?php

header("Content-Type: application/json");

require_once "db.php";


/* =========================================
   ONLY ALLOW GET REQUEST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/* =========================================
   GET PAYMENT ID
========================================= */

$paymentID = isset($_GET["paymentID"])
    ? (int) $_GET["paymentID"]
    : 0;


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
   GET PAYMENT
========================================= */

$stmt = $conn->prepare(
    "SELECT
        p.paymentID,
        p.payment_amount,
        p.payment_date,
        p.payment_method,
        p.payment_status,
        p.payment_photo,
        p.order_ID,

        o.userID,

        CONCAT(
            u.first_name,
            ' ',
            u.last_name
        ) AS customer_name,

        u.phone_number,
        u.email,
        u.address,

        d.status AS delivery_status

     FROM tbl_payment p

     INNER JOIN tbl_order o
        ON p.order_ID = o.order_ID

     INNER JOIN tbl_user u
        ON o.userID = u.userID

     LEFT JOIN tbl_delivery d
        ON p.order_ID = d.orderID

     WHERE p.paymentID = ?"
);


$stmt->bind_param(
    "i",
    $paymentID
);


/* =========================================
   EXECUTE
========================================= */

$stmt->execute();

$result = $stmt->get_result();


/* =========================================
   CHECK RESULT
========================================= */

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Payment not found."
    ]);

    $stmt->close();
    $conn->close();

    exit;
}


/* =========================================
   GET DATA
========================================= */

$payment =
    $result->fetch_assoc();


/* =========================================
   RETURN RESPONSE
========================================= */

echo json_encode([
    "success" => true,
    "payment" => $payment
]);


$stmt->close();

$conn->close();

?>