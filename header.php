<?php
// Fetch categories from the database
$select_categories = mysqli_query($conn, "SELECT * FROM `categories`") or die('Query failed');
?>

<?php
if (isset($message)) {
    foreach ($message as $message) {
        echo '
        <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">

   <?php if (!isset($_SESSION['user_id'])): ?>
   <div class="header-1">
      <div class="flex">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <p> new <a href="login.php">login</a> | <a href="register.php">register</a> </p>
      </div>
   </div>
   <?php endif; ?>

   <div class="header-2">
      <div class="flex">
         <a href="home.php" class="logo">Bookly</a>

         <nav class="navbar">
            <a href="home.php">Home</a>
            <a href="about.php">About</a>
            <a href="shop.php">Shop</a>
            <a href="contact.php">Contact</a>
            <a href="orders.php">Orders</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php" class="fas fa-search"></a>
            <div id="user-btn" class="fas fa-user"></div>
            <?php
               $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
               $cart_rows_number = mysqli_num_rows($select_cart_number); 
            ?>
            <a href="cart.php"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
         </div>

         <?php if (isset($_SESSION['user_id'])): ?>
         <div class="user-box">
            <p>username : <span><?php echo $_SESSION['user_name']; ?></span></p>
            <p>email : <span><?php echo $_SESSION['user_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">logout</a>
         </div>
         <?php endif; ?>
      </div>
   </div>

   <!-- Swiper for Categories -->
   <div class="category-swiper">
      <div class="swiper-container">
         <div class="swiper-wrapper">
            <?php
            // Display categories in the swiper
            if (mysqli_num_rows($select_categories) > 0) {
                while ($fetch_categories = mysqli_fetch_assoc($select_categories)) {
                    $category_id = intval($fetch_categories['id']); // Get the category ID
                    $category_name = htmlspecialchars($fetch_categories['name']); // Sanitize category name
                    echo '<div class="swiper-slide">
                            <div class="category-card">
                                <!-- Link category to shop.php with the category ID -->
                                <a href="shop.php?category_id=' . $category_id . '">' . $category_name . '</a>
                            </div>
                          </div>';
                }
            } else {
                echo '<p class="no-categories">No categories available</p>';
            }
            ?>
         </div>
      </div>
   </div>

</header>

<!-- Include Swiper CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<style>
.category-swiper {
    width: 100%;
    padding: 10px 0; /* Reduced padding */
}

.swiper-slide {
    display: flex;
    justify-content: center;
    align-items: center;
}

.category-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Lighter shadow */
    padding: 10px; /* Reduced padding */
    text-align: center;
    transition: transform 0.2s;
    width: 100%; 
    max-width: 150px; /* Reduced max-width */
}

.category-card a {
    text-decoration: none;
    color: #333;
    font-size: 14px; /* Reduced font size */
    font-weight: 500; /* Slightly bold */
}

.category-card:hover {
    transform: scale(1.05); /* Slight zoom effect */
}

.category-card a:hover {
    color: #ff6f61;
}

.no-categories {
    font-size: 14px;
    color: #666;
    text-align: center;
    padding: 20px;
}
</style>

<script>
const swiper = new Swiper('.swiper-container', {
    slidesPerView: 3, // Show 3 slides by default
    spaceBetween: 10, // Reduced space between slides
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    breakpoints: {
        // Responsive breakpoints
        320: {
            slidesPerView: 2,
            spaceBetween: 5,
        },
        640: {
            slidesPerView: 3,
            spaceBetween: 10,
        },
        768: {
            slidesPerView: 4,
            spaceBetween: 15,
        },
        1024: {
            slidesPerView: 5, // Show more slides on larger screens
            spaceBetween: 20,
        },
    },
});
</script>