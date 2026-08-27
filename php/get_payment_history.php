<?php

header("Content-Type: application/json");

require_once "db_connect.php";


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
   GET PAYMENT RECORDS
========================================= */

$sql = "
    SELECT
        p.paymentID,
        p.payment_amount,
        p.payment_date,
        p.payment_method,
        p.payment_status,
        p.payment_photo,
        p.order_ID,
        o.userID,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name

    FROM tbl_payment p

    INNER JOIN tbl_order o
        ON p.order_ID = o.order_ID

    INNER JOIN tbl_user u
        ON o.userID = u.userID

    ORDER BY p.payment_date DESC
";


$stmt = $conn->prepare($sql);


/* =========================================
   CHECK QUERY
========================================= */

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare payment query."
    ]);

    exit;
}


/* =========================================
   EXECUTE
========================================= */

if (!$stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve payment records."
    ]);

    $stmt->close();
    $conn->close();

    exit;
}


/* =========================================
   GET RESULTS
========================================= */

$result = $stmt->get_result();

$payments = [];


while ($row = $result->fetch_assoc()) {

    $payments[] = $row;

}


/* =========================================
   RETURN JSON
========================================= */

echo json_encode([
    "success" => true,
    "payments" => $payments
]);


$stmt->close();
$conn->close();

?>