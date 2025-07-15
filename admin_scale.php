<?php
include 'config.php';
session_start();

// Ensure the admin is logged in
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Fetch total books sold by category (genre)
$categories = [
    'Action and Adventure', 'Bildungsroman', 'Coming-of-age story', 'Fiction', 'Gay-fiction',
    'Legal story', 'Legal thriller', 'Mystery', 'Nature', 'Non-fiction',
    'Novel', 'Politics', 'Psychological Fiction', 'Psychological thriller',
    'Psychology', 'Romance', 'Suspense', 'Thriller'
];

$category_sales = array_fill_keys($categories, 0); // Initialize sales for each category to 0

// Fetch all completed orders
$select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'completed'") or die('query failed');

if (mysqli_num_rows($select_orders) > 0) {
    while ($fetch_order = mysqli_fetch_assoc($select_orders)) {
        preg_match_all('/\((\d+)\)/', $fetch_order['total_products'], $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $quantity) {
                $product_name = explode('(', $fetch_order['total_products'])[0];
                $product_name = trim($product_name);

                $select_product = mysqli_query($conn, "SELECT c.name AS category FROM products p INNER JOIN product_categories pc ON p.id = pc.product_id INNER JOIN categories c ON pc.category_id = c.id WHERE p.name = '$product_name'") or die('query failed: ' . mysqli_error($conn));
                if (mysqli_num_rows($select_product) > 0) {
                    $fetch_product = mysqli_fetch_assoc($select_product);
                    $category = $fetch_product['category'];
                    if (in_array($category, $categories)) {
                        $category_sales[$category] += (int)$quantity;
                    }
                }
            }
        }
    }
}

// Sort categories by sales in descending order
arsort($category_sales);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Genre Sales</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

   <!-- Chart.js library -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <style>
      body {
         background-color: #f0f0f0;
         font-family: Arial, sans-serif;
      }
      .main-content {
         margin-left: 270px; /* Same as sidebar width */
         padding: 20px;
      }
      .chart-container {
         width: 100%;
         max-width: 900px;
         margin: 20px auto;
         background-color: #fff;
         padding: 20px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         border-radius: 10px;
      }
      .title {
         text-align: center;
         color: #333;
         margin-bottom: 20px;
         font-size: 28px;
         font-weight: bold;
      }
   </style>
</head>

<body>
<?php include 'admin_header.php'; ?>

<section class="main-content">
   <h1 class="title">Most to Least Sold Products by Category</h1>

   <div class="chart-container">
      <canvas id="categorySalesChart"></canvas>
   </div>
</section>

<!-- Chart.js Script -->
<script>
const categoryLabels = <?php echo json_encode(array_keys($category_sales)); ?>;
const categoryData = <?php echo json_encode(array_values($category_sales)); ?>;

const ctx = document.getElementById('categorySalesChart').getContext('2d');
const categorySalesChart = new Chart(ctx, {
   type: 'bar',
   data: {
      labels: categoryLabels,
      datasets: [{
         label: 'Total Books Sold',
         data: categoryData,
         backgroundColor: 'rgba(75, 192, 192, 0.6)',
         borderColor: 'rgba(0, 128, 128, 1)',
         borderWidth: 3,
         barThickness: 40
      }]
   },
   options: {
      responsive: true,
      plugins: {
         legend: {
            labels: {
               font: {
                  size: 16,
                  weight: 'bold'
               }
            }
         },
         title: {
            display: true,
            text: 'Total Sales by Book Category',
            font: {
               size: 20,
               weight: 'bold'
            }
         }
      },
      scales: {
         x: {
            ticks: {
               font: {
                  size: 14,
                  weight: 'bold'
               }
            }
         },
         y: {
            beginAtZero: true,
            ticks: {
               font: {
                  size: 14,
                  weight: 'bold'
               }
            },
            grid: {
               color: 'rgba(0, 0, 0, 0.2)',
               lineWidth: 1
            }
         }
      }
   }
});
</script>

</body>
</html>