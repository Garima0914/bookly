<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// Function to display messages
function displayMessage($message) {
    if (!empty($message)) {
        echo '<div class="message">' . htmlspecialchars($message) . '</div>';
    }
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (empty($user_id)) {
        header('location:login.php');
        exit();
    }

    // Sanitize and validate inputs
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = floatval($_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = intval($_POST['product_quantity']);

    // Check available stock
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product_data = $product_result->fetch_assoc();

    if ($product_data) {
        $available_stock = intval($product_data['quantity']);

        if ($product_quantity > $available_stock) {
            $message[] = "Only $available_stock items are available for $product_name.";
        } else {
            // Check if product is already in the cart
            $stmt = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
            $stmt->bind_param("si", $product_name, $user_id);
            $stmt->execute();
            $check_cart_result = $stmt->get_result();

            if ($check_cart_result->num_rows > 0) {
                $message[] = 'Product already added to the cart!';
            } else {
                // Add product to the cart without updating the stock
                $stmt = $conn->prepare("INSERT INTO cart (user_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isdis", $user_id, $product_name, $product_price, $product_quantity, $product_image);
                $stmt->execute();

                $message[] = 'Product added to the cart!';
            }
        }
    } else {
        $message[] = 'Product not found!';
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Page</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .message {
            background-color: #ffefef;
            color: #d9534f;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #d9534f;
            border-radius: 5px;
        }
        .booked-button {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>Search Page</h3>
    <p><a href="home.php">Home</a> / Search</p>
</div>

<section class="search-form">
    <form action="" method="post">
        <input type="text" name="search" placeholder="Search products..." class="box" required>
        <input type="submit" name="submit" value="Search" class="btn">
    </form>
</section>

<section class="products" style="padding-top: 0;">
    <div class="box-container">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $search_item = mysqli_real_escape_string($conn, $_POST['search']); // Sanitize search input

            // Validate search term
            if (strlen($search_item) < 3 || !preg_match('/\w+/', $search_item)) {
                // If the search term is too short or doesn't form a word
                echo '<p class="empty">Please be more specific!</p>';
            } else {
                // Search in the products table
                $select_products = mysqli_query($conn, "SELECT * FROM products WHERE name LIKE '%$search_item%'") or die('Query failed');

                if (mysqli_num_rows($select_products) > 0) {
                    while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                        $available_stock = intval($fetch_product['quantity']);
                        $product_name = htmlspecialchars($fetch_product['name']);

                        // Check if the product is already booked by the user and not fulfilled
                        $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND product_name = ? AND status = 'pending'");
                        $stmt->bind_param("is", $user_id, $product_name);
                        $stmt->execute();
                        $check_booking_result = $stmt->get_result();
                        $is_booked = $check_booking_result->num_rows > 0;
                        ?>
                        <form action="" method="post" class="box">
                            <a href="product_detail.php?product_id=<?php echo $fetch_product['id']; ?>">
                                <img src="uploaded_img/<?php echo htmlspecialchars($fetch_product['image']); ?>" alt="Product Image" class="image">
                            </a>
                            <div class="name">
                                <a href="product_detail.php?product_id=<?php echo $fetch_product['id']; ?>"><?php echo $product_name; ?></a>
                            </div>
                            <div class="price">Rs. <?php echo number_format($fetch_product['price']); ?>/-</div>
                            <div class="stock">Stock Available: <?php echo $available_stock; ?></div>
                            <input type="hidden" name="product_name" value="<?php echo $product_name; ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_product['price']); ?>">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_product['image']); ?>">
                            <?php if ($available_stock > 0): ?>
                                <input type="number" class="qty" name="product_quantity" min="1" max="<?php echo $available_stock; ?>" value="1" required>
                                <input type="submit" class="btn" value="Add to Cart" name="add_to_cart">
                            <?php else: ?>
                                <?php
                                // Check if the logged-in user has already booked this product
                                $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND product_name = ? AND status = 'pending'");
                                $stmt->bind_param("is", $user_id, $product_name);
                                $stmt->execute();
                                $check_booking_result = $stmt->get_result();
                                $is_booked = $check_booking_result->num_rows > 0;

                                if ($is_booked): ?>
                                    <button type="button" class="btn booked-button" disabled>Booked</button>
                                <?php else: ?>
                                    <input type="submit" class="btn" value="Book Now" name="book_product">
                                <?php endif; ?>
                            <?php endif; ?>
                        </form>
                        <?php
                    }
                } else {
                    echo '<p class="empty">No results found!</p>';
                }
            }
        } else {
            echo '<p class="empty">Search for something!</p>';
        }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>

</body>
</html>