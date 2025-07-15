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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
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
                $_SESSION['message'] = 'Image size too large!';
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
            mysqli_query($conn, "UPDATE bookings SET status = 'fulfilled' WHERE product_name = '$name' AND status = 'pending'") or die('Query failed');
        }

        // Show success message for 3 seconds before redirecting
        echo '<div class="success-notification">Product updated successfully! Redirecting back to products page...</div>';
        echo '<script>
                setTimeout(function() {
                    window.location.href = "admin_products.php";
                }, 3000);
              </script>';
        exit();
    } else {
        $_SESSION['message'] = $errors;
    }
}

// Get product data to update
if (isset($_GET['update'])) {
    $update_id = $_GET['update'];
    $update_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$update_id'") or die('Query failed');
    
    if (mysqli_num_rows($update_query) > 0) {
        $product = mysqli_fetch_assoc($update_query);
        
        $product_categories_query = mysqli_query($conn, "SELECT category_id FROM product_categories WHERE product_id = '$update_id'") or die('Query failed');
        $product_categories = [];
        while ($category = mysqli_fetch_assoc($product_categories_query)) {
            $product_categories[] = $category['category_id'];
        }
    } else {
        header('location:admin_products.php');
        exit();
    }
} else {
    header('location:admin_products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        /* Main content styling - matches admin_products.php */
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

        .update-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .update-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #28a745;
            outline: none;
        }
        
        .image-preview {
            text-align: center;
            margin: 20px 0;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        
        .category-item {
            display: flex;
            align-items: center;
        }
        
        .category-item input {
            width: auto;
            margin-right: 8px;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #28a745;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; top: 0; }
            to { opacity: 1; top: 20px; }
        }
        
        @media (max-width: 768px) {
            .update-container {
                padding: 20px;
            }
            
            .categories-container {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .update-container {
                padding: 15px;
            }
            
            .categories-container {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="main-content">
    <div class="update-container">
        <h1>Update Product</h1>
        
        <?php
        if (isset($_SESSION['message'])) {
            if (is_array($_SESSION['message'])) {
                foreach ($_SESSION['message'] as $msg) {
                    echo '<div class="error-message">' . $msg . '</div>';
                }
            } else {
                echo '<div class="error-message">' . $_SESSION['message'] . '</div>';
            }
            unset($_SESSION['message']);
        }
        ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="update_p_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="update_old_image" value="<?php echo $product['image']; ?>">
            
            <div class="image-preview">
                <img src="uploaded_img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            </div>
            
            <div class="form-group">
                <label for="update_name">Book Name*</label>
                <input type="text" name="update_name" value="<?php echo $product['name']; ?>" required 
                       pattern="[a-zA-Z].*" title="Book name must start with an alphabet">
            </div>
            
            <div class="form-group">
                <label for="update_author">Author*</label>
                <input type="text" name="update_author" value="<?php echo $product['author']; ?>" required 
                       pattern="[a-zA-Z].*" title="Author name must start with an alphabet">
            </div>
            
            <div class="form-group">
                <label for="update_edition">Edition</label>
                <input type="text" name="update_edition" value="<?php echo $product['edition']; ?>">
            </div>
            
            <div class="form-group">
                <label for="update_price">Price (Rs.)*</label>
                <input type="number" name="update_price" value="<?php echo $product['price']; ?>" min="100" required>
            </div>
            
            <div class="form-group">
                <label for="update_quantity">Quantity*</label>
                <input type="number" name="update_quantity" value="<?php echo $product['quantity']; ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Categories</label>
                <div class="categories-container">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item">
                            <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                                id="cat-<?php echo $category['id']; ?>"
                                <?php echo in_array($category['id'], $product_categories) ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo $category['id']; ?>"><?php echo $category['name']; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="update_description">Description*</label>
                <textarea name="update_description" required 
                          pattern="[a-zA-Z].*" title="Description must start with an alphabet"><?php echo $product['description']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="update_image">Update Image (optional)</label>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png">
                <small>Max size: 2MB (Leave blank to keep current image)</small>
            </div>
            
            <div class="btn-group">
                <input type="submit" value="Update Product" name="update_product" class="btn btn-primary">
                <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live validation for fields
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', function() {
            if (this.hasAttribute('pattern')) {
                const regex = new RegExp(this.getAttribute('pattern'));
                if (regex.test(this.value)) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#ff6b6b';
                }
            }
            
            if (this.name === 'update_price' && this.value < 100) {
                this.style.borderColor = '#ff6b6b';
            } else if (this.name === 'update_price') {
                this.style.borderColor = '#28a745';
            }
        });
    });
    
    // Image preview functionality
    const imageInput = document.querySelector('input[type="file"][accept^="image"]');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.querySelector('.image-preview img');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<script src="js/admin_script.js"></script>

</body>
</html>