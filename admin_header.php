<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <div class="flex header-left">
      <i class="fas fa-bars" id="menu-btn"></i>
      <a href="admin_page.php" class="mobile-logo">Admin<span>Panel</span></a>
   </div>
</header>

<!-- Sidebar -->
<div class="sidebar-overlay"></div>
<aside class="sidebar">
   <div class="sidebar-header">
      <h3><a href="admin_page.php" class="logo">Admin<span>Panel</span></a></h3>
      <div class="close-btn"><i class="fas fa-times"></i></div>
   </div>

   <!-- Navigation -->
   <nav class="sidebar-nav">
      <a href="admin_page.php"><i class="fas fa-home"></i> <span>Home</span></a>
      <a href="admin_products.php"><i class="fas fa-box"></i> <span>Products</span></a>
      <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
      <a href="admin_users.php"><i class="fas fa-users"></i> <span>Users</span></a>
      <a href="admin_contacts.php"><i class="fas fa-envelope"></i> <span>Messages</span></a>
      <a href="admin_bookings.php"><i class="fas fa-calendar"></i> <span>View Bookings</span></a>
   </nav>
   
   <!-- Footer / Profile -->
   <div class="sidebar-footer">
      <div class="user-profile">
         <div class="profile-info">
            <i class="fas fa-user-circle"></i>
            <div>
               <h4>My Profile</h4>
               <p class="username"><?php echo $_SESSION['admin_name']; ?></p>
               <p class="email"><?php echo $_SESSION['admin_email']; ?></p>
            </div>
         </div>
      </div>
      <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
   </div>
</aside>

<style>
/* Main Layout Structure */
body {
   margin: 0;
   padding: 0;
   font-family: 'Poppins', sans-serif;
   transition: all 0.3s ease;
   min-height: 100vh;
   position: relative;
}

/* Header */
.header {
   background: #fff;
   padding: 1rem 2rem;
   box-shadow: 0 2px 10px rgba(0,0,0,0.1);
   position: fixed;
   top: 0;
   left: 0;
   right: 0;
   z-index: 1000;
   display: flex;
   align-items: center;
   height: 70px;
   transition: transform 0.3s ease;
}

.header-left {
   display: flex;
   align-items: center;
   gap: 1.5rem;
}

#menu-btn {
   font-size: 1.8rem;
   cursor: pointer;
   color: #333;
   transition: all 0.3s;
   position: relative;
   z-index: 1001;
}

.mobile-logo {
   font-size: 1.5rem;
   font-weight: 700;
   color: #333;
   text-decoration: none;
   display: none;
}

.mobile-logo span {
   color: #6c5ce7;
}

/* Sidebar */
.sidebar {
   position: fixed;
   top: 0;
   left: -280px;
   width: 280px;
   height: 100vh;
   background: #fff;
   box-shadow: 2px 0 15px rgba(0,0,0,0.1);
   transition: all 0.3s ease;
   z-index: 1001;
   display: flex;
   flex-direction: column;
}

.sidebar.active {
   left: 0;
}

.sidebar-header {
   padding: 1.5rem;
   display: flex;
   justify-content: space-between;
   align-items: center;
   border-bottom: 1px solid #eee;
}

.sidebar-header h3 {
   margin: 0;
   font-size: 1.3rem;
   color: #333;
   font-weight: 600;
}

.logo {
   font-size: 4rem;
   font-weight: 700;
   color: #333;
   text-decoration: none;
}

.logo span {
   color: #6c5ce7;
}

.close-btn {
   font-size: 1.5rem;
   color: #666;
   cursor: pointer;
}

.user-profile {
   padding: 1rem;
   border-bottom: 1px solid #eee;
}

.profile-info {
   display: flex;
   align-items: center;
   gap: 1rem;
}

.profile-info i {
   font-size: 2.5rem;
   color: #6c5ce7;
}

.profile-info h4 {
   margin: 0 0 0.3rem 0;
   font-size: 1.1rem;
   color: #666;
   font-weight: 600;
}

.profile-info .username {
   font-size: 1rem;
   font-weight: 600;
   color: #333;
}

.profile-info .email {
   font-size: 0.85rem;
   color: #999;
   word-break: break-word;
}

/* Navigation */
.sidebar-nav {
   flex: 1;
   padding: 2rem 0;
   overflow-y: auto;
}

