<?php
include 'config.php';
session_start();

// Check if user is logged in and has address details
if (!isset($_SESSION['user_id']) || !isset($_SESSION['address_details'])) {
    header('location:checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve user details
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'") or die('Query Failed');
if (mysqli_num_rows($user_query) > 0) {
    $user_info = mysqli_fetch_assoc($user_query);
} else {
    header('location:logout.php');
    exit;
}

if (isset($_POST['order_btn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $address = mysqli_real_escape_string($conn,
        'Street: ' . $_SESSION['address_details']['street'] . ', ' .
        'City: ' . $_SESSION['address_details']['city'] . ', ' .
        'District: ' . $_SESSION['address_details']['state']
    );
    $placed_on = date('d-M-Y');

    $cart_total = 0;
    $cart_products = [];

    $cart_query = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'") or die('Query Failed');
    if (mysqli_num_rows($cart_query) > 0) {
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
            $sub_total = $cart_item['price'] * $cart_item['quantity'];
            $cart_total += $sub_total;

            // Update product stock
            $product_name = $cart_item['name'];
            $product_query = mysqli_query($conn, "SELECT quantity FROM products WHERE name = '$product_name'") or die('Query Failed');
            if (mysqli_num_rows($product_query) > 0) {
                $product_data = mysqli_fetch_assoc($product_query);
                $new_quantity = $product_data['quantity'] - $cart_item['quantity'];
                if ($new_quantity < 0) {
                    $new_quantity = 0;
                }
                mysqli_query($conn, "UPDATE products SET quantity = '$new_quantity' WHERE name = '$product_name'") or die('Query Failed');
            }
        }
    }

    // Define shipping charge based on payment method
    $shipping_charge = ($method == "cash on delivery") ? 100 : 60;
    $discount = 50;

    // Calculate final total
    $final_total = $cart_total + $shipping_charge - $discount;

    $total_products = implode(', ', $cart_products);

    if ($method === 'khalti') {
        $amount_in_paisa = $final_total * 100;
        // Use a unique purchase_order_id. This will be used to link back to the order.
        // For simplicity, using user_id and current timestamp. A more robust solution might
        // involve inserting a 'pending' order into the DB first and using its ID.
        $purchase_order_id = "BOOKLY_" . $user_id . "_" . time();

        // Prepare order details to be potentially saved as a pending order
        // and passed to Khalti. Note: We'll save the order to DB upon Khalti success.
        // For now, these details are for Khalti initiation and eventual DB insertion.
        $postFields = array(
            "return_url" => "http://localhost/project/payment_response_bookly.php?purchase_order_id=$purchase_order_id", // Make sure this URL is correct for your project
            "website_url" => "http://localhost/project/", // Make sure this URL is correct for your project
            "amount" => $amount_in_paisa,
            "purchase_order_id" => $purchase_order_id,
            "purchase_order_name" => "Bookly Order Payment #$purchase_order_id",
            "customer_info" => array(
                "name" => $name,
                "email" => $email,
                "phone" => $number
            )
        );

        $jsonData = json_encode($postFields);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your actual Khalti LIVE SECRET KEY
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $_SESSION['error'] = "Failed to initiate Khalti payment: " . $error;
            header("Location: payment.php");
            exit;
        }

        $responseArray = json_decode($response, true);

        if (isset($responseArray['payment_url'])) {
            // Store details in session temporarily to be retrieved in response page
            $_SESSION['khalti_pending_order'] = [
                'user_id' => $user_id,
                'name' => $name,
                'number' => $number,
                'email' => $email,
                'method' => $method,
                'address' => $address,
                'total_products' => $total_products,
                'total_price' => $final_total,
                'placed_on' => $placed_on,
                'purchase_order_id' => $purchase_order_id, // Store this to retrieve in response
                'cart_total' => $cart_total,
                'shipping_charge' => $shipping_charge,
                'discount' => $discount
            ];

            header('Location: ' . $responseArray['payment_url']);
            exit;
        } else {
            $_SESSION['error'] = "Failed to initiate Khalti payment: " . ($responseArray['detail'] ?? 'Unknown error');
            header("Location: payment.php");
            exit;
        }
    } else { // Cash on Delivery
        $transaction_id = uniqid('COD_'); // Unique ID for Cash on Delivery transactions
        $insert_order_query = "INSERT INTO orders(
            user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, transaction_id
        ) VALUES(
            '$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$final_total', '$placed_on', 'Pending', '$transaction_id'
        )";

        if (mysqli_query($conn, $insert_order_query)) {
            require_once 'sendmail.php';

            $subject = "Order Confirmation - Bookly";
            $body = "
                <h2>Dear $name,</h2>
                <p>Thank you for your order! Your order has been successfully placed.</p>

                <h3>Order Details:</h3>
                <p><strong>Order Date:</strong> $placed_on</p>
                <p><strong>Payment Method:</strong> " . ucwords($method) . "</p>
                <p><strong>Delivery Address:</strong> $address</p>

                <h3>Order Summary:</h3>
                <p><strong>Products:</strong> $total_products</p>
                <p><strong>Subtotal:</strong> Rs $cart_total</p>
                <p><strong>Shipping Charge:</strong> Rs $shipping_charge</p>
                <p><strong>Discount:</strong> -Rs $discount</p>
                <p><strong>Total Amount:</strong> Rs $final_total</p>

                <p>We will process your order shortly. You will receive another email when your order is shipped.</p>
                <p>If you have any questions, please contact us at support@bookly.com</p>

                <p>Thank you for shopping with us!</p>
                <p><strong>Bookly Team</strong></p>
            ";

            if (sendMail($email, $subject, $body)) {
                $_SESSION['message'] = 'Order placed successfully! A confirmation email has been sent to your email address.';
            } else {
                $_SESSION['message'] = 'Order placed successfully! Failed to send confirmation email.';
            }

            mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('Query Failed');

            header('location:orders.php');
            exit;
        } else {
            $_SESSION['error'] = "Failed to place order: " . mysqli_error($conn);
            header('location:payment.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        .payment-options {
            width: 55%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .summary {
            width: 40%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .payment-method {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        .method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .method:hover {
            border-color: #007BFF;
        }
        .method.active {
            border-color: #007BFF;
            background-color: #e7f1ff;
        }
        .method img {
            width: 50px;
            margin-right: 15px;
        }
        .method-content {
            flex-grow: 1;
        }
        .method-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .method-desc {
            color: #666;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: orange;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: darkorange;
        }
        .summary h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .summary p {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .summary .total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .payment-options, .summary {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="payment-options">
        <h1>Payment Options</h1>

        <form id="payment-form" action="" method="post">
            <input type="hidden" name="name" value="<?php echo $user_info['name']; ?>">
            <input type="hidden" name="number" value="<?php echo $user_info['phone']; ?>">
            <input type="hidden" name="email" value="<?php echo $user_info['email']; ?>">
            <input type="hidden" name="method" id="payment-method" value="cash on delivery">

            <div class="payment-method">
                <div class="method active" onclick="selectMethod(this, 'cash on delivery')">
                    <img src="images/cod.png" alt="Cash on Delivery">
                    <div class="method-content">
                        <div class="method-title">Cash on Delivery</div>
                        <div class="method-desc">Pay when you receive your order</div>
                    </div>
                </div>

                <div class="method" onclick="selectMethod(this, 'khalti')">
                    <img src="images/khalti.png" alt="Khalti">
                    <div class="method-content">
                        <div class="method-title">Pay with Khalti</div>
                        <div class="method-desc">Secure online payment</div>
                    </div>
                </div>
            </div>

            <button type="submit" name="order_btn" class="btn">Complete Order</button>
        </form>
    </div>

    <div class="summary">
        <h2>Order Summary</h2>
        <?php
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'") or die('Query Failed');
        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $total_price = $fetch_cart['price'] * $fetch_cart['quantity'];
                $grand_total += $total_price;
                echo "<p>{$fetch_cart['name']} <span>({$fetch_cart['price']} x {$fetch_cart['quantity']})</span></p>";
            }
        } else {
            echo "<p>Your cart is empty.</p>";
        }

        // Get selected payment method (default to COD initially, but client-side JS updates it)
        // For accurate display before submission, this would need AJAX or re-render based on JS selection.
        // For now, it will reflect the default or the last submitted method.
        $selected_method_display = isset($_POST['method']) ? $_POST['method'] : 'cash on delivery';
        $shipping_charge_display = ($selected_method_display == "cash on delivery") ? 100 : 60;
        $discount_display = 50;
        $final_total_display = $grand_total + $shipping_charge_display - $discount_display;
        ?>
        <p>Subtotal <span>Rs <?php echo $grand_total; ?></span></p>
        <p>Shipping <span>Rs <?php echo $shipping_charge_display; ?></span></p>
        <p>Discount <span>-Rs <?php echo $discount_display; ?></span></p>
        <p class="total">Total <span>Rs <?php echo $final_total_display; ?></span></p>
    </div>
</div>

<script>
function selectMethod(methodElement, methodValue) {
    // Remove active class from all methods
    document.querySelectorAll('.method').forEach(method => {
        method.classList.remove('active');
    });

    // Add active class to selected method
    methodElement.classList.add('active');

    // Update hidden input value
    document.getElementById('payment-method').value = methodValue;

    // Optional: You could update the summary section dynamically here with AJAX or JavaScript
    // For now, the summary will only update on page load or form submission.
    // To make the summary reflect the selected method without page refresh,
    // you would need to recalculate shipping_charge_display in JS and update the DOM elements.
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>