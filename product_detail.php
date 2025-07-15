<?php
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$message = [];

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// Validate product ID
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Fetch product details including categories
    $stmt = $conn->prepare("SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
        FROM products p 
        LEFT JOIN product_categories pc ON p.id = pc.product_id 
        LEFT JOIN categories c ON pc.category_id = c.id 
        WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
    } else {
        echo "<p>Product not found!</p>";
        exit;
    }
} else {
    echo "<p>Invalid product!</p>";
    exit;
}

// Handle Add to Cart
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['name']); ?> - Details</title>
  
  <!-- Font Awesome CDN link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- Custom CSS file link -->
  <link rel="stylesheet" href="css/style.css">
  <style>
      /* Product Details Page Styles */
      body {
          font-family: Arial, sans-serif;
          background-color: #f9f9f9;
          margin: 0;
          padding: 0;
      }
      .product-page {
          max-width: 800px;
          margin: 20px auto;
          background: white;
          padding: 20px;
          box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }
      .product-header h1 {
          font-size: 24px;
          color: #333;
      }
      .product-content {
          display: flex;
          flex-wrap: wrap;
          gap: 20px;
      }
      .left-panel {
          flex: 1;
          text-align: center;
      }
      .product-image {
          max-width: 100%;
          border: 1px solid #ddd;
          border-radius: 8px;
      }
      .right-panel {
          flex: 2;
      }
      .product-price {
          font-size: 20px;
          font-weight: bold;
          color: #333;
      }
      .product-description {
          margin-bottom: 10px;
      }
      .btn {
          display: inline-block;
          padding: 10px 15px;
          background-color: #ff6f61;
          color: white;
          border: none;
          border-radius: 4px;
          text-decoration: none;
          cursor: pointer;
      }
      .btn:hover {
          background-color: #ff5436;
      }
      .message {
          background-color: #ffefef;
          color: #d9534f;
          padding: 10px;
          margin: 10px 0;
          border: 1px solid #d9534f;
          border-radius: 5px;
          text-align: center;
      }
      /* Recommended Products Section */
      .products {
          margin-top: 40px;
      }
      .products .title {
          font-size: 24px;
          margin-bottom: 20px;
          text-align: center;
      }
      .recommendations-container {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 20px;
      }
      /* Responsive adjustments for recommendations */
      @media (max-width: 768px) {
          .recommendations-container {
              grid-template-columns: repeat(2, 1fr);
          }
      }
      @media (max-width: 480px) {
          .recommendations-container {
              grid-template-columns: 1fr;
          }
      }
      /* Use same box style as home.php but override width for recommendations */
      .recommendations-container .box {
          width: 100%;
      }
      .box {
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          padding: 15px;
          border: 1px solid #ddd;
          border-radius: 5px;
          text-align: center;
      }
      .box .image {
          max-width: 100%;
          height: auto;
          margin-bottom: 10px;
      }
      .box .name {
          font-size: 18px;
          margin-bottom: 5px;
          color: #333;
      }
      .box .price {
          font-size: 16px;
          color: #555;
          margin-bottom: 5px;
      }
      .box .available-stock {
          font-size: 14px;
          color: #777;
          margin-bottom: 10px;
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
  </style>
</head>
<body>
  <!-- Include Header -->
  <?php include 'header.php'; ?>

  <?php if (!empty($message) && is_array($message)): ?>
      <?php foreach ($message as $msg): ?>
          <div class="message"><?php echo htmlspecialchars($msg); ?></div>
      <?php endforeach; ?>
  <?php endif; ?>

  <div class="product-page">
      <div class="product-header">
          <h1><?php echo htmlspecialchars($product['name']); ?></h1>
      </div>

      <div class="product-content">
          <div class="left-panel">
              <img src="uploaded_img/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
          </div>
          <div class="right-panel">
              <p class="product-price">Price: Rs <?php echo number_format($product['price']); ?></p>
              <br>
              <p class="product-description"><strong>Description: <br></strong><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
              <br>
              <p class="product-stock"><strong>Stock Available: </strong><?php echo intval($product['quantity']); ?></p>
              <p class="product-author"><strong>Author:</strong> <?php echo htmlspecialchars($product['author'] ?: 'Unknown'); ?></p>
              <p class="product-edition"><strong>Edition:</strong> <?php echo htmlspecialchars($product['edition'] ?: 'N/A'); ?></p>
              <p class="product-categories"><strong>Genre:</strong> <?php echo htmlspecialchars($product['categories'] ?: 'Uncategorized'); ?></p>
              <form action="" method="post">
                  <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                  <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product['price']); ?>">
                  <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                  <input type="hidden" name="product_quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>">
                  <?php if ($product['quantity'] > 0): ?>
    <label for="order_quantity">Quantity:</label>
    <input type="number" name="order_quantity" id="order_quantity" min="1" max="<?php echo intval($product['quantity']); ?>" value="1" required>
    <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
