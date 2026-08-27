<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$result = $conn->query(
    "SELECT productID, product_name, size, price, stock,
            COALESCE(image_path, 'productImage/water_one.jpg') AS image_path
     FROM tbl_product
     WHERE is_active = 1
     ORDER BY productID ASC
     LIMIT 2"
);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
$conn->close();
