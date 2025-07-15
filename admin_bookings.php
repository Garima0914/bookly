<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

if (isset($_POST['update_booking'])) {
    $booking_update_id = $_POST['booking_id'];
    $update_status = $_POST['update_status'];
    mysqli_query($conn, "UPDATE `bookings` SET status = '$update_status' WHERE id = '$booking_update_id'") or die('Query failed');
    $message[] = 'Booking status has been updated!';
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `bookings` WHERE id = '$delete_id'") or die('Query failed');
    header('location:admin_bookings.php');
    exit();
}

$select_bookings = mysqli_query($conn, "
    SELECT bookings.*, users.name AS user_name 
    FROM bookings 
    INNER JOIN users ON bookings.user_id = users.id
    ORDER BY bookings.created_at DESC
") or die('Query failed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .main-content {
            margin-left: 250px;
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
        .option-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .option-btn {
            background-color: #3498db;
        }
        .option-btn:hover {
            background-color: #2980b9;
        }
        .delete-btn {
            background-color: #e74c3c;
            display: inline-block;
            text-align: center;
            margin-top: 5px;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .empty {
            text-align: center;
            font-size: 18px;
            color: #555;
            padding: 20px 0;
        }
        @media (max-width: 600px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
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
            .action-buttons {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
            }
            .action-buttons a, .action-buttons input {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="main-content">
<section class="bookings">
    <h1 class="title">Bookings</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Name</th> 
                    <th>Product Name</th>
                    <th>Product Price</th>
                    <th>Status</th>
                    <th>Booked On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($select_bookings) > 0) {
                    while ($fetch_bookings = mysqli_fetch_assoc($select_bookings)) { ?>
                        <tr>
                            <td data-label="User ID"><?php echo $fetch_bookings['user_id']; ?></td>
                            <td data-label="User Name"><?php echo $fetch_bookings['user_name']; ?></td> 
                            <td data-label="Product Name"><?php echo $fetch_bookings['product_name']; ?></td>
                            <td data-label="Product Price">Rs.<?php echo number_format($fetch_bookings['product_price']); ?>/-</td>
                            <td data-label="Status">
                                <form action="" method="post" class="action-buttons">
                                    <input type="hidden" name="booking_id" value="<?php echo $fetch_bookings['id']; ?>">
                                    <select name="update_status">
                                        <option value="pending" <?php echo ($fetch_bookings['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="fulfilled" <?php echo ($fetch_bookings['status'] == 'fulfilled') ? 'selected' : ''; ?>>Fulfilled</option>
                                    </select>
                                    <input type="submit" value="Update" name="update_booking" class="option-btn">
                                </form>
                            </td>
                            <td data-label="Booked On"><?php echo $fetch_bookings['created_at']; ?></td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <a href="admin_bookings.php?delete=<?php echo $fetch_bookings['id']; ?>" onclick="return confirm('Delete this booking?');" class="delete-btn">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr><td colspan="7" class="empty">No bookings yet!</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>
</div>

<script src="js/admin_script.js"></script>
</body>
</html>
