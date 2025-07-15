<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
}

// Handle filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where_clause = '';
$page_title = "All Orders";

if($filter == 'pending'){
   $where_clause = "WHERE payment_status = 'pending'";
   $page_title = "Pending Orders";
} elseif($filter == 'completed') {
   $where_clause = "WHERE payment_status = 'completed'";
   $page_title = "Completed Orders";
}

if(isset($_POST['update_order'])){
   $order_update_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'];
   
   $current_status_query = mysqli_query($conn, "SELECT payment_status, email FROM `orders` WHERE id = '$order_update_id'") or die('query failed');
   $current_status_data = mysqli_fetch_assoc($current_status_query);
   $current_status = $current_status_data['payment_status'];
   $user_email = $current_status_data['email'];
   
   mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'") or die('query failed');
   $message[] = 'Payment status has been updated!';
   
   if($current_status == 'pending' && $update_payment == 'completed'){
       require_once 'sendmail.php';
       
       $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_update_id'") or die('query failed');
       $order_data = mysqli_fetch_assoc($order_query);
       
       $subject = "Your Order is Ready to Ship - Bookly";
       $body = "
           <h2>Dear {$order_data['name']},</h2>
           <p>We're excited to inform you that your order #{$order_update_id} has been processed and is ready to be shipped!</p>
           
           <h3>Order Details:</h3>
           <p><strong>Order Date:</strong> {$order_data['placed_on']}</p>
           <p><strong>Payment Method:</strong> " . ucwords($order_data['method']) . "</p>
           <p><strong>Delivery Address:</strong> {$order_data['address']}</p>
           
           <h3>Order Summary:</h3>
           <p><strong>Products:</strong> {$order_data['total_products']}</p>
           <p><strong>Total Amount:</strong> Rs {$order_data['total_price']}/-</p>
           
           <p>Your order will be shipped shortly and you'll receive another notification when it's on its way.</p>
           <p>Expected delivery: Within 2-3 business days</p>
           
           <p>If you have any questions, please contact us at support@bookly.com</p>
           
           <p>Thank you for shopping with us!</p>
           <p><strong>Bookly Team</strong></p>
       ";
       
       if(sendMail($user_email, $subject, $body)){
           $message[] = 'Payment status updated and notification email sent to customer!';
       } else {
           $message[] = 'Payment status updated but failed to send notification email.';
       }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - <?php echo $page_title; ?></title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom admin CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
        body { background-color: #f5f5f5; }
        .main-content { margin-left: 250px; padding: 20px; }
        .table-container {
            overflow-x: auto;
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f4f4f4; font-weight: bold; }
        tr:hover { background-color: #f9f9f9; }
        .option-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .option-btn { background-color: #3498db; }
        .option-btn:hover { background-color: #2980b9; }
        .delete-btn { background-color: #e74c3c; display: inline-block; margin-top: 5px; }
        .delete-btn:hover { background-color: #c0392b; }
        .empty { text-align: center; font-size: 18px; color: #555; padding: 20px 0; }
        .filter-buttons { margin-bottom: 20px; }
        .filter-btn {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            background: #3498db;
            color: white;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #2980b9;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; padding: 10px; }
            table, thead, tbody, th, td, tr { display: block; }
            tr { margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; background: #fff; }
            td { position: relative; padding-left: 50%; text-align: right; }
            td::before { content: attr(data-label); position: absolute; left: 10px; top: 10px; font-weight: bold; }
            .action-buttons { display: flex; flex-direction: column; align-items: flex-end; }
            .action-buttons a, .action-buttons input { margin-top: 5px; }
        }
    </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<div class="main-content">
<section class="orders">
   <h1 class="title"><?php echo $page_title; ?></h1>
   
   <div class="filter-buttons">
      <a href="admin_orders.php" class="filter-btn <?php echo empty($filter) ? 'active' : ''; ?>">All Orders</a>
      <a href="admin_orders.php?filter=pending" class="filter-btn <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending Orders</a>
      <a href="admin_orders.php?filter=completed" class="filter-btn <?php echo $filter == 'completed' ? 'active' : ''; ?>">Completed Orders</a>
   </div>
   
   <div class="table-container">
       <table>
           <thead>
               <tr>
                   <th>User ID</th>
                   <th>Placed On</th>
                   <th>Name</th>
                   <th>Number</th>
                   <th>Email</th>
                   <th>Address</th>
                   <th>Total Products</th>
                   <th>Total Price</th>
                   <th>Payment Method</th>
                   <th>Payment Status</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               <?php
               $select_orders = mysqli_query($conn, "SELECT * FROM `orders` $where_clause ORDER BY STR_TO_DATE(placed_on, '%d-%b-%Y') DESC") or die('query failed');

               if(mysqli_num_rows($select_orders) > 0){
                   while($fetch_orders = mysqli_fetch_assoc($select_orders)){
               ?>
               <tr>
                   <td data-label="User ID"><?php echo $fetch_orders['user_id']; ?></td>
                   <td data-label="Placed On"><?php echo $fetch_orders['placed_on']; ?></td>
                   <td data-label="Name"><?php echo $fetch_orders['name']; ?></td>
                   <td data-label="Number"><?php echo $fetch_orders['number']; ?></td>
                   <td data-label="Email"><?php echo $fetch_orders['email']; ?></td>
                   <td data-label="Address"><?php echo $fetch_orders['address']; ?></td>
                   <td data-label="Total Products"><?php echo $fetch_orders['total_products']; ?></td>
                   <td data-label="Total Price">Rs<?php echo number_format($fetch_orders['total_price']); ?>/-</td>
                   <td data-label="Payment Method"><?php echo $fetch_orders['method']; ?></td>
                   <td data-label="Payment Status">
                       <form action="" method="post" class="action-buttons">
                           <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                           <select name="update_payment">
                               <option value="" selected disabled><?php echo $fetch_orders['payment_status']; ?></option>
                               <option value="pending">pending</option>
                               <option value="completed">completed</option>
                           </select>
                           <input type="submit" value="Update" name="update_order" class="option-btn">
                       </form>
                   </td>
                   <td data-label="Actions">
                       <div class="action-buttons">
                           <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" onclick="return confirm('Delete this order?');" class="delete-btn">Delete</a>
                       </div>
                   </td>
               </tr>
               <?php
                   }
               } else {
                   echo '<tr><td colspan="11" class="empty">No orders found!</td></tr>';
               }
               ?>
           </tbody>
       </table>
   </div>
</section>
</div>

<script src="js/admin_script.js"></script>

</body>
</html>