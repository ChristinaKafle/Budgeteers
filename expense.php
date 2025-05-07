<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

// Step 1: Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Walletok";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Step 2: Receive data from POST
$username = $_SESSION['username'];
$date = $_POST['date'] ?? '';
$categories = ['shopping', 'bills', 'food', 'investment', 'transportation', 'others'];

$hasEntry = false;

// Step 3: Prepare insert statement
$stmt = $conn->prepare("INSERT INTO transactions (username, type, category, amount, date, description) VALUES (?, 'expense', ?, ?, ?, ?)");

foreach ($categories as $category) {
    $amount = $_POST[$category] ?? '';
    $remarks = $_POST[$category . '_remarks'] ?? '';

    // Ensure amount is a valid positive number and remarks are filled
    if (is_numeric($amount) && $amount > 0 && !empty($remarks)) {
        $stmt->bind_param("ssdss", $username, $category, $amount, $date, $remarks);
        $stmt->execute();
        $hasEntry = true;
    }
}

// Step 4: Respond to client
if ($hasEntry) {
    echo json_encode(["status" => "success", "message" => "Expenses saved successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "No valid expense data to save."]);
}
// Insert notification for each valid expense
$notification_sql = "INSERT INTO notifications (username, category, amount, date, status) 
                     VALUES (?, ?, ?, ?, 'paid')";
$notification_stmt = $conn->prepare($notification_sql);

foreach ($categories as $category) {
    $amount = $_POST[$category] ?? '';
    $remarks = $_POST[$category . '_remarks'] ?? '';

    if (is_numeric($amount) && $amount > 0 && !empty($remarks)) {
        // Insert the notification
        $notification_stmt->bind_param("ssds", $username, $category, $amount, $date);
        $notification_stmt->execute();
    }
}

$notification_stmt->close();

$stmt->close();
$conn->close();
?>
