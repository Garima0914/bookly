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

// Function to validate if string starts with alphabet
function startsWithAlphabet($string) {
    return preg_match('/^[a-zA-Z]/', $string);
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

        // Validate inputs
        $errors = [];
        
        if (!startsWithAlphabet($name)) {
            $errors[] = 'Book name must start with an alphabet!';
        }
        
        if (!startsWithAlphabet($author)) {
            $errors[] = 'Author name must start with an alphabet!';
        }
        
        if (!startsWithAlphabet($description)) {
            $errors[] = 'Description must start with an alphabet!';
        }
        
        if ($price < 100) {
            $errors[] = 'Price must be at least Rs.100!';
        }

        // Check for existing product with same name AND author (only if no validation errors)
        if (empty($errors)) {
            $product_check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name' AND author = '$author'");
            if (mysqli_num_rows($product_check) > 0) {
                $errors[] = 'A product with this name and author already exists!';
            } elseif ($image_size > 2000000) {
                $errors[] = 'Image size too large!';
            }
        }

        if (empty($errors)) {
            mysqli_query($conn, "INSERT INTO products (name, author, edition, price, image, quantity, description) VALUES ('$name', '$author', '$edition', '$price', '$image', '$quantity', '$description')") or die('Query failed');
            $product_id = mysqli_insert_id($conn);
            move_uploaded_file($image_tmp_name, $image_folder);

            foreach ($selected_categories as $category_id) {
                mysqli_query($conn, "INSERT INTO product_categories (product_id, category_id) VALUES ('$product_id', '$category_id')") or die('Query failed');
            }

            $message[] = 'Product added successfully!';
        } else {
            $message = $errors;
        }
    }

    if (isset($_POST['update_product'])) {
        $id = $_POST['update_p_id'];
        $name = mysqli_real_escape_string($conn, $_POST['update_name']);
        $author = mysqli_real_escape_string($conn, $_POST['update_author']);
        $edition = mysqli_real_escape_string($conn, $_POST['update_edition']);
        $price = $_POST['update_price'];
        $quantity = $_POST['update_quantity'];
        $description = mysqli_real_escape_string($conn, $_POST['update_description']);
        $old_image = $_POST['update_old_image'];
        $new_image = $_FILES['update_image']['name'];
        $new_image_tmp_name = $_FILES['update_image']['tmp_name'];
        $new_image_size = $_FILES['update_image']['size'];
        $selected_categories = $_POST['categories'] ?? [];

        // Validate inputs
        $errors = [];
        
        if (!startsWithAlphabet($name)) {
            $errors[] = 'Book name must start with an alphabet!';
        }
        
        if (!startsWithAlphabet($author)) {
            $errors[] = 'Author name must start with an alphabet!';
        }
        
        if (!startsWithAlphabet($description)) {
            $errors[] = 'Description must start with an alphabet!';
        }
        
        if ($price < 100) {
            $errors[] = 'Price must be at least Rs.100!';
        }

        if (empty($errors)) {
            // Fetch the current stock of the product
            $current_stock_query = mysqli_query($conn, "SELECT quantity FROM products WHERE id = '$id'");
            $current_stock_data = mysqli_fetch_assoc($current_stock_query);
            $current_stock = $current_stock_data['quantity'];

            // Check if name or author is being changed to a combination that already exists
            $existing_check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name' AND author = '$author' AND id != '$id'");
            if (mysqli_num_rows($existing_check) > 0) {
                $errors[] = 'Another product with this name and author already exists!';
            }
        }

        if (empty($errors)) {
            // Update the product
            mysqli_query($conn, "UPDATE products SET name='$name', author='$author', edition='$edition', price='$price', quantity='$quantity', description='$description' WHERE id='$id'") or die('Query failed');

            if (!empty($new_image)) {
                if ($new_image_size > 2000000) {
                    $message[] = 'Image size too large!';
                } else {
                    if (file_exists("uploaded_img/$old_image")) {
                        unlink("uploaded_img/$old_image");
                    }
                    move_uploaded_file($new_image_tmp_name, "uploaded_img/$new_image");
                    mysqli_query($conn, "UPDATE products SET image='$new_image' WHERE id='$id'") or die('Query failed');
                }
            }

            mysqli_query($conn, "DELETE FROM product_categories WHERE product_id='$id'") or die('Query failed');
            foreach ($selected_categories as $category_id) {
                mysqli_query($conn, "INSERT INTO product_categories (product_id, category_id) VALUES ('$id', '$category_id')") or die('Query failed');
            }

            // Check if stock was updated from 0 to a value greater than 0
            if ($current_stock == 0 && $quantity > 0) {
                // Update the status of all pending bookings for this product to 'fulfilled'
                mysqli_query($conn, "UPDATE bookings SET status = 'fulfilled' WHERE product_name = '$name' AND status = 'pending'") or die('Query failed');
            }

            $message[] = 'Product updated successfully!';
            header('location:admin_products.php');
            exit();
        } else {
            $message = $errors;
        }
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $img_query = mysqli_query($conn, "SELECT image FROM products WHERE id = '$id'");
    $img = mysqli_fetch_assoc($img_query);
    if (file_exists("uploaded_img/" . $img['image'])) {
        unlink("uploaded_img/" . $img['image']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'") or die('Query failed');
    header('location:admin_products.php');
    exit();
}

// Pagination logic
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$total_products_query = mysqli_query($conn, "SELECT COUNT(id) AS total FROM products") or die('Query failed');
$total_products = mysqli_fetch_assoc($total_products_query)['total'];
$total_pages = ceil($total_products / $limit);

// Fetch products with limit
$select_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT $start, $limit") or die('Query failed');
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
    <style>
        /* Main content styling */
        .main-content {
            margin-top: 70px; /* Offset for fixed header */
            padding: 2rem;
            transition: margin-left 0.3s;
            min-height: calc(100vh - 70px);
        }

        @media (min-width: 992px) {
            .main-content {
                margin-left: 280px; /* Offset for sidebar */
            }
        }

        .box {
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        /* Responsive styles for admin_products.php */
        @media (max-width: 1200px) {
            .box-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .box-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .add-products, .show-products, .edit-product-form {
                padding: 10px;
            }
            .box {
                padding: 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 768px) {
            .box-container {
                grid-template-columns: 1fr;
            }
            .add-products h3, .edit-product-form h3 {
                font-size: 18px;
            }
            input, textarea, .btn {
                width: 100%;
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            .box-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .box {
                width: 90%;
            }
            .title {
                font-size: 22px;
            }
            .btn, .option-btn, .delete-btn {
                width: 100%;
            }
            label {
                font-size: 14px;
            }
        }

        .add-product-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background: #28a745;
            color: white;
            font-size: 18px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        /* Low stock styles */
        .low-stock {
            border: 2px solid #ff6b6b;
            position: relative;
        }
        
        .low-stock::before {
            content: "LOW STOCK";
            position: absolute;
            top: 0;
            right: 0;
            background: #ff6b6b;
            color: white;
            padding: 2px 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stock-warning {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .quantity.low-stock {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .low-stock-alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        /* Show products section */
        .show-products {
            padding: 20px;
            background-color: #f9f9f9;
        }

        /* Edit product form */
        .edit-product-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 10px;
        }
    </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="main-content">
    <div class="show-products">
        <h1 class="title">Product List</h1>
        
        <?php
        // Display error messages if any
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="error-message">' . $msg . '</div>';
            }
        }
        
        // Check for low stock products
        $low_stock_query = mysqli_query($conn, "SELECT name, quantity FROM products WHERE quantity < 2");
        if (mysqli_num_rows($low_stock_query) > 0) {
            echo '<div class="low-stock-alert">';
            echo '<h3>⚠️ Low Stock Alert!</h3>';
            echo '<p>The following products have low stock (less than 2 items):</p>';
            echo '<ul>';
            while ($low_stock = mysqli_fetch_assoc($low_stock_query)) {
                echo '<li><strong>' . $low_stock['name'] . '</strong> (Quantity: ' . $low_stock['quantity'] . ')</li>';
            }
            echo '</ul>';
            echo '<p>Please update their stock quantities soon.</p>';
            echo '</div>';
        }
        ?>
        
        <div class="box-container">
            <?php
            if (mysqli_num_rows($select_products) > 0) {
                while ($product = mysqli_fetch_assoc($select_products)) {
                    // Add warning class if quantity is low
                    $quantity_class = $product['quantity'] < 2 ? 'low-stock' : '';
                    ?>
                    <div class="box <?php echo $quantity_class; ?>">
                        <img src="uploaded_img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="name">Name: <?php echo $product['name']; ?></div>
                        <div class="author">Author: <?php echo $product['author']; ?></div>
                        <div class="edition">Edition: <?php echo $product['edition'] ?: 'N/A'; ?></div>
                        <div class="price">Rs<?php echo $product['price']; ?>/-</div>
                        <div class="quantity <?php echo $quantity_class; ?>">
                            Quantity: <?php echo $product['quantity']; ?>
                            <?php if ($product['quantity'] < 2): ?>
                                <span class="stock-warning">(Low Stock!)</span>
                            <?php endif; ?>
                        </div>
                        <a href="update_product.php?update=<?php echo $product['id']; ?>" class="option-btn">Update</a>
                        <a href="admin_products.php?delete=<?php echo $product['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="empty">No products added yet!</p>';
            }
            ?>
        </div>
    </div>


    <a href="add_product.php" class="add-product-btn">Add New Product</a>

    <div class="pagination">
        <?php if ($page > 1) : ?>
            <a href="admin_products.php?page=<?php echo $page - 1; ?>" class="add-product-btn" style="align: center">Previous</a>
        <?php endif; ?>
        
        <?php if ($page < $total_pages) : ?>
            <a href="admin_products.php?page=<?php echo $page + 1; ?>" class="add-product-btn" style="align: center">View More Products</a>
        <?php endif; ?>
    </div>
</section>

<script>
// Add this JavaScript to show an alert when the page loads if there are low stock items
document.addEventListener('DOMContentLoaded', function() {
    <?php
    $low_stock_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE quantity < 2"));
    if ($low_stock_count > 0) {
        echo "alert('⚠️ Low Stock Alert! You have $low_stock_count product(s) with less than 2 items in stock. Please update their quantities.');";
    }
    ?>
});

// Client-side validation for the add product form
document.addEventListener('DOMContentLoaded', function() {
    const addProductForm = document.querySelector('form[action="add_product.php"]');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            const name = this.querySelector('[name="name"]').value;
            const author = this.querySelector('[name="author"]').value;
            const description = this.querySelector('[name="description"]').value;
            const price = parseFloat(this.querySelector('[name="price"]').value);
            
            if (!/^[a-zA-Z]/.test(name)) {
                alert('Book name must start with an alphabet!');
                e.preventDefault();
                return false;
            }
            
            if (!/^[a-zA-Z]/.test(author)) {
                alert('Author name must start with an alphabet!');
                e.preventDefault();
                return false;
            }
            
            if (!/^[a-zA-Z]/.test(description)) {
                alert('Description must start with an alphabet!');
                e.preventDefault();
                return false;
            }
            
            if (price < 100) {
                alert('Price must be at least Rs.100!');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
});
</script>

<script src="js/admin_script.js"></script>

</body>
</html>