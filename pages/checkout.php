<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items from database
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    echo "<h2>Your cart is empty.</h2>";
    echo "<a href='../index.php'>Go back to shopping</a>";
    exit;
}

// Fetch product details
$product_ids = array_column($cart_items, 'product_id');
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build a lookup array of cart quantities
$quantities = [];
foreach ($cart_items as $item) {
    $quantities[$item['product_id']] = $item['quantity'];
}

$total_cost = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 80%; margin: auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #eee; }
        .total { font-weight: bold; font-size: 1.2em; }
        .btn { padding: 10px 20px; background-color: green; color: white; border: none; cursor: pointer; }
        .center { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h1 class="center">Checkout</h1>

    <table>
        <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php foreach ($products as $product): 
            $quantity = $quantities[$product['id']];
            $subtotal = $quantity * $product['price'];
            $total_cost += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($product['name']); ?></td>
            <td><img src="../images/<?= htmlspecialchars($product['image']); ?>" width="50"></td>
            <td>$<?= number_format($product['price'], 2); ?></td>
            <td><?= $quantity ?></td>
            <td>$<?= number_format($subtotal, 2); ?></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="4" class="total">Total:</td>
            <td class="total">$<?= number_format($total_cost, 2); ?></td>
        </tr>
    </table>

    <h2 class="center">Shipping & Payment Information</h2>

<form method="POST" action="confirm_order.php" style="max-width: 600px; margin: 30px auto; background-color: #fdfdfd; padding: 30px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);">

    <label style="font-weight: bold; color: #333;">Full Name:</label><br>
    <input type="text" name="full_name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;"><br>

    <label style="font-weight: bold; color: #333;">Phone Number:</label><br>
    <input type="tel" name="phone" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;"><br>

    <label style="font-weight: bold; color: #333;">Delivery Address:</label><br>
    <textarea name="address" rows="3" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;"></textarea><br>

    <label style="font-weight: bold; color: #333;">PIN Code:</label><br>
    <input type="text" name="pincode" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;"><br>

    <label style="font-weight: bold; color: #333;">Payment Method:</label><br>
    <select name="payment_method" required style="width: 100%; padding: 12px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 20px;">
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="UPI">UPI</option>
        <option value="Card">Card</option>
    </select><br>

    <div style="text-align: center;">
        <button type="submit" class="btn" style="background-color: #28a745; font-size: 16px;">🛒 Place Order</button>
    </div>
</form>


</body>
</html>
