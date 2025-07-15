<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('Query failed');
    header('location:admin_contacts.php');
}

// Check if created_at column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM `message` LIKE 'created_at'");
$column_exists = (mysqli_num_rows($check_column) > 0);

// Modify query based on column existence
$order_by = $column_exists ? "ORDER BY created_at DESC" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - Messages</title>
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
      .message-content {
         max-width: 300px;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      .delete-btn {
         padding: 5px 10px;
         border: none;
         border-radius: 5px;
         text-decoration: none;
         color: #fff;
         background-color: #e74c3c;
         cursor: pointer;
         transition: background 0.3s;
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
      .timestamp {
         white-space: nowrap;
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
         .message-content {
            max-width: 100%;
            white-space: normal;
         }
      }
   </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="main-content">
<section class="messages">

   <h1 class="title">Messages</h1>

   <div class="table-container">
      <table>
         <thead>
            <tr>
               <?php if ($column_exists): ?>
               <th>Date & Time</th>
               <?php endif; ?>
               <th>User ID</th>
               <th>Name</th>
               <th>Email</th>
               <th>Phone</th>
               <th>Message</th>
               <th>Action</th>
            </tr>
         </thead>
         <tbody>
            <?php
               $select_message = mysqli_query($conn, "SELECT * FROM `message` $order_by") or die('Query failed');
               if (mysqli_num_rows($select_message) > 0) {
                  while ($fetch_message = mysqli_fetch_assoc($select_message)) {
            ?>
            <tr>
               <?php if ($column_exists): ?>
               <td data-label="Date & Time" class="timestamp"><?php echo $fetch_message['created_at']; ?></td>
               <?php endif; ?>
               <td data-label="User ID"><?php echo $fetch_message['user_id']; ?></td>
               <td data-label="Name"><?php echo $fetch_message['name']; ?></td>
               <td data-label="Email"><?php echo $fetch_message['email']; ?></td>
               <td data-label="Phone"><?php echo $fetch_message['number']; ?></td>
               <td data-label="Message" class="message-content" title="<?php echo htmlspecialchars($fetch_message['message']); ?>">
                  <?php echo htmlspecialchars($fetch_message['message']); ?>
               </td>
               <td data-label="Action">
                  <a href="admin_contacts.php?delete=<?php echo $fetch_message['id']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">Delete</a>
               </td>
            </tr>
            <?php
                  }
               } else {
                  $colspan = $column_exists ? 7 : 6;
                  echo '<tr><td colspan="'.$colspan.'" class="empty">No messages found!</td></tr>';
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