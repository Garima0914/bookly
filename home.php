<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$message = []; // Initialize $message as an empty array

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Function to display messages (no changes needed)
function displayMessage($message) {
    if (!empty($message) && is_array($message)) { // Check if $message is an array and not empty
        foreach ($message as $msg) {
            echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
        }
    }
}

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

        // Insert the booking into the database
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
    <title>Bookly - Online Store</title>

    <!-- font cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- custom css file link  -->
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
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<?php displayMessage($message); ?> 

<section class="home">
    <div class="content">
        <h3>Hand Picked Books to your door.</h3>
        <p>Venture beyond your usual reading preferences and discover hidden gems in genres you've never explored before, broadening your literary horizons.</p>
        <a href="about.php" class="white-btn">discover more</a>
    </div>
</section>


<section class="products">
    <h1 class="title">Newest Arrivals</h1>
    <div class="box-container">
        <?php
        $select_newest_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 6") or die('query failed');
        if (mysqli_num_rows($select_newest_products) > 0) {
            while ($fetch_newest_products = mysqli_fetch_assoc($select_newest_products)) {
                ?>
                <div class="box">
                    <a href="product_detail.php?product_id=<?php echo $fetch_newest_products['id']; ?>">
                        <img class="image" src="uploaded_img/<?php echo $fetch_newest_products['image']; ?>" alt="Product Image">
                        <div class="name"> <?php echo htmlspecialchars($fetch_newest_products['name']); ?> </div>
                    </a>
                    <div class="price">Rs<?php echo number_format($fetch_newest_products['price']); ?>/-</div>
                    <div class="available-stock">Stock Available: <?php echo intval($fetch_newest_products['quantity']); ?></div>
                    <form action="" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $fetch_newest_products['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_newest_products['name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_newest_products['price']); ?>">
                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_newest_products['image']); ?>">
                        <input type="hidden" name="product_quantity" value="<?php echo intval($fetch_newest_products['quantity']); ?>">

                        <div class="button-container">
                            <?php
                            // Check if the product is booked
                            $book_check = mysqli_query($conn, "SELECT * FROM bookings WHERE product_name = '" . mysqli_real_escape_string($conn, $fetch_newest_products['name']) . "' AND status = 'pending'") or die('query failed');
                            $is_booked = mysqli_num_rows($book_check) > 0;

                            if ($fetch_newest_products['quantity'] > 0) {
                                // If stock is available
                                ?>
                                <input type="number" min="1" max="<?php echo intval($fetch_newest_products['quantity']); ?>" name="order_quantity" value="1" class="qty">
                                <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
                                <?php
                            } else {
                                // If out of stock, show "Booked" if already booked, otherwise "Book Now"
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
            echo '<p class="empty">No new arrivals added yet!</p>';
        }
        ?>
    </div>
</section>


<section class="products">
    <h1 class="title">Best Seller</h1>
    <div class="box-container">
        <?php
        // Fetch all completed orders
        $select_orders = mysqli_query($conn, "SELECT total_products FROM `orders` WHERE payment_status = 'completed' LIMIT 6") or die('query failed');

        // Initialize an array to store total quantities sold for each product
        $product_sales = [];

        if (mysqli_num_rows($select_orders) > 0) {
            while ($fetch_order = mysqli_fetch_assoc($select_orders)) {
                // Extract product names and quantities from the total_products field
                preg_match_all('/(.*?)\((\d+)\)/', $fetch_order['total_products'], $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $index => $product_name) {
                        $product_name = trim($product_name); // Remove extra spaces
                        $quantity = (int)$matches[2][$index]; // Get the quantity sold

                        // Add the quantity to the product's total sales
                        if (isset($product_sales[$product_name])) {
                            $product_sales[$product_name] += $quantity;
                        } else {
                            $product_sales[$product_name] = $quantity;
                        }
                    }
                }
            }
        }

        // Sort products by total sales in descending order
        arsort($product_sales);

        // Fetch the top 6 best-selling products
        $top_products = array_slice($product_sales, 0, 6, true);

        if (!empty($top_products)) {
            foreach ($top_products as $product_name => $total_sold) {
                // Fetch product details from the database
                $select_product = mysqli_query($conn, "SELECT * FROM `products` WHERE name = '$product_name'") or die('query failed');
                if (mysqli_num_rows($select_product) > 0) {
                    $fetch_product = mysqli_fetch_assoc($select_product);
                    ?>
                    <div class="box">
                        <a href="product_detail.php?product_id=<?php echo $fetch_product['id']; ?>">
                            <img class="image" src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="Product Image">
                            <div class="name"><?php echo htmlspecialchars($fetch_product['name']); ?></div>
                        </a>
                        <div class="price">Rs<?php echo number_format($fetch_product['price']); ?>/-</div>
                        <div class="available-stock">Total Sold: <?php echo $total_sold; ?></div>
                        <form action="" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $fetch_product['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_product['name']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_product['price']); ?>">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_product['image']); ?>">
                            <input type="hidden" name="product_quantity" value="<?php echo intval($fetch_product['quantity']); ?>">

                            <div class="button-container">
                                <?php
                                // Check if the product is booked
                                $book_check = mysqli_query($conn, "SELECT * FROM bookings WHERE product_name = '" . mysqli_real_escape_string($conn, $fetch_product['name']) . "' AND status = 'pending'") or die('query failed');
                                $is_booked = mysqli_num_rows($book_check) > 0;

                                if ($fetch_product['quantity'] > 0) {
                                    // If stock is available
                                    ?>
                                    <input type="number" min="1" max="<?php echo intval($fetch_product['quantity']); ?>" name="order_quantity" value="1" class="qty">
                                    <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
                                    <?php
                                } else {
                                    // If out of stock, show "Booked" if already booked, otherwise "Book Now"
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
            }
        } else {
            echo '<p class="empty">No best-selling products found!</p>';
        }
        ?>
    </div>
    <div class="load-more" style="margin-top: 2rem; text-align:center">
        <a href="shop.php" class="option-btn">Load More</a>
    </div>
</section>


<section class="about">
    <div class="flex">
        <div class="image">
            <img src="images/about-img.jpg" alt="">
        </div>
        <div class="content">
            <h3>About Us</h3>
            <p>Welcome to Bookly where you can browse our extensive library, discover new authors, and enjoy an enriched reading experience.</p>
            <a href="about.php" class="btn">Read More</a>
        </div>
    </div>
</section>

<section class="home-contact">
    <div class="content">
        <h3>Have Any Questions?</h3>
        <p>If you require further assistance, please</p>
        <a href="contact.php" class="white-btn">Contact Us</a>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>