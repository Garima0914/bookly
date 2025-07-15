<?php

$conn = mysqli_connect('localhost','root','','shop_db') or die('connection failed');

putenv('EMAIL_USERNAME=your_email@gmail.com');
putenv('EMAIL_PASSWORD=your_secure_app_password');

?>