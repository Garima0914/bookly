<?php
session_start();
include 'config.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/khalti_errors.log');

// 1. Verify required parameters exist
if (!isset($_GET['pidx']) || !isset($_GET['transaction_id']) || !isset($_GET['amount'])) {
    $_SESSION['payment_error'] = "Invalid payment response - missing parameters";
    error_log("Missing parameters: pidx, transaction_id, or amount");
    header("Location: payment.php");
    exit;
}

$pidx = $_GET['pidx'];
$transaction_id = $_GET['transaction_id'];
$amount = $_GET['amount'] / 100; // Convert from paisa to rupees

// 2. Verify payment with Khalti API
$verify_url = 'https://a.khalti.com/api/v2/epayment/lookup/';
$payload = json_encode(['pidx' => $pidx]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $verify_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455',
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 20,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 3. Handle verification errors
if ($error || $http_code !== 200) {
    $_SESSION['payment_error'] = "Payment verification failed. Please try again.";
    error_log("Khalti verification failed. HTTP $http_code: $error");
    header("Location: payment.php");
    exit;
}

$response_data = json_decode($response, true);
if (!$response_data || $response_data['status'] !== 'Completed') {
    $_SESSION['payment_error'] = "Payment not completed. Status: " . ($response_data['status'] ?? 'Unknown');
    error_log("Payment not completed. Response: " . print_r($response_data, true));
    header("Location: payment.php");
    exit;
}

// 4. Begin database transaction
mysqli_begin_transaction($conn);

try {
    // 5. Validate session data
    if (!isset($_SESSION['khalti_order']) || !isset($_SESSION['user_id'])) {
        throw new Exception("Required session data missing");
    }

    $order_data = $_SESSION['khalti_order'];
    $user_id = $_SESSION['user_id'];

    // 6. Insert order
    $insert_order_query = "INSERT INTO orders(
        user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status
    ) VALUES(
        '$user_id', 
        '" . mysqli_real_escape_string($conn, $order_data['name']) . "', 
        '" . mysqli_real_escape_string($conn, $order_data['number']) . "', 
        '" . mysqli_real_escape_string($conn, $order_data['email']) . "', 
        'khalti', 
        '" . mysqli_real_escape_string($conn, $order_data['address']) . "', 
        '" . mysqli_real_escape_string($conn, $order_data['total_products']) . "', 
        '" . $order_data['total_price'] . "', 
        '" . $order_data['placed_on'] . "',
        'completed'
    )";

    if (!mysqli_query($conn, $insert_order_query)) {
        throw new Exception("Order creation failed: " . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);

    // 7. Get cart items with proper product_id
    $cart_query = mysqli_query($conn, "SELECT c.*, p.id as product_id FROM cart c 
                                      JOIN products p ON c.name = p.name 
                                      WHERE c.user_id = '$user_id'");
    if (!$cart_query) {
        throw new Exception("Cart query failed: " . mysqli_error($conn));
    }

    $cart_items = [];
    while ($item = mysqli_fetch_assoc($cart_query)) {
        $cart_items[] = $item;
    }

    if (empty($cart_items)) {
        throw new Exception("Cart is empty");
    }

    // 8. Insert order items and update product quantities
    foreach ($cart_items as $item) {
        // Insert order item
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES ('$order_id', '{$item['product_id']}', '{$item['quantity']}', '{$item['price']}')";
        if (!mysqli_query($conn, $item_query)) {
            throw new Exception("Order item insertion failed: " . mysqli_error($conn));
        }

        // Update product stock
        $update_query = "UPDATE products SET quantity = quantity - {$item['quantity']} 
                         WHERE id = {$item['product_id']}";
        if (!mysqli_query($conn, $update_query)) {
            throw new Exception("Stock update failed: " . mysqli_error($conn));
        }
    }

    // 9. Clear cart
    $clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
    if (!mysqli_query($conn, $clear_cart)) {
        throw new Exception("Cart clearance failed: " . mysqli_error($conn));
    }

    // 10. Commit transaction
    mysqli_commit($conn);

    // 11. Include the sendMail function
    require_once 'sendmail.php';

    // 12. Prepare the order confirmation email
    $subject = "Order Confirmation - Bookly (Khalti Payment)";
    $body = "
        <h2>Dear {$order_data['name']},</h2>
        <p>Thank you for your order! Your payment was successful and your order has been confirmed.</p>
        
        <h3>Order Details:</h3>
        <p><strong>Order ID:</strong> $order_id</p>
        <p><strong>Order Date:</strong> {$order_data['placed_on']}</p>
        <p><strong>Payment Method:</strong> Khalti</p>
        <p><strong>Transaction ID:</strong> $transaction_id</p>
        <p><strong>Delivery Address:</strong> {$order_data['address']}</p>
        
        <h3>Order Summary:</h3>
        <p><strong>Products:</strong> {$order_data['total_products']}</p>
        <p><strong>Subtotal:</strong> Rs {$order_data['cart_total']}</p>
        <p><strong>Shipping Charge:</strong> Rs {$order_data['shipping_charge']}</p>
        <p><strong>Discount:</strong> -Rs {$order_data['discount']}</p>
        <p><strong>Total Amount:</strong> Rs {$order_data['total_price']}</p>
        
        <p>We will process your order shortly. You will receive another email when your order is shipped.</p>
        <p>If you have any questions, please contact us at support@bookly.com</p>
        
        <p>Thank you for shopping with us!</p>
        <p><strong>Bookly Team</strong></p>
    ";

    // 13. Send email notification
    if (sendMail($order_data['email'], $subject, $body)) {
        $_SESSION['message'] = 'Payment successful! Order placed successfully. A confirmation email has been sent to your email address.';
    } else {
        $_SESSION['message'] = 'Payment successful! Order placed successfully. Failed to send confirmation email.';
    }

    // 14. Clear session data
    unset($_SESSION['khalti_order']);
    unset($_SESSION['cart_count']);

    // 15. Redirect to home.php after successful order placement
    header('location:home.php');
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['payment_error'] = "Order processing failed: " . $e->getMessage();
    error_log("Order processing error: " . $e->getMessage());
    header("Location: payment.php");
    exit;
}
?>