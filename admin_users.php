<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Handle filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page_title = "User Accounts";
$show_all = true;

if ($filter == 'admin') {
    $page_title = "Admin Accounts";
    $show_all = false;
} elseif ($filter == 'user') {
    $page_title = "Regular User Accounts";
    $show_all = false;
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Retrieve user type before deletion
    $user_type_query = mysqli_query($conn, "SELECT user_type FROM `users` WHERE id = '$delete_id'");
    $user_type_data = mysqli_fetch_assoc($user_type_query);
    $user_type = $user_type_data['user_type'];

    // Prevent admin deletion
    if ($user_type !== 'admin') {
        mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'") or die('Query failed');
        header('location:admin_users.php');
    } else {
        echo "<script>alert('You cannot delete an admin account.');</script>";
    }
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
        .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #e74c3c;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            display: inline-block;
            margin-top: 5px;
            transition: background 0.3s;
        }
        .delete-btn:hover { background-color: #c0392b; }
        .empty { text-align: center; font-size: 18px; color: #555; padding: 20px 0; }
        .filter-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background: #3498db;
            color: white;
            cursor: pointer;
            text-decoration: none;
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
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        .add-product-btn {
            padding: 8px 15px;
            background: var(--orange);
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="main-content">
<section class="users">
    <h1 class="title"><?php echo $page_title; ?></h1>

    <div class="button-container">
        <div class="filter-buttons">
            <a href="admin_users.php" class="filter-btn <?php echo empty($filter) ? 'active' : ''; ?>">All Users</a>
            <a href="admin_users.php?filter=admin" class="filter-btn <?php echo $filter == 'admin' ? 'active' : ''; ?>">Admins</a>
            <a href="admin_users.php?filter=user" class="filter-btn <?php echo $filter == 'user' ? 'active' : ''; ?>">Regular Users</a>
        </div>
        <a href="register2.php" class="add-product-btn">Register New Account</a>
    </div>

    <?php if ($show_all || $filter == 'admin'): ?>
    <div class="table-container">
        <h2>Admin Accounts</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>User Type</th>
                    <?php if ($filter == 'admin'): ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $where_clause = $filter == 'admin' ? "WHERE user_type = 'admin'" : "WHERE user_type = 'admin'";
                $select_admins = mysqli_query($conn, "SELECT * FROM `users` $where_clause") or die('Query failed');
                if (mysqli_num_rows($select_admins) > 0) {
                    while ($fetch_admins = mysqli_fetch_assoc($select_admins)) {
                        echo "<tr>
                                <td data-label='User ID'>{$fetch_admins['id']}</td>
                                <td data-label='Username'>{$fetch_admins['name']}</td>
                                <td data-label='Email'>{$fetch_admins['email']}</td>
                                <td data-label='Phone'>{$fetch_admins['phone']}</td>
                                <td data-label='User Type' style='color: var(--orange);'>{$fetch_admins['user_type']}</td>";
                        // if ($filter == 'admin') {
                        //     echo "<td data-label='Actions'>
                        //             <div class='action-buttons'>
                        //                 <a href='admin_users.php?delete={$fetch_admins['id']}' onclick='return confirm(\"Delete this admin?\");' class='delete-btn'>Delete</a>
                        //             </div>
                        //           </td>";
                        // }
                        echo "</tr>";
                    }
                } else {
                    $colspan = $filter == 'admin' ? 6 : 5;
                    echo "<tr><td colspan='$colspan' class='empty'>No admin accounts found!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($show_all || $filter == 'user'): ?>
    <div class="table-container">
        <h2>Regular User Accounts</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>User Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where_clause = $filter == 'user' ? "WHERE user_type != 'admin'" : "WHERE user_type != 'admin'";
                $select_users = mysqli_query($conn, "SELECT * FROM `users` $where_clause") or die('Query failed');
                if (mysqli_num_rows($select_users) > 0) {
                    while ($fetch_users = mysqli_fetch_assoc($select_users)) {
                        echo "<tr>
                                <td data-label='User ID'>{$fetch_users['id']}</td>
                                <td data-label='Username'>{$fetch_users['name']}</td>
                                <td data-label='Email'>{$fetch_users['email']}</td>
                                <td data-label='Phone'>{$fetch_users['phone']}</td>
                                <td data-label='User Type'>{$fetch_users['user_type']}</td>
                                <td data-label='Actions'>
                                    <div class='action-buttons'>
                                        <a href='admin_users.php?delete={$fetch_users['id']}' onclick='return confirm(\"Delete this user?\");' class='delete-btn'>Delete</a>
                                    </div>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='empty'>No user accounts found!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
</div>

<!-- Custom admin JS file link -->
<script src="js/admin_script.js"></script>

</body>
</html>