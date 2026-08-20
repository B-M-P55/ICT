<?php


$host = "localhost";
$username = "root";
$password = "";
$database = "h2o2u_db";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$conn = mysqli_connect("localhost", "root", "", "h2o2u_db");

// if (!$conn) {
//     die("Connection Failed: " . mysqli_connect_error());
// }
// echo "connected";
?>

