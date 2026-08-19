<?php

$conn = mysqli_connect("localhost", "root", "", "h2o2u_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
echo "connected";
?>