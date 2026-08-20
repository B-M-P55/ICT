<?php

$url = "http://localhost/ICT/php/update_payment_status.php";

$data = [
    "paymentID" => 1,
    "status" => "completed"
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