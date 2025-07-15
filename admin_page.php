<?php
include 'config.php';
session_start();

// Ensure the admin is logged in
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Debugging step: Check what is being passed in the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if form data is available
    if (isset($_POST['order_id'], $_POST['product_id'], $_POST['quantity'])) {
        $order_id = $_POST['order_id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        // Insert the order item data into order_items table
        if ($order_id && $product_id && $quantity) {
            // Make sure the order exists in the orders table
            $check_order = mysqli_query($conn, "SELECT id FROM orders WHERE id = '$order_id' AND payment_status = 'completed'") or die('Query failed');
            if (mysqli_num_rows($check_order) > 0) {
                // Make sure the product exists in the products table
                $check_product = mysqli_query($conn, "SELECT id FROM products WHERE id = '$product_id'") or die('Query failed');
                if (mysqli_num_rows($check_product) > 0) {
                    // Insert into order_items
                    $insert_order_item = mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity) VALUES ('$order_id', '$product_id', '$quantity')") or die('Query failed');
                    echo "Order item added successfully.";
                } else {
                    echo "Error: Product ID does not exist in the products table.";
                }
            } else {
                echo "Error: Order ID does not exist or is not completed.";
            }
        } else {
            echo "Error: Please make sure all fields are filled.";
        }
    } else {
        echo "Error: Please make sure all fields are filled.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
   <title>Admin Panel</title>

   <!-- Font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
   <!-- Admin Style -->
   <link rel="stylesheet" href="css/admin_style.css">
   
   <style>
      .main-content {
         margin-top: 70px;
         padding: 1.5rem;
         min-height: calc(100vh - 70px);
      }

      .panel {
         border-radius: 5px;
         box-shadow: 0 1px 3px rgba(0,0,0,0.1);
         margin-bottom: 20px;
      }
      
      .panel-default {
         border-color: #ddd;
      }
      
      .panel-body {
         padding: 15px;
      }
      
      .panel-heading {
         padding: 10px 15px;
         border-bottom: 1px solid transparent;
         border-top-left-radius: 3px;
         border-top-right-radius: 3px;
         background-color: #f5f5f5;
         border-color: #ddd;
      }
      
      .bk-primary { background-color: #3e454c; }
      .bk-success { background-color: #5cb85c; }
      .bk-info { background-color: #5bc0de; }
      .bk-warning { background-color: #f0ad4e; }
      .bk-danger { background-color: #d9534f; }
      
      .stat-panel-number {
         font-size: 2.5rem;
         font-weight: bold;
         margin-bottom: 5px;
      }
      
      .stat-panel-title {
         font-size: 1rem;
         opacity: 0.9;
      }
      
      .block-anchor {
         display: block;
         padding: 10px 15px;
         background-color: #f5f5f5;
         border-top: 1px solid #ddd;
         color: #333;
         text-decoration: none;
      }
      
      .block-anchor:hover {
         background-color: #e8e8e8;
         color: #333;
         text-decoration: none;
      }
      
      .page-title {
         margin-top: 0;
         margin-bottom: 20px;
         font-size: 40px;
         font-weight: 500;
         text-align: center;
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="page-title">Dashboard</h2>

                <!-- Stats Cards Row 1 -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-primary text-light">
                                <div class="stat-panel text-center">
                                    <?php
                                        $total_pendings = 0;
                                        $select_pending = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'pending'") or die('query failed');
                                        if(mysqli_num_rows($select_pending) > 0){
                                           while($fetch_pendings = mysqli_fetch_assoc($select_pending)){
                                              $total_price = $fetch_pendings['total_price'];
                                              $total_pendings += $total_price;
                                           };
                                        };
                                    ?>
                                    <div class="stat-panel-number">Rs<?php echo $total_pendings; ?>/-</div>
                                    <div class="stat-panel-title">Total Pendings</div>
                                </div>
                            </div>
                            <a href="admin_orders.php?filter=pending" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-success text-light">
                                <div class="stat-panel text-center">
                                    <?php
                                        $total_completed = 0;
                                        $select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'completed'") or die('query failed');
                                        if(mysqli_num_rows($select_completed) > 0){
                                           while($fetch_completed = mysqli_fetch_assoc($select_completed)){
                                              $total_price = $fetch_completed['total_price'];
                                              $total_completed += $total_price;
                                           };
                                        };
                                    ?>
                                    <div class="stat-panel-number">Rs<?php echo $total_completed; ?>/-</div>
                                    <div class="stat-panel-title">Completed Payments</div>
                                </div>
                            </div>
                            <a href="admin_orders.php?filter=completed" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-info text-light">
                                <div class="stat-panel text-center">
                                    <?php
                                        $total_books_sold = 0;
                                        $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'completed'") or die('query failed');
                                        
                                        if (mysqli_num_rows($select_orders) > 0) {
                                           while ($fetch_order = mysqli_fetch_assoc($select_orders)) {
                                              preg_match_all('/\((\d+)\)/', $fetch_order['total_products'], $matches);
                                              if (!empty($matches[1])) {
                                                 foreach ($matches[1] as $quantity) {
                                                    $total_books_sold += (int)$quantity;
                                                 }
                                              }
                                           }
                                        }
                                    ?>
                                    <div class="stat-panel-number"><?php echo $total_books_sold; ?></div>
                                    <div class="stat-panel-title">Total Books Sold</div>
                                </div>
                            </div>
                            <a href="admin_scale.php" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-warning text-light">
                                <div class="stat-panel text-center">
                                    <?php 
                                        $select_bookings = mysqli_query($conn, "SELECT * FROM `bookings`") or die('query failed');
                                        $number_of_bookings = mysqli_num_rows($select_bookings);
                                    ?>
                                    <div class="stat-panel-number"><?php echo $number_of_bookings; ?></div>
                                    <div class="stat-panel-title">Bookings Placed</div>
                                </div>
                            </div>
                            <a href="admin_bookings.php" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards Row 2 -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-danger text-light">
                                <div class="stat-panel text-center">
                                    <?php 
                                        $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
                                        $number_of_products = mysqli_num_rows($select_products);
                                    ?>
                                    <div class="stat-panel-number"><?php echo $number_of_products; ?></div>
                                    <div class="stat-panel-title">Products Added</div>
                                </div>
                            </div>
                            <a href="admin_products.php" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-primary text-light">
                                <div class="stat-panel text-center">
                                    <?php 
                                        $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'user'") or die('query failed');
                                        $number_of_users = mysqli_num_rows($select_users);
                                    ?>
                                    <div class="stat-panel-number"><?php echo $number_of_users; ?></div>
                                    <div class="stat-panel-title">Normal Users</div>
                                </div>
                            </div>
                            <a href="admin_users.php?filter=user" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-success text-light">
                                <div class="stat-panel text-center">
                                    <?php 
                                        $select_admins = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'admin'") or die('query failed');
                                        $number_of_admins = mysqli_num_rows($select_admins);
                                    ?>
                                    <div class="stat-panel-number"><?php echo $number_of_admins; ?></div>
                                    <div class="stat-panel-title">Admin Users</div>
                                </div>
                            </div>
                            <a href="admin_users.php?filter=admin" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-body bk-info text-light">
                                <div class="stat-panel text-center">
                                    <?php 
                                        $select_messages = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
                                        $number_of_messages = mysqli_num_rows($select_messages);
                                    ?>
                                    <div class="stat-panel-number"><?php echo $number_of_messages; ?></div>
                                    <div class="stat-panel-title">New Messages</div>
                                </div>
                            </div>
                            <a href="admin_contacts.php" class="block-anchor">Full Detail <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Admin JS -->
<script src="js/admin_script.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>