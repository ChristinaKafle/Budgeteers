<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'] ?? '';

    if (isset($_SESSION['otp']) && $user_otp == $_SESSION['otp']) {
        echo "OTP verified";
    } else {
        echo "Invalid OTP";
    }
}
?>
