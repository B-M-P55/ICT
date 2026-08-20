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
   OPTIONAL USER ID
========================================= */

$userID = isset($_GET["userID"])
    ? (int) $_GET["userID"]
    : 0;


/* =========================================
   GET PAYMENT HISTORY
========================================= */

if ($userID > 0) {

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
            CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
            d.status AS delivery_status
         FROM tbl_payment p
         INNER JOIN tbl_order o
            ON p.order_ID = o.order_ID
         INNER JOIN tbl_user u
            ON o.userID = u.userID
         LEFT JOIN tbl_delivery d
            ON p.order_ID = d.orderID
         WHERE o.userID = ?
         ORDER BY p.payment_date DESC"
    );

    $stmt->bind_param(
        "i",
        $userID
    );

} else {

    /*
       Admin view:
       Get payments from all customers.
    */

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
            CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
            d.status AS delivery_status
         FROM tbl_payment p
         INNER JOIN tbl_order o
            ON p.order_ID = o.order_ID
         INNER JOIN tbl_user u
            ON o.userID = u.userID
         LEFT JOIN tbl_delivery d
            ON p.order_ID = d.orderID
         ORDER BY p.payment_date DESC"
    );
}


/* =========================================
   EXECUTE
========================================= */

$stmt->execute();

$result = $stmt->get_result();


/* =========================================
   STORE PAYMENTS
========================================= */

$payments = [];

while ($row = $result->fetch_assoc()) {

    $payments[] = $row;
}


/* =========================================
   RETURN RESPONSE
========================================= */

echo json_encode([
    "success" => true,
    "payments" => $payments
]);


$stmt->close();

$conn->close();