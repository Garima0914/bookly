<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';
session_start();

$message = [];

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// Function to display messages
function displayMessage($message) {
    if (!empty($message)) {
        echo '<div class="message">' . htmlspecialchars($message) . '</div>';
    }
}

// Add to cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (empty($user_id)) {
        header('location:login.php');
        exit();
    }

    // Sanitize and retrieve form inputs
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = floatval($_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = intval($_POST['product_quantity']);
    $order_quantity = intval($_POST['order_quantity']);

    // Check if the product is already in the cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
    $stmt->bind_param('si', $product_name, $user_id);
    $stmt->execute();
    $check_cart_result = $stmt->get_result();

    if ($check_cart_result->num_rows > 0) {
        $message[] = 'Already added to cart!';
    } elseif ($order_quantity > $product_quantity) {
        $message[] = 'Not enough stock available!';
    } else {
        // Add the product to the cart without updating the stock
        $stmt = $conn->prepare("INSERT INTO cart (user_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('isdis', $user_id, $product_name, $product_price, $order_quantity, $product_image);
        $stmt->execute();

        $message[] = 'Product added to cart!';
    }
}

// Handle Book Product
if (isset($_POST['book_product'])) {
    if (!isset($_SESSION['user_id'])) {
        header('location:login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);

    $user_name_query = mysqli_query($conn, "SELECT name, email FROM users WHERE id = '$user_id'") or die('Query failed');
    if ($user_name_result = mysqli_fetch_assoc($user_name_query)) {
        $user_name = $user_name_result['name'];
        $user_email = $user_name_result['email'];

        mysqli_query($conn, "INSERT INTO bookings(user_id, product_name, product_price, product_image, status, created_at) VALUES('$user_id', '$product_name', '$product_price', '$product_image', 'pending', NOW())") or die('Query failed');

        // Include the sendMail function
        require_once 'sendmail.php';

        // Prepare the booking confirmation email
        $subject = "Product Booking Confirmation - Bookly";
        $body = "
            <h2>Dear $user_name,</h2>
            <p>You have successfully booked the product: <strong>$product_name</strong>.</p>
            <p><strong>Booking Details:</strong></p>
            <p><strong>Product Name:</strong> $product_name</p>
            <p><strong>Product Price:</strong> Rs $product_price</p>
            <p><strong>Status:</strong> Pending</p>
            <p>We will notify you once the product is back in stock.</p>
            <p>Thank you!</p>
            <p><strong>Bookly Team</strong></p>
        ";

        // Send email notification
        if (sendMail($user_email, $subject, $body)) {
            $message[] = 'Product booked successfully! We will notify you once the product is back in stock.';
        } else {
            $message[] = 'Failed to send booking confirmation email. Please try again.';
        }
    } else {
        $message[] = "Error: Could not retrieve user details.";
    }
}
// Fetch selected category ID from URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Pagination settings
$products_per_page = 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $products_per_page;

if ($category_id > 0) {
    // Fetch products by category with pagination
    $select_products_query = "SELECT p.* 
                              FROM products AS p
                              INNER JOIN product_categories AS pc ON p.id = pc.product_id
                              WHERE pc.category_id = $category_id
                              LIMIT $products_per_page OFFSET $offset";
} else {
    $select_products_query = "SELECT * FROM products LIMIT $products_per_page OFFSET $offset";
}

$select_products_result = mysqli_query($conn, $select_products_query) or die('Query failed: ' . mysqli_error($conn));

// Get total product count for pagination
$total_products_query = "SELECT COUNT(*) as total FROM products";
$total_products_result = mysqli_query($conn, $total_products_query);
$total_products = mysqli_fetch_assoc($total_products_result)['total'];
$total_pages = ceil($total_products / $products_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .stock-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .button-container {
            margin-top: auto;
        }

        .btn.out-of-stock {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .box .image {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .box .name, .box .price, .box .available-stock {
            margin: 5px 0;
        }

        .box .qty {
            width: 60px;
            padding: 5px;
            margin: 10px 0;
        }

        .box .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .box .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .available-stock {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .heading {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #ddd;
        }

        .heading h3 {
            margin: 0;
            font-size: 24px;
        }

        .heading p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>Our Shop</h3>
    <p> <a href="home.php">Home</a> / Shop </p>
</div>

<section class="products">
    <h1 class="title">Products</h1>
    <div class="box-container">
        <?php  
        if ($select_products_result->num_rows > 0) {
            while ($fetch_products = $select_products_result->fetch_assoc()) {
        ?>
        <div class="box">
    <a href="product_detail.php?product_id=<?php echo intval($fetch_products['id']); ?>">
        <img class="image" src="uploaded_img/<?php echo htmlspecialchars($fetch_products['image']); ?>" alt="Product Image">
        <div class="name"><?php echo htmlspecialchars($fetch_products['name']); ?></div>
    </a>
    <div class="price">Rs<?php echo number_format($fetch_products['price']); ?>/-</div>
    <div class="available-stock">Stock Available: <?php echo intval($fetch_products['quantity']); ?></div>
    <form action="" method="post">
        <input type="hidden" name="product_id" value="<?php echo intval($fetch_products['id']); ?>">
        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_products['name']); ?>">
        <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_products['price']); ?>">
        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_products['image']); ?>">
        <input type="hidden" name="product_quantity" value="<?php echo intval($fetch_products['quantity']); ?>">
        <div class="button-container">
        <?php
    // Check if the logged-in user has already booked this product
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND product_name = ? AND status = 'pending'");
    $stmt->bind_param("is", $user_id, $fetch_products['name']);
    $stmt->execute();
    $check_booking_result = $stmt->get_result();
    $is_booked = $check_booking_result->num_rows > 0;

    if ($fetch_products['quantity'] > 0) {
        // If stock is available
        ?>
        <input type="number" min="1" max="<?php echo intval($fetch_products['quantity']); ?>" name="order_quantity" value="1" class="qty">
        <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
        <?php
    } else {
        // If out of stock, show "Booked" if already booked by the user, otherwise "Book Now"
        if ($is_booked) {
            echo '<input type="submit" value="Booked" class="btn out-of-stock" disabled>';
        } else {
            echo '<input type="submit" value="Book Now" name="book_product" class="btn out-of-stock">';
        }
    }
    ?>
        </div>
    </form>
</div>

        <?php
            }
        } else {
            echo '<p class="empty">No products found for this category!</p>';
        }
        ?>
    </div>
</section>

<div class="pagination">
    <?php if ($page > 1) { ?>
        <a href="?page=<?php echo $page - 1; ?>" class="btn" style="display: block; width: 200px; margin: 20px auto; padding: 10px; text-align: center; background: #28a745; color: white; font-size: 18px; text-decoration: none; border-radius: 5px;">Previous</a>
    <?php } ?>
    
    <?php if ($page < $total_pages) { ?>
        <a href="?page=<?php echo $page + 1; ?>" class="btn" style="display: block; width: 200px; margin: 20px auto; padding: 10px; text-align: center; background: #28a745; color: white; font-size: 18px; text-decoration: none; border-radius: 5px;">View More Products</a>
    <?php } ?>
</div>

<?php include 'footer.php'; ?>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>

</body>
</html>