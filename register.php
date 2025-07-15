<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
      $message[] = 'Name must contain more than 3 alphabets and only letters!';
   }
   // Validate email (proper email format)
   elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message[] = 'Please enter a valid email address!';
   }
   // Validate phone (must be valid Nepal number: 98######## or +97798########)
   elseif (!preg_match("/^(98[0-9]{8}|\+97798[0-9]{8})$/", $phone)) {
      $message[] = 'Phone number must be a valid Nepal number (e.g., 9841234567 or +9779841234567)!';
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
            echo '<script>
                  setTimeout(function() {
                     window.location.href = "login.php?registration=success";
                  }, 3000);
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      input.disabled-keypad {
         background-color: #f5f5f5;
         cursor: not-allowed;
      }
      .error-message {
         color: #ff4444;
         font-size: 12px;
         margin-top: -10px;
         margin-bottom: 10px;
         display: none;
      }
      .valid {
         border-color: #00C851 !important;
      }
      .invalid {
         border-color: #ff4444 !important;
      }
      .input-group {
         margin-bottom: 15px;
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
   <form action="" method="post" id="registrationForm">
      <h3>register now</h3>
      
      <div class="input-group">
         <input type="text" name="name" placeholder="Enter your full name" required class="box" id="nameInput" 
                onkeydown="validateNameInput(event)" oninput="validateName()">
         <div class="error-message" id="nameError">Name must contain at least 4 alphabets and only letters</div>
      </div>
      
      <div class="input-group">
         <input type="email" name="email" placeholder="Enter your email (e.g., user@example.com)" required class="box" 
                id="emailInput" onkeydown="validateEmailInput(event)" oninput="validateEmail()">
         <div class="error-message" id="emailError">Please enter a valid email address</div>
      </div>
      
      <div class="input-group">
         <input type="text" name="phone" placeholder="Enter your phone (e.g., 9841234567)" required class="box" 
                id="phoneInput" onkeydown="validatePhoneInput(event)" oninput="validatePhone()">
         <div class="error-message" id="phoneError">Please enter a valid Nepal number (98######## or +97798########)</div>
      </div>
      
      <div class="input-group">
         <input type="password" name="password" placeholder="Enter your password" required class="box" id="passwordInput" 
                oninput="validatePassword()">
         <div class="error-message" id="passwordError">Password must be at least 6 characters</div>
      </div>
      
      <div class="input-group">
         <input type="password" name="cpassword" placeholder="Confirm your password" required class="box" 
                id="cpasswordInput" oninput="validateConfirmPassword()">
         <div class="error-message" id="cpasswordError">Passwords do not match</div>
      </div>
      
      <select name="user_type" class="box">
         <option value="user">user</option>
      </select>
      
      <input type="submit" name="submit" value="register now" class="btn" id="submitBtn">
      <p>already have an account? <a href="login.php">login now</a></p>
   </form>
</div>

<script>
   // Name validation
   function validateNameInput(event) {
      const input = event.target;
      const key = event.key;
      
      if ([8, 46, 9, 27, 13].includes(event.keyCode) || 
          (event.ctrlKey === true && [65, 67, 86, 88].includes(event.keyCode))) {
         return;
      }
      
      if (!/^[a-zA-Z ]$/.test(key)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
   }

   // Email validation - allow standard email characters
   function validateEmailInput(event) {
      const input = event.target;
      const key = event.key;
      
      if ([8, 46, 9, 27, 13].includes(event.keyCode) || 
          (event.ctrlKey === true && [65, 67, 86, 88].includes(event.keyCode))) {
         return;
      }
      
      // Allow: alphanumeric, @, ., _, -, +
      if (!/^[a-zA-Z0-9@._+-]$/.test(key)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
      
      // Prevent multiple @ symbols
      if (key === '@' && input.value.includes('@')) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
   }

   // Phone validation (Nepal numbers)
   function validatePhoneInput(event) {
      const input = event.target;
      const key = event.key;
      const currentValue = input.value;
      
      if ([8, 46, 9, 27, 13].includes(event.keyCode) || 
          (event.ctrlKey === true && [65, 67, 86, 88].includes(event.keyCode))) {
         return;
      }
      
      // Allow + only at start
      if (key === '+' && currentValue.length === 0) {
         return;
      }
      
      // Only allow numbers after +
      if (!/^[0-9]$/.test(key)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
      
      // Length limits
      const maxLength = currentValue.startsWith('+977') ? 14 : 10;
      if (currentValue.length >= maxLength && ![8, 46].includes(event.keyCode)) {
         event.preventDefault();
         input.classList.add('disabled-keypad');
         setTimeout(() => input.classList.remove('disabled-keypad'), 200);
      }
   }

   // Field validation functions
   function validateName() {
      const input = document.getElementById('nameInput');
      const error = document.getElementById('nameError');
      const isValid = /^[a-zA-Z ]{4,}$/.test(input.value);
      
      input.classList.toggle('valid', isValid);
      input.classList.toggle('invalid', !isValid);
      error.style.display = isValid ? 'none' : 'block';
      return isValid;
   }

   function validateEmail() {
      const input = document.getElementById('emailInput');
      const error = document.getElementById('emailError');
      // Use proper email validation regex
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      const isValid = emailRegex.test(input.value);
      
      input.classList.toggle('valid', isValid);
      input.classList.toggle('invalid', !isValid);
      error.style.display = isValid ? 'none' : 'block';
      return isValid;
   }

   function validatePhone() {
      const input = document.getElementById('phoneInput');
      const error = document.getElementById('phoneError');
      const isValid = /^(98[0-9]{8}|\+97798[0-9]{8})$/.test(input.value);
      
      input.classList.toggle('valid', isValid);
      input.classList.toggle('invalid', !isValid);
      error.style.display = isValid ? 'none' : 'block';
      return isValid;
   }

   function validatePassword() {
      const input = document.getElementById('passwordInput');
      const error = document.getElementById('passwordError');
      const isValid = input.value.length >= 6;
      
      input.classList.toggle('valid', isValid);
      input.classList.toggle('invalid', !isValid);
      error.style.display = isValid ? 'none' : 'block';
      return isValid;
   }

   function validateConfirmPassword() {
      const password = document.getElementById('passwordInput').value;
      const input = document.getElementById('cpasswordInput');
      const error = document.getElementById('cpasswordError');
      const isValid = input.value === password;
      
      input.classList.toggle('valid', isValid);
      input.classList.toggle('invalid', !isValid);
      error.style.display = isValid ? 'none' : 'block';
      return isValid;
   }

   // Form submission
   document.getElementById('registrationForm').addEventListener('submit', function(e) {
      const isValid = validateName() && validateEmail() && validatePhone() && 
                     validatePassword() && validateConfirmPassword();
      
      if (!isValid) {
         e.preventDefault();
         // Ensure all errors are visible
         document.querySelectorAll('.error-message').forEach(el => {
            const input = el.previousElementSibling;
            if (input.classList.contains('invalid')) {
               el.style.display = 'block';
            }
         });
      }
   });

   // Prevent paste on restricted fields
   document.getElementById('nameInput').addEventListener('paste', e => e.preventDefault());
   document.getElementById('emailInput').addEventListener('paste', e => e.preventDefault());
   document.getElementById('phoneInput').addEventListener('paste', e => e.preventDefault());
</script>

</body>
</html>