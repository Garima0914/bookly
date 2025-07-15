<?php
include 'config.php';
session_start();

$response = [];

if (!isset($_SESSION['user_id'])) {
    $response['success'] = false;
    $response['error'] = "Please log in to add products to your cart.";
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {
        $response['success'] = false;
        $response['error'] = "Invalid quantity.";
        echo json_encode($response);
        exit;
    }

    // Check if product exists
    $stmt = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Check stock
        if ($quantity > $product['quantity']) {
            $response['success'] = false;
            $response['error'] = "Insufficient stock.";
            echo json_encode($response);
            exit;
        }

        // Insert into cart or update if already in cart
        $stmt = $conn->prepare("INSERT INTO `cart` (user_id, product_id, quantity) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Product added to cart.";
        } else {
            $response['success'] = false;
            $response['error'] = "Failed to add product to cart.";
        }
    } else {
        $response['success'] = false;
        $response['error'] = "Product not found.";
    }

    echo json_encode($response);
    exit;
} else {
    $response['success'] = false;
    $response['error'] = "Invalid request method.";
    echo json_encode($response);
    exit;
}
?>
