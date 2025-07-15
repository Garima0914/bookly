<?php
session_start();
include 'config.php';

if (!isset($_SESSION['khalti_order'])) {
    header('Location: checkout.php');
    exit;
}

$order = $_SESSION['khalti_order'];
$amount_in_paisa = $order['total_price'] * 100;
$purchase_order_id = "ORD_" . $order['user_id'] . "_" . time();

$postFields = array(
    "return_url" => "http://localhost/project/khalti_callback.php?order_id=" . $purchase_order_id,
    "website_url" => "http://localhost/project/",
    "amount" => $amount_in_paisa,
    "purchase_order_id" => $purchase_order_id,
    "purchase_order_name" => "Order Payment #" . $purchase_order_id,
    "customer_info" => array(
        "name" => $order['name'],
        "email" => $order['email'],
        "phone" => $order['number']
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
        'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your live key
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
    // Store the purchase_order_id in session for verification later
    $_SESSION['khalti_purchase_order_id'] = $purchase_order_id;
    header('Location: ' . $responseArray['payment_url']);
    exit;
} else {
    $_SESSION['error'] = "Failed to initiate Khalti payment: " . ($responseArray['detail'] ?? 'Unknown error');
    header("Location: payment.php");
    exit;
}
?>