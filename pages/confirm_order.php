<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';  // Don’t forget to include DB for product price fetch

$user_id = $_SESSION['user_id'];

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['full_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $pincode = htmlspecialchars($_POST['pincode']);
    $payment = htmlspecialchars($_POST['payment_method']);
} else {
    header("Location: checkout.php");
    exit();
}

// ✅ Fetch cart items
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;

// ✅ Calculate total cost
foreach ($cart_items as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];

    // Get product price
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $subtotal = $product['price'] * $quantity;
    $total_cost += $subtotal;
}

// ✅ Clear cart after placing order (optional but recommended)
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f8f5;
            text-align: center;
            padding: 50px;
        }
        .tick {
            font-size: 80px;
            color: green;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: auto;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            margin-top: 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }
        .highlight {
            font-weight: bold;
            font-size: 1.2em;
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="box">
    <div class="tick">✔</div>
    <h2>Thank you, <?= $name ?>!</h2>
    <p>Your order has been placed successfully.</p>

    <h3>Shipping Details:</h3>
    <p><strong>Phone:</strong> <?= $phone ?></p>
    <p><strong>Address:</strong> <?= $address ?>, <?= $pincode ?></p>
    <p><strong>Payment Method:</strong> <?= $payment ?></p>
    <p class="highlight">💰 Total Amount: ₹<?= number_format($total_cost, 2) ?></p>

    <a href="../index.php" class="btn">Back to Home</a>
</div>

</body>
</html>
