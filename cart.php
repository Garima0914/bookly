<?php
include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id) {
    header('location:login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        $cart_id = $_POST['cart_id'];
        $cart_quantity = $_POST['cart_quantity'];

        // Fetch product details for stock validation
        $select_cart_item = mysqli_query($conn, "SELECT * FROM cart WHERE id = '$cart_id'") or die('Query failed');
        $cart_item = mysqli_fetch_assoc($select_cart_item);

        $product_name = $cart_item['name'];
        $product_details = mysqli_query($conn, "SELECT * FROM products WHERE name = '$product_name'") or die('Query failed');
        $product = mysqli_fetch_assoc($product_details);

        if ($product['quantity'] == 0) {
            // Delete cart item if stock is 0
            mysqli_query($conn, "DELETE FROM cart WHERE id = '$cart_id'") or die('Query failed');
            $message[] = 'Product is out of stock and has been removed from your cart.';
        } elseif ($cart_quantity > $product['quantity']) {
            $message[] = 'Requested quantity exceeds stock availability!';
        } else {
            // Update cart quantity
            mysqli_query($conn, "UPDATE cart SET quantity = '$cart_quantity' WHERE id = '$cart_id'") or die('Query failed');
            $message[] = 'Cart quantity updated!';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$delete_id'") or die('Query failed');
    header('location:cart.php');
    exit();
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('Query failed');
    header('location:cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>Shopping Cart</h3>
    <p><a href="home.php">Home</a> / Cart</p>
</div>

<section class="shopping-cart">

    <h1 class="title">Products Added</h1>

    <div class="box-container">
        <?php
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'") or die('Query failed');

        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $product_name = $fetch_cart['name'];
                $product_details = mysqli_query($conn, "SELECT * FROM products WHERE name = '$product_name'") or die('Query failed');
                $product = mysqli_fetch_assoc($product_details);
        ?>
        <div class="box">
            <a href="cart.php?delete=<?php echo $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('Delete this item from the cart?');"></a>
            <a href="product_detail.php?product_id=<?php echo intval($product['id']); ?>">
                <img class="image" src="uploaded_img/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
                <div class="name"><?php echo htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            </a>
            
            <div class="price">Rs<?php echo $fetch_cart['price']; ?>/-</div>
            <div class="stock">Stock Available: <?php echo $product['quantity']; ?></div>
            <form action="" method="post">
                <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                <input type="number" min="1" max="<?php echo $product['quantity']; ?>" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>" class="qty">
                <input type="submit" name="update_cart" value="Update" class="option-btn">
            </form>
            <div class="sub-total">Subtotal: <span>Rs<?php echo $sub_total = $fetch_cart['quantity'] * $fetch_cart['price']; ?>/-</span></div>
        </div>
        <?php
                $grand_total += $sub_total;
            }
        } else {
            echo '<p class="empty">Your cart is empty</p>';
        }
        ?>
    </div>

    <div style="margin-top: 2rem; text-align:center;">
        <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all items from the cart?');">Delete All</a>
    </div>

    <div class="cart-total">
        <p>Grand Total: <span>Rs<?php echo $grand_total; ?>/-</span></p>
        <div class="flex">
            <a href="shop.php" class="option-btn">Continue Shopping</a>
            <a href="checkout.php" class="btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
        </div>
    </div>

</section>

<?php include 'footer.php'; ?>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>

</body>
</html>