.sidebar-nav a {
   display: flex;
   align-items: center;
   padding: 1rem 1.5rem;
   margin: 0.3rem 1rem;
   color: #555;
   text-decoration: none;
   border-radius: 8px;
   transition: all 0.3s;
}

.sidebar-nav a:hover {
   background: #f0f2f5;
   color: #6c5ce7;
   transform: translateX(5px);
}

.sidebar-nav a i {
   font-size: 2.2rem;
   margin-right: 1rem;
   width: 24px;
   text-align: center;
}

/* Footer */
.sidebar-footer {
   padding: 1.5rem;
   border-top: 1px solid #eee;
}

.logout-btn {
   display: flex;
   align-items: center;
   padding: 1rem 1.5rem;
   color: #ff4757;
   text-decoration: none;
   font-weight: 600;
   border-radius: 8px;
   transition: all 0.3s;
}

.logout-btn:hover {
   background: #ffebee;
   transform: translateX(5px);
}

.logout-btn i {
   margin-right: 1rem;
   font-size: 1.8rem;
}

/* Overlay */
.sidebar-overlay {
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(0,0,0,0.5);
   z-index: 1000;
   opacity: 0;
   visibility: hidden;
   transition: all 0.3s;
}

.sidebar-overlay.active {
   opacity: 1;
   visibility: visible;
}

/* Content */
.main-content {
   margin-top: 70px;
   padding: 2rem;
   transition: all 0.3s ease;
   min-height: calc(100vh - 70px);
}

/* Responsive */
@media (min-width: 992px) {
   .sidebar {
      left: 0;
   }

   .main-content {
      margin-left: 280px;
   }

   #menu-btn {
      display: none;
   }

   .close-btn {
      display: none;
   }

   .sidebar-overlay {
      display: none;
   }

   .mobile-logo {
      display: none;
   }
}

@media (max-width: 991px) {
   .sidebar {
      left: -280px;
   }

   .sidebar.active {
      left: 0;
   }

   .main-content {
      margin-left: 0;
   }

   .mobile-logo {
      display: block;
   }
   
   /* Push content when sidebar is open */
   body.sidebar-open {
      overflow: hidden;
   }
   
   .sidebar-open .main-content,
   .sidebar-open .header {
      transform: translateX(280px);
   }
}

@media (max-width: 576px) {
   .header {
      padding: 1rem;
      height: 60px;
   }
   
   .main-content {
      margin-top: 60px;
      padding: 1rem;
   }
   
   .sidebar {
      width: 260px;
      left: -260px;
   }
   
   .sidebar-nav a {
      padding: 0.8rem 1.2rem;
   }
   
   .profile-info i {
      font-size: 2.5rem;
   }
   
   .profile-info h4 {
      font-size: 1rem;
   }
   
   .profile-info .username {
      font-size: 0.9rem;
   }
   
   .profile-info .email {
      font-size: 0.8rem;
   }
   
   /* Adjust push for mobile */
   .sidebar-open .main-content,
   .sidebar-open .header {
      transform: translateX(260px);
   }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const menuBtn = document.getElementById('menu-btn');
   const sidebar = document.querySelector('.sidebar');
   const closeBtn = document.querySelector('.close-btn');
   const overlay = document.querySelector('.sidebar-overlay');
   const body = document.body;
   const header = document.querySelector('.header');

   const openSidebar = () => {
      sidebar.classList.add('active');
      overlay.classList.add('active');
      body.classList.add('sidebar-open');
   };

   const closeSidebar = () => {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
      body.classList.remove('sidebar-open');
   };

   menuBtn?.addEventListener('click', openSidebar);
   closeBtn?.addEventListener('click', closeSidebar);
   overlay?.addEventListener('click', closeSidebar);

   // Close sidebar when clicking outside on mobile
   document.addEventListener('click', function(event) {
      if (window.innerWidth < 992 && 
          !sidebar.contains(event.target) && 
          event.target !== menuBtn &&
          !menuBtn.contains(event.target)) {
         closeSidebar();
      }
   });

   // Handle window resize
   function handleResize() {
      if (window.innerWidth >= 992) {
         closeSidebar();
      }
   }

   window.addEventListener('resize', handleResize);
});
</script>