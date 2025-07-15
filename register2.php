<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $phone = mysqli_real_escape_string($conn, $_POST['phone']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $user_type = $_POST['user_type'];

   // Validate name (must contain more than 3 alphabets)
   if (!preg_match("/^[a-zA-Z ]{4,}$/", $name)) {
      $message[] = 'Name must contain more than 3 alphabets!';
   }
   // Validate email (must be valid format and end with .com)
   elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/", $email)) {
      $message[] = 'Please enter a valid email ending with .com (e.g., example@domain.com)!';
   }
   // Validate phone (must be exactly 10 digits)
   elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
      $message[] = 'Phone number must be exactly 10 digits!';
   }
   // Check if email or phone already exists
   else {
      $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' OR phone = '$phone'") or die('query failed');

      if(mysqli_num_rows($select_users) > 0){
         $existing_user = mysqli_fetch_assoc($select_users);
         if ($existing_user['email'] === $email) {
            $message[] = 'Email already exists!';
         } elseif ($existing_user['phone'] === $phone) {
            $message[] = 'Phone number already exists!';
         }
      } else {
         if($pass != $cpass){
            $message[] = 'Confirm password does not match!';
         } else {
            mysqli_query($conn, "INSERT INTO `users`(name, email, phone, password, user_type) VALUES('$name', '$email', '$phone', '$cpass', '$user_type')") or die('query failed');
            $message[] = 'Registered successfully! Redirecting to login page...';
            // Display the success message for 3 seconds before redirecting
            echo '<script>
                  setTimeout(function() {
                     window.location.href = "login.php?registration=success";
                  }, 3000); // Redirect after 3 seconds
               </script>';
         }
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
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Style to indicate disabled input */
      input.disabled-keypad {
         background-color: #f5f5f5;
         cursor: not-allowed;
      }
   </style>

</head>
<body>

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
   
<div class="form-container">

   <form action="" method="post">
      <h3>register now</h3>
      <input type="text" name="name" placeholder="enter your name" required class="box" id="nameInput" onkeydown="validateNameInput(event)">
      <input type="email" name="email" placeholder="enter your email" required class="box" id="emailInput">
      <input type="text" name="phone" placeholder="enter your phone number" required class="box" id="phoneInput" onkeydown="validatePhoneInput(event)">
      <input type="password" name="password" placeholder="enter your password" required class="box">
      <input type="password" name="cpassword" placeholder="confirm your password" required class="box">
      <select name="user_type" class="box">
         <option value="user">user</option>
         <option value="admin">admin</option>
      </select>
      <input type="submit" name="submit" value="register now" class="btn">
   </form>

</div>

<script>
   // Function to validate name input (only alphabets and spaces)
   function validateNameInput(event) {
      const input = event.target;
      const key = event.key;
      
      // Allow backspace, delete, tab, escape, enter
      if ([8, 46, 9, 27, 13].includes(event.keyCode) || 
          // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
          (event.ctrlKey === true && [65, 67, 86, 88].includes(event.keyCode))) {
         return;
      }
      
      // Only allow alphabets and space
      if (!/^[a-zA-Z ]$/.test(key)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
   }

   // Function to validate phone input (only numbers)
   function validatePhoneInput(event) {
      const input = event.target;
      const key = event.key;
      
      // Allow backspace, delete, tab, escape, enter
      if ([8, 46, 9, 27, 13].includes(event.keyCode) || 
          // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
          (event.ctrlKey === true && [65, 67, 86, 88].includes(event.keyCode))) {
         return;
      }
      
      // Only allow numbers
      if (!/^[0-9]$/.test(key)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
      
      // Limit to 10 digits
      if (input.value.length >= 10 && ![8, 46].includes(event.keyCode)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
   }

   // Add event listeners for paste events to prevent invalid pasting
   document.getElementById('nameInput').addEventListener('paste', function(e) {
      e.preventDefault();
      this.classList.add('disabled-keypad');
      setTimeout(() => this.classList.remove('disabled-keypad'), 200);
   });

   document.getElementById('phoneInput').addEventListener('paste', function(e) {
      e.preventDefault();
      this.classList.add('disabled-keypad');
      setTimeout(() => this.classList.remove('disabled-keypad'), 200);
   });

   // Real-time email validation
   document.getElementById('emailInput').addEventListener('input', function(e) {
      const email = e.target.value;
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/;
      
      if (email && !emailRegex.test(email)) {
         e.target.setCustomValidity('Please enter a valid email ending with .com (e.g., example@domain.com)');
      } else {
         e.target.setCustomValidity('');
      }
   });
</script>

</body>
</html>