<?php else: ?>
    <?php
    // Check if the logged-in user has already booked this product
    $book_check = mysqli_query($conn, "SELECT * FROM bookings WHERE product_name = '" . mysqli_real_escape_string($conn, $product['name']) . "' AND user_id = '$user_id' AND status = 'pending'") or die('query failed');
    if (mysqli_num_rows($book_check) > 0) {
        // Show "Booked" button as disabled
        echo '<button type="submit" class="btn" disabled>Booked</button>';
    } else {
        // Show "Book Now" button
        echo '<button type="submit" name="book_product" class="btn">Book Now</button>';
    }
    ?>
<?php endif; ?>
              </form>
              <a href="shop.php" class="btn">Back to Shop</a>
          </div>
      </div>

      <!-- Recommendation Section -->
      <?php
      // If product has genres, recommend similar products.
      if (!empty($product['categories'])) {
          // Convert comma-separated genres into an array
          $genres = array_map('trim', explode(',', $product['categories']));
          // Build a comma-separated list for SQL IN clause
          $genres_list = "'" . implode("','", $genres) . "'";
          $recommend_query = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
              FROM products p 
              LEFT JOIN product_categories pc ON p.id = pc.product_id 
              LEFT JOIN categories c ON pc.category_id = c.id 
              WHERE c.name IN ($genres_list) AND p.id != $product_id 
              GROUP BY p.id LIMIT 4";
          $recommend_result = mysqli_query($conn, $recommend_query) or die('Query Failed');
          
          if (mysqli_num_rows($recommend_result) > 0) {
              echo '<section class="products">';
              echo '<h1 class="title">Similar Products</h1>';
              echo '<div class="recommendations-container">';
              while($rec = mysqli_fetch_assoc($recommend_result)) {
                  echo '<div class="box">';
                  echo '<a href="product_detail.php?product_id=' . $rec['id'] . '">';
                  echo '<img class="image" src="uploaded_img/' . htmlspecialchars($rec['image']) . '" alt="Product Image">';
                  echo '<div class="name">' . htmlspecialchars($rec['name']) . '</div>';
                  echo '</a>';
                  echo '<div class="price">Rs' . number_format($rec['price']) . '/-</div>';
                  echo '<div class="available-stock">Stock Available: ' . intval($rec['quantity']) . '</div>';
                  echo '<form action="" method="post">';
                  echo '<input type="hidden" name="product_id" value="' . $rec['id'] . '">';
                  echo '<input type="hidden" name="product_name" value="' . htmlspecialchars($rec['name']) . '">';
                  echo '<input type="hidden" name="product_price" value="' . htmlspecialchars($rec['price']) . '">';
                  echo '<input type="hidden" name="product_image" value="' . htmlspecialchars($rec['image']) . '">';
                  echo '<input type="hidden" name="product_quantity" value="' . intval($rec['quantity']) . '">';
                  if ($rec['quantity'] > 0) {
                      echo '<input type="number" min="1" max="' . intval($rec['quantity']) . '" name="order_quantity" value="1" class="qty">';
                      echo '<input type="submit" value="Add to Cart" name="add_to_cart" class="btn">';
                  } else {
                      $book_check_rec = mysqli_query($conn, "SELECT * FROM bookings WHERE product_name = '" . mysqli_real_escape_string($conn, $rec['name']) . "' AND status = 'pending'") or die('query failed');
                      if (mysqli_num_rows($book_check_rec) > 0) {
                          echo '<input type="submit" value="Booked" class="btn" disabled>';
                      } else {
                          echo '<input type="submit" value="Book Now" name="book_product" class="btn">';
                      }
                  }
                  echo '</form>';
                  echo '</div>';
              }
              echo '</div>'; // End recommendations-container
              echo '</section>';
          }
      }
      ?>

  </div>

  <!-- Include Footer -->
  <?php include 'footer.php'; ?>

  <!-- Custom JS -->
  <script>
      // Handle add to cart or booking success message
      const message = <?php echo json_encode($message ?? []); ?>;
      if (message.length > 0) {
          alert(message.join("\n"));
      }
  </script>
</body>
</html>