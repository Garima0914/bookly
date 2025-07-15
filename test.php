<?php
require 'sendmail.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$to = "max984103@gmail.com"; // Replace with your email for testing
$subject = "Test Email from bookly";
$body = "<h3>Hello, this is a test email from bookly!</h3>";

if (sendMail($to, $subject, $body)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>