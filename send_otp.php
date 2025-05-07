<?php
session_start();  // Start session
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        echo "No email provided.";
        exit;
    }

    $email = $_POST['email'];
    $otp_code = rand(1000, 9999);

    $_SESSION['otp'] = $otp_code;
    $_SESSION['email'] = $email;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.elasticemail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kafle.christina@gmail.com';
        $mail->Password   = '31B9DBA7C60EF35C6D999FA9799B0F61CD76'; // replace
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('kafle.christina@gmail.com', 'christina kafle');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "<h1>Your OTP is: $otp_code</h1>";

        $mail->send();
        echo "OTP sent successfully";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
