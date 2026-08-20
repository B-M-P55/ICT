<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test KPay Payment</title>
</head>

<body>

<h2>Test KPay Payment</h2>

<form
    action="create_payment.php"
    method="POST"
    enctype="multipart/form-data"
>

    <label>Order ID:</label>
    <input
        type="number"
        name="order_ID"
        value="1"
        required
    >

    <br><br>

    <label>Payment Amount:</label>
    <input
        type="number"
        name="payment_amount"
        value="1000"
        required
    >

    <br><br>

    <input
        type="hidden"
        name="payment_method"
        value="Kpay"
    >

    <label>Payment Screenshot:</label>
    <input
        type="file"
        name="payment_photo"
        accept="image/*"
        required
    >

    <br><br>

    <button type="submit">
        Test KPay Payment
    </button>

</form>

</body>

</html>