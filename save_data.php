<?php
// Step 1: Connect to database
$servername = "localhost";
$username = "root";
$password = ""; // leave empty if you have no password
$database = "Walletok";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Receive data from frontend (POST method)
$date = $_POST['date'];
$shopping = $_POST['shopping'];
$bills = $_POST['bills'];
$food = $_POST['food'];
$investment = $_POST['investment'];
$transportation = $_POST['transportation'];
$others = $_POST['others'];
$remarks = $_POST['remarks'];

// Step 3: Insert data into table
$sql = "INSERT INTO records (user_id, date, shopping, bills, food, investment, transportation, others, remarks)
VALUES (1, '$date', '$shopping', '$bills', '$food', '$investment', '$transportation', '$others', '$remarks')";

if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Step 4: Close connection
$conn->close();
?>

