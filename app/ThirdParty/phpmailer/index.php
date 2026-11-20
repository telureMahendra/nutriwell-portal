<?php

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Create an instance of PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 0;                                       // Disable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'host5.xhost.co.in';                     // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'support@healthie4u.com';               // SMTP username
    $mail->Password   = 'support@healthie4u';                  // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;                                    // TCP port to connect to

    // Recipients
    $mail->setFrom('support@healthie4u.com', 'healthie4u');
    $mail->addAddress('kiran.hujare@gmail.com', 'kiran hujare'); // Add a recipient

    // Attachments (if needed)
    // $mail->addAttachment('/path/to/file.pdf');               // Add attachments

    // Content
    $mail->isHTML(true);                                        // Set email format to HTML
    $mail->Subject = 'Test Email using PHPMailer';
    $mail->Body    = '<p>This is a test email sent using PHPMailer in PHP 8.0.</p>';

    // Send the email
    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
