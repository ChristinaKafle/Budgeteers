<?php
session_start(); // Start the session to access the logged-in user

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Walletok";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Get the logged-in user's username from the session
$loggedInUser = $_SESSION['username'];

// Query to get the 10 most recent transactions for the logged-in user
$query = "SELECT * FROM transactions WHERE username = '$loggedInUser' ORDER BY date DESC"; 
$result = $conn->query($query);

// Check if there are results
if ($result->num_rows > 0) {
    $transactionsData = [];
    while ($row = $result->fetch_assoc()) {
        $transactionsData[] = [
            'date' => $row['date'],
            'income' => $row['type'] == 'income' ? $row['amount'] : 0,
            'shopping' => $row['category'] == 'shopping' ? $row['amount'] : 0,
            'bills' => $row['category'] == 'bills' ? $row['amount'] : 0,
            'food' => $row['category'] == 'food' ? $row['amount'] : 0,
            'groceries' => $row['category'] == 'groceries' ? $row['amount'] : 0,
            'transport' => $row['category'] == 'transport' ? $row['amount'] : 0,
            'others' => $row['category'] != 'shopping' && $row['category'] != 'bills' && $row['category'] != 'food' && $row['category'] != 'groceries' && $row['category'] != 'transport' ? $row['amount'] : 0,
            'remarks' => $row['description']
        ];
    }
    echo json_encode($transactionsData); // Send the data to the front end
} else {
    echo json_encode(['error' => 'No records found']);
}

$conn->close();
?>
