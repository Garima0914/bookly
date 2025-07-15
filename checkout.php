<?php
include 'config.php';
session_start();

// Check if the user is logged in
$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

// Retrieve user details for auto-filling form fields
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'") or die('Query Failed');
if (mysqli_num_rows($user_query) > 0) {
    $user_info = mysqli_fetch_assoc($user_query);
} else {
    header('location:logout.php');
    exit;
}

if (isset($_POST['continue_to_payment'])) {
    // Validate and store address details in session
    $_SESSION['address_details'] = [
        'street' => mysqli_real_escape_string($conn, $_POST['street']),
        'city' => mysqli_real_escape_string($conn, $_POST['city']),
        'state' => mysqli_real_escape_string($conn, $_POST['state'])
    ];
    header('Location: payment.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shipping Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .shipping {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        form {
            display: grid;
            grid-gap: 15px;
        }
        label {
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: orange;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: darkorange;
        }
        .readonly-input {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="shipping">
        <h1>Shipping Details</h1>
        <form action="" method="post">
            <label for="name">Your Name*</label>
            <input type="text" id="name" name="name" required value="<?php echo $user_info['name']; ?>" class="readonly-input" readonly>
            
            <label for="number">Your Phone Number*</label>
            <input type="number" id="number" name="number" required value="<?php echo $user_info['phone']; ?>" class="readonly-input" readonly>
            
            <label for="email">Your Email*</label>
            <input type="email" id="email" name="email" required value="<?php echo $user_info['email']; ?>" class="readonly-input" readonly>
            
            <h4>Address Details</h4>
            <label for="street">Street Name*</label>
            <input type="text" id="street" name="street" required placeholder="Enter street name">
            
            <label for="city">City*</label>
            <input type="text" id="city" name="city" required placeholder="Enter city">
            
            <label for="state">District*</label>
            <input type="text" id="state" name="state" required placeholder="Enter district">
            
            <button type="submit" name="continue_to_payment" class="btn">Continue to Payment</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>