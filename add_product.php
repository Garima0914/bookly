<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Fetch categories
$categories = [];
$categories_query = mysqli_query($conn, "SELECT * FROM categories") or die('Query failed');
while ($row = mysqli_fetch_assoc($categories_query)) {
    $categories[] = $row;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $author = mysqli_real_escape_string($conn, $_POST['author']);
        $edition = mysqli_real_escape_string($conn, $_POST['edition']);
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $image = $_FILES['image']['name'];
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = "uploaded_img/$image";
        $selected_categories = $_POST['categories'] ?? [];

        // Validation checks
        $errors = [];
        
        // Check if name starts with alphabet
        if (!preg_match('/^[a-zA-Z]/', $name)) {
            $errors[] = 'Book name must start with an alphabet!';
        }
        
        // Check if author starts with alphabet
        if (!preg_match('/^[a-zA-Z]/', $author)) {
            $errors[] = 'Author name must start with an alphabet!';
        }
        
        // Check if description starts with alphabet
        if (!preg_match('/^[a-zA-Z]/', $description)) {
            $errors[] = 'Description must start with an alphabet!';
        }
        
        // Check if price is at least 100
        if ($price < 100) {
            $errors[] = 'Price must be at least Rs.100!';
        }

        // Check for existing product with same name AND author
        $product_check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name' AND author = '$author'");
        if (mysqli_num_rows($product_check) > 0) {
            $errors[] = 'A book with this name and author already exists!';
        } elseif ($image_size > 2000000) {
            $errors[] = 'Image size too large!';
        }

        if (empty($errors)) {
            mysqli_query($conn, "INSERT INTO products (name, author, edition, price, image, quantity, description) VALUES ('$name', '$author', '$edition', '$price', '$image', '$quantity', '$description')") or die('Query failed');
            $product_id = mysqli_insert_id($conn);
            move_uploaded_file($image_tmp_name, $image_folder);

            foreach ($selected_categories as $category_id) {
                mysqli_query($conn, "INSERT INTO product_categories (product_id, category_id) VALUES ('$product_id', '$category_id')") or die('Query failed');
            }

            header('location:admin_products.php');
            exit();
        } else {
            $message = $errors; // Set the error messages to display
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="add-products">
    <h1 class="title">Add New Product</h1>
    
    <?php
    if (isset($message)) {
        foreach ($message as $msg) {
            echo '<div class="message">' . $msg . '</div>';
        }
    }
    ?>
    
    <form action="" method="post" enctype="multipart/form-data">
        <h3>Add Product</h3>
        <input type="text" name="name" class="box" placeholder="Enter Book name" required>
        <input type="text" name="author" class="box" placeholder="Enter Author name" required>
        <input type="text" name="edition" class="box" placeholder="Enter Edition (optional)">
        <input type="number" min="100" name="price" class="box" placeholder="Enter price" required>
        <input type="number" min="1" name="quantity" class="box" placeholder="Enter quantity" required>
        <textarea name="description" class="box" placeholder="Enter description" required></textarea>
        <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>

        <h3>Select Categories (Optional)</h3>
        <?php foreach ($categories as $category): ?>
            <label>
                <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>">
                <?php echo $category['name']; ?>
            </label>
        <?php endforeach; ?>

        <br><input type="submit" value="Add Product" name="add_product" class="btn">
    </form>
</section>

<script src="js/admin_script.js"></script>

</body>
</html>