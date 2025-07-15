<?php
session_start();
include('config.php'); // Your database connection file
require_once 'sendmail.php'; // Your email sending function

// Check for required parameters from Khalti callback
if (!isset($_GET['pidx']) || !isset($_GET['purchase_order_id'])) {
    $_SESSION['error'] = "Invalid payment response from Khalti.";
    header("Location: home.php"); // Redirect to home or an error page
    exit;
}

$pidx = $_GET['pidx'];
$purchase_order_id_from_khalti = $_GET['purchase_order_id'];

// Retrieve pending order details from session that were stored before redirecting to Khalti
$khalti_pending_order = $_SESSION['khalti_pending_order'] ?? null;

if (!$khalti_pending_order || $khalti_pending_order['purchase_order_id'] !== $purchase_order_id_from_khalti) {
    $_SESSION['error'] = "Payment context lost or invalid purchase order ID.";
    header("Location: home.php");
    exit;
}

// Verify payment with Khalti
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your actual Khalti LIVE SECRET KEY
        'Content-Type: application/json',
    ),
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
));

$response = curl_exec($curl);
curl_close($curl);

if (!$response) {
    $_SESSION['error'] = "Failed to verify payment with Khalti. Please try again or contact support.";
    header("Location: home.php"); // Redirect to home with error
    exit;
}

$responseArray = json_decode($response, true);
$status = $responseArray['status'] ?? 'Failed';
$transaction_id = $responseArray['transaction_id'] ?? 'N/A'; // Khalti's transaction ID
$amount_paid_khalti = ($responseArray['total_amount'] ?? 0) / 100; // Convert from paisa to rupees

// Retrieve necessary data from session for order insertion/update
$user_id = $khalti_pending_order['user_id'];
$name = $khalti_pending_order['name'];
$number = $khalti_pending_order['number'];
$email = $khalti_pending_order['email'];
$method = $khalti_pending_order['method'];
$address = $khalti_pending_order['address'];
$total_products = $khalti_pending_order['total_products'];
$final_total = $khalti_pending_order['total_price'];
$placed_on = $khalti_pending_order['placed_on'];
$cart_total = $khalti_pending_order['cart_total'];
$shipping_charge = $khalti_pending_order['shipping_charge'];
$discount = $khalti_pending_order['discount'];

if ($status === 'Completed') {
    // Check if the amount matches the expected amount
    if ($amount_paid_khalti != $final_total) {
        $_SESSION['error'] = "Payment amount mismatch. Expected Rs $final_total, received Rs $amount_paid_khalti.";
        // Log this discrepancy for investigation
        header("Location: home.php");
        exit;
    }

    // Payment successful - record/update in database
    // IMPORTANT: In a real-world scenario, you would first insert a 'pending' order before
    // redirecting to Khalti, and then update its status here.
    // For this example, we will insert the order here.
    // Ensure your 'orders' table has 'payment_status' and 'transaction_id' columns.
    $insert_order_query = "INSERT INTO orders(
        user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, transaction_id
    ) VALUES(
        '$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$final_total', '$placed_on', 'Paid', '$transaction_id'
    )";

    if (mysqli_query($conn, $insert_order_query)) {
        // Clear the cart after successful order placement
        mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('Query Failed to clear cart');

        // Send order confirmation email
        $subject = "Order Confirmation - Bookly (Payment Successful)";
        $body = "
            <h2>Dear $name,</h2>
            <p>Your payment via Khalti was successful and your order has been confirmed!</p>

            <h3>Order Details:</h3>
            <p><strong>Order Date:</strong> $placed_on</p>
            <p><strong>Payment Method:</strong> " . ucwords($method) . "</p>
            <p><strong>Khalti Transaction ID:</strong> $transaction_id</p>
            <p><strong>Delivery Address:</strong> $address</p>

            <h3>Order Summary:</h3>
            <p><strong>Products:</strong> $total_products</p>
            <p><strong>Subtotal:</strong> Rs $cart_total</p>
            <p><strong>Shipping Charge:</strong> Rs $shipping_charge</p>
            <p><strong>Discount:</strong> -Rs $discount</p>
            <p><strong>Total Amount Paid:</strong> Rs $final_total</p>

            <p>We will process your order shortly. You will receive another email when your order is shipped.</p>
            <p>If you have any questions, please contact us at support@bookly.com</p>

            <p>Thank you for shopping with us!</p>
            <p><strong>Bookly Team</strong></p>
        ";

        if (sendMail($email, $subject, $body)) {
            $_SESSION['message'] = "Payment successful via Khalti! Your order has been confirmed. A confirmation email has been sent.";
        } else {
            $_SESSION['message'] = "Payment successful via Khalti! Your order has been confirmed, but failed to send confirmation email.";
        }

        // Clear the pending order session variable
        unset($_SESSION['khalti_pending_order']);

        header("Location: orders.php"); // Redirect to home page
        exit;
    } else {
        $_SESSION['error'] = "Payment verified but failed to record order in database: " . mysqli_error($conn);
        header("Location: home.php"); // Redirect to home with database error
        exit;
    }

} else {
    // Payment verification failed or status is not 'Completed'
    $_SESSION['error'] = "Khalti payment verification failed. Status: " . $status . ". Transaction ID: " . $transaction_id;
    // You might want to log this failed attempt or redirect to a specific "payment failed" page.
    header("Location: home.php"); // Redirect to home with error
    exit;
}
?>