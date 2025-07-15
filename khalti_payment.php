<?php
session_start();
include 'config.php';

// Check if order data exists
if (!isset($_SESSION['khalti_order'])) {
    header('Location: checkout.php');
    exit;
}

$order = $_SESSION['khalti_order'];
$amount_in_paisa = $order['total_price'] * 100; // Convert to paisa
$purchase_order_id = "ORD_" . $order['user_id'] . "_" . time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with Khalti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .btn-khalti {
            background-color: #5C2D91;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        .btn-khalti:hover {
            background-color: #4a2474;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <h2 class="text-center mb-4">Complete Your Payment</h2>
            
            <div class="order-summary">
                <h4>Order Summary</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Amount:</strong> Rs <?php echo number_format($order['total_price'], 2); ?></p>
                <p><strong>Items:</strong> <?php echo htmlspecialchars($order['total_products']); ?></p>
            </div>

            <form id="khalti-form" action="verify_khalti.php" method="post">
                <input type="hidden" name="amount" value="<?php echo $amount_in_paisa; ?>">
                <input type="hidden" name="purchase_order_id" value="<?php echo $purchase_order_id; ?>">
                <input type="hidden" name="purchase_order_name" value="Order #<?php echo $purchase_order_id; ?>">
                <button type="submit" class="btn-khalti">Pay with Khalti</button>
            </form>
        </div>
    </div>
</body>
</html>