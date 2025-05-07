<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo "User not logged in.";
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method not allowed
    echo "405 Method Not Allowed";
    exit;
}

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "Walletok";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$username = $_SESSION['username'];
$amount = $_POST['amount'] ?? '';
$description = $_POST['source'] ?? ''; // use as remarks/description
$date = $_POST['income_date'] ?? '';

// Validate data
if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
    echo "Invalid amount.";
    exit;
}

if (empty($description)) {
    echo "Description (remarks) is required.";
    exit;
}

if (empty($date)) {
    echo "Date is required.";
    exit;
}

// Insert into transactions table
$sql = "INSERT INTO transactions (username, type, category, amount, date, description) 
        VALUES (?, 'income', 'income', ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sdss", $username, $amount, $date, $description);

if ($stmt->execute()) {
    echo "Income saved successfully!";
} else {
    echo "Error: " . $stmt->error;
}
// Insert notification for income
$notification_sql = "INSERT INTO notifications (username, category, amount, date, status) 
                     VALUES (?, 'income', ?, ?, 'paid')";
$notification_stmt = $conn->prepare($notification_sql);
$notification_stmt->bind_param("sds", $username, $amount, $date);

if ($notification_stmt->execute()) {
    // You can log or echo the success message here if needed.
} else {
    echo "Error inserting notification: " . $notification_stmt->error;
}

$notification_stmt->close();

$stmt->close();
$conn->close();
?>
