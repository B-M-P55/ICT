<?php

$url = "http://localhost/ICT/php/create_payment.php";

$data = [
    "order_ID" => 1,
    "payment_amount" => 1000,
    "payment_method" => "Cash on Delivery"
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch);
} else {
    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
}

curl_close($ch);