<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "User not logged in.";
    exit;
} else {
    // Continue with fetching notifications
    $username = $_SESSION['username'];
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

// Fetch notifications
$sql = "SELECT * FROM notifications WHERE username = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Kay+Pho+Du&display=swap" rel="stylesheet">
    <style>
        /* Reset Defaults */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Times New Roman', serif;
            color: #0F366D;
        }

        /* Page Background */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f8f8;
        }

        /* Container for the App */
        .container {
            width: 430px;
            height: 932px;
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Back Button */
        .back-btn {
            background: none;
            border: none;
            cursor: pointer;
            position: absolute;
            top: 20px;
            left: 15px;
        }

        .back-btn img {
            width: 30px;
        }

        /* Title */
        .title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-top: 15px;
        }

        /* Search Box */
        .search-box {
            display: flex;
            align-items: center;
            background-color: #c2d2e9;
            border-radius: 30px;
            padding: 12px;
            margin-top: 25px;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        .search-icon {
            width: 24px;
            margin-right: 12px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: none;
            font-size: 18px;
            width: 100%;
        }

        /* Notification List */
        .notification-list {
            margin-top: 25px;
        }

        /* Notification Item */
        .notification-item {
            display: flex;
            align-items: center;
            background-color: #dde7f0;
            border-radius: 15px;
            padding: 16px;
            margin-bottom: 14px;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bill-icon {
            width: 45px;
            height: 45px;
            margin-right: 12px;
        }

        .details {
            flex-grow: 1;
        }

        .bill-title {
            font-size: 18px;
            font-weight: normal;
        }

        .bill-date {
            font-size: 14px;
            font-weight: normal;
        }

        .bill-amount {
            font-size: 18px;
            font-weight: bold;
            margin-right: 12px;
        }

        .red {
            color: red;
        }

        .bill-status {
            font-size: 14px;
            font-weight: bold;
        }

        /* Responsive Design */
        @media screen and (max-width: 430px) {
            .container {
                width: 100%;
                height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <button class="back-btn" onclick="window.location.href='Home.php';">
            <img src="icons/Back.png" alt="Back">
        </button>

        <!-- Title -->
        <h2 class="title">Notifications</h2>

        <!-- Search Box -->
        <div class="search-box">
            <img src="icons/search.png" alt="Search" class="search-icon">
            <input type="text" placeholder="Search">
        </div>

        <!-- Notification List -->
        <div class="notification-list">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <!-- Notification Icon Based on Category -->
                    <img src="icons/<?= htmlspecialchars($notification['category']) ?>.png" alt="<?= htmlspecialchars($notification['category']) ?>" class="bill-icon">

                    <div class="details">
                        <!-- Title and Date -->
                        <p class="bill-title"><?= htmlspecialchars(ucfirst($notification['category'])) ?> Bills</p>
                        <p class="bill-date"><?= htmlspecialchars($notification['date']) ?></p>
                    </div>

                    <!-- Amount and Status -->
                    <p class="bill-amount red"><?= htmlspecialchars($notification['amount']) ?>$</p>
                    <p class="bill-status"><?= htmlspecialchars($notification['status']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
