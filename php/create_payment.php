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
   GET PAYMENT DATA
========================================= */

$orderID = isset($_POST["order_ID"])
    ? (int) $_POST["order_ID"]
    : 0;

$paymentAmount = isset($_POST["payment_amount"])
    ? (int) $_POST["payment_amount"]
    : 0;

$paymentMethod = isset($_POST["payment_method"])
    ? trim($_POST["payment_method"])
    : "";


/* =========================================
   VALIDATE REQUIRED DATA
========================================= */

if ($orderID <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid order ID."
    ]);

    exit;
}


if ($paymentAmount <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid payment amount."
    ]);

    exit;
}


if (
    $paymentMethod !== "Kpay" &&
    $paymentMethod !== "Cash on Delivery"
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid payment method."
    ]);

    exit;
}


/* =========================================
   CHECK WHETHER ORDER EXISTS
========================================= */

$checkOrder = $conn->prepare(
    "SELECT order_ID
     FROM tbl_order
     WHERE order_ID = ?"
);

$checkOrder->bind_param(
    "i",
    $orderID
);

$checkOrder->execute();

$orderResult = $checkOrder->get_result();


if ($orderResult->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "The selected order does not exist."
    ]);

    $checkOrder->close();

    exit;
}

$checkOrder->close();


/* =========================================
   PAYMENT PHOTO
========================================= */

$paymentPhotoName = null;


/*
   KBZ Pay requires a payment screenshot.
*/

if ($paymentMethod === "Kpay") {

    if (
        !isset($_FILES["payment_photo"]) ||
        $_FILES["payment_photo"]["error"] !== UPLOAD_ERR_OK
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Payment screenshot is required for KPay."
        ]);

        exit;
    }


    /* =====================================
       CHECK FILE TYPE
    ===================================== */

    $allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    $fileType = mime_content_type(
        $_FILES["payment_photo"]["tmp_name"]
    );


    if (!in_array($fileType, $allowedTypes)) {

        echo json_encode([
            "success" => false,
            "message" => "Only JPG, PNG, and WEBP images are allowed."
        ]);

        exit;
    }


    /* =====================================
       CHECK FILE SIZE
       Maximum: 5 MB
    ===================================== */

    $maxFileSize = 5 * 1024 * 1024;

    if ($_FILES["payment_photo"]["size"] > $maxFileSize) {

        echo json_encode([
            "success" => false,
            "message" => "Payment screenshot must be smaller than 5 MB."
        ]);

        exit;
    }


    /* =====================================
       CREATE PAYMENT IMAGE FOLDER
    ===================================== */

    $uploadDirectory = "../images/payment/";

    if (!is_dir($uploadDirectory)) {

        mkdir(
            $uploadDirectory,
            0777,
            true
        );
    }


    /* =====================================
       CREATE UNIQUE FILE NAME
    ===================================== */

    $fileExtension =
        strtolower(
            pathinfo(
                $_FILES["payment_photo"]["name"],
                PATHINFO_EXTENSION
            )
        );

    $paymentPhotoName =
        "payment_" .
        uniqid() .
        "." .
        $fileExtension;


    $uploadPath =
        $uploadDirectory .
        $paymentPhotoName;


    /* =====================================
       MOVE UPLOADED FILE
    ===================================== */

    if (
        !move_uploaded_file(
            $_FILES["payment_photo"]["tmp_name"],
            $uploadPath
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Failed to upload payment screenshot."
        ]);

        exit;
    }
}


/* =========================================
   CREATE PAYMENT RECORD
========================================= */

$paymentStatus = "pending";


$stmt = $conn->prepare(
    "INSERT INTO tbl_payment
    (
        payment_amount,
        payment_method,
        payment_status,
        payment_photo,
        order_ID
    )
    VALUES (?, ?, ?, ?, ?)"
);


$stmt->bind_param(
    "isssi",
    $paymentAmount,
    $paymentMethod,
    $paymentStatus,
    $paymentPhotoName,
    $orderID
);


/* =========================================
   EXECUTE INSERT
========================================= */

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Payment created successfully.",
        "paymentID" => $stmt->insert_id
    ]);

} else {

    /*
       If database insertion fails after
       uploading the image, remove the image
       so we don't leave an unused file.
    */

    if (
        $paymentPhotoName !== null &&
        file_exists($uploadPath)
    ) {

        unlink($uploadPath);
    }


    echo json_encode([
        "success" => false,
        "message" => "Failed to create payment record."
    ]);
}


$stmt->close();

$conn->close();