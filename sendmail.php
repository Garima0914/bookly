<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Correct the path based on your directory structure
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

function sendMail($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'max984103@gmail.com'; // Your email
        $mail->Password = 'xeauzuvlszrcadqp';         // Your email password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Optional: Enable verbose debug output for troubleshooting (set to 0 in production)
        // $mail->SMTPDebug = 2;

        // Sender & recipient settings
        $mail->setFrom('suta123@gmail.com', 'Bookly');
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error message for debugging
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>