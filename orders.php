<?php
include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .main-content {
            padding: 20px;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .status-pending {
            color: red;
            font-weight: bold;
        }
        .status-completed {
            color: green;
            font-weight: bold;
        }
        .status-shipped {
            color: blue;
            font-weight: bold;
        }
        .status-delivered {
            color: #28a745;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .empty {
            text-align: center;
            font-size: 18px;
            color: #555;
            padding: 20px 0;
        }
        .heading {
            text-align: center;
            padding: 20px 0;
            background: #f4f4f4;
            margin-bottom: 20px;
        }
        .heading h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .heading p {
            font-size: 14px;
            color: #666;
        }
        .heading p a {
            color: #333;
            text-decoration: none;
        }
        .heading p a:hover {
            text-decoration: underline;
        }
        .recent-badge {
            background: #28a745;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 5px;
        }
        .order-id {
            font-weight: bold;
            color: #333;
        }
        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr { 
                display: block; 
            }
            tr {
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                background: #fff;
                overflow: hidden;
            }
            td {
                position: relative;
                padding-left: 50%;
                text-align: right;
                border: none;
                border-bottom: 1px solid #eee;
            }
            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                top: 10px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
    <h3>your orders</h3>
    <p> <a href="home.php">home</a> / orders </p>
</div>

<div class="main-content">
<section class="placed-orders">
    <h1 class="title">Placed Orders</h1>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Payment Method</th>
                    <th>Products</th>
                    <th>Total Price</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Enhanced query to ensure proper sorting by most recent orders first
                $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' ORDER BY id DESC, placed_on DESC") or die('query failed');
                
                if(mysqli_num_rows($order_query) > 0){
                    $order_count = 0;
                    while($fetch_orders = mysqli_fetch_assoc($order_query)){
                        $order_count++;
                        
                        // Check if order is recent (within last 7 days)
                        $order_date = strtotime($fetch_orders['placed_on']);
                        $current_date = time();
                        $days_diff = floor(($current_date - $order_date) / (60 * 60 * 24));
                        $is_recent = $days_diff <= 7;
                        
                        // Format date for better display (without time)
                        $formatted_date = date('M d, Y', $order_date);
                        
                        // Determine status class
                        $status_class = 'status-pending';
                        switch(strtolower($fetch_orders['payment_status'])) {
                            case 'completed':
                            case 'paid':
                                $status_class = 'status-completed';
                                break;
                            case 'shipped':
                                $status_class = 'status-shipped';
                                break;
                            case 'delivered':
                                $status_class = 'status-delivered';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                break;
                            default:
                                $status_class = 'status-pending';
                        }
                ?>
                <tr>
                    <td data-label="Order ID">
                        <span class="order-id">#<?php echo str_pad($fetch_orders['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        <?php if($is_recent): ?>
                            <span class="recent-badge">NEW</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Order Date"><?php echo $formatted_date; ?></td>
                    <td data-label="Name"><?php echo htmlspecialchars($fetch_orders['name']); ?></td>
                    <td data-label="Contact"><?php echo htmlspecialchars($fetch_orders['number']); ?></td>
                    <td data-label="Email"><?php echo htmlspecialchars($fetch_orders['email']); ?></td>
                    <td data-label="Address"><?php echo htmlspecialchars($fetch_orders['address']); ?></td>
                    <td data-label="Payment Method"><?php echo htmlspecialchars($fetch_orders['method']); ?></td>
                    <td data-label="Products"><?php echo htmlspecialchars($fetch_orders['total_products']); ?></td>
                    <td data-label="Total Price">Rs.<?php echo number_format($fetch_orders['total_price']); ?>/-</td>
                    <td data-label="Payment Status" class="<?php echo $status_class; ?>">
                        <?php echo ucfirst($fetch_orders['payment_status']); ?>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="10" class="empty">No orders placed yet!</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>
</div>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>