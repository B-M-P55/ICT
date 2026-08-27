<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "h2";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to support international characters/symbols
$conn->set_charset("utf8mb4");
?>  