<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
$username = $_SESSION['username'];

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$database = "Walletok";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total income
$incomeResult = $conn->query("SELECT SUM(amount) as total_income FROM transactions WHERE username = '$username' AND type = 'income'");
$incomeRow = $incomeResult->fetch_assoc();
$totalIncome = $incomeRow['total_income'] ?? 0;

// Fetch total expense
$expenseResult = $conn->query("SELECT SUM(amount) as total_expense FROM transactions WHERE username = '$username' AND type = 'expense'");
$expenseRow = $expenseResult->fetch_assoc();
$totalExpense = $expenseRow['total_expense'] ?? 0;

// Calculate balance
$balance = $totalIncome - $totalExpense;

// Fetch 3 most recent transactions
$recentQuery = "SELECT category, description, amount, type, date 
                FROM transactions 
                WHERE username = '$username' 
                ORDER BY date DESC 
                LIMIT 3";
$recentResult = $conn->query($recentQuery);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walletok App</title>
    <link rel="stylesheet" href="Home.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        <button class="notification-btn" onclick="window.location.href='notifications.php'">
            <img src="icons/Vector.png" alt="Notification">
        </button>
    </header>

    <section class="balance-box">
    <h2>Total Balance</h2>
    <p class="total" title="<?php echo $balance < 0 ? 'You might have forgotten to enter your incomes.' : ''; ?>">
        $<?php echo number_format($balance, 2); ?>
    </p>
    
    <?php if ($balance < 0): ?>
        <p class="balance-note" style="font-size: 0.9em; color: #dc3545; margin-top: 5px;">
            (If the amount is in minus, it indicates that you may have forgotten to log your income)
        </p>
    <?php endif; ?>

    <div class="income-expense">
        <span class="income">Income <br> $<?php echo number_format($totalIncome, 2); ?></span>
        <span class="divider"></span>
        <span class="expense">Expense <br> $<?php echo number_format($totalExpense, 2); ?></span>
    </div>
</section>


    <div class="toggle-buttons">
        <button class="expense-btn" onclick="window.location.href='expense.html'">Expense</button>
        <button class="income-btn" onclick="window.location.href='income.html'">Income</button>
    </div>

    <section class="recent-activity">
        <h3>Recent Activity</h3>

        <?php while($row = $recentResult->fetch_assoc()): ?>
            <?php
                $categoryIcon = "icons/" . htmlspecialchars($row['category']) . ".png";
                if (!file_exists($categoryIcon)) {
                    $categoryIcon = "icons/Logo.png";
                }
            ?>
            <div class="activity">
                <img src="<?php echo $categoryIcon; ?>" class="icon" alt="<?php echo htmlspecialchars($row['category']); ?>">
                <div class="details">
                    <p class="title"><?php echo htmlspecialchars($row['category']); ?></p>
                    <p class="subtitle"><?php echo htmlspecialchars($row['description']); ?></p>
                </div>
                <div class="amount-date">
                    <span class="amount" style="color: <?php echo $row['type'] === 'income' ? '#28a745' : '#dc3545'; ?>">
                        $<?php echo number_format($row['amount'], 2); ?><br>
                    </span>
                    <span class="date"><?php echo date('F j, Y', strtotime($row['date'])); ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    </section>

    <footer>
        <div class="nav-icons">
            <img src="icons/mage_goals-fill.png" alt="Goals" onclick="window.location.href='Goals.html'" style="cursor:pointer;">
            <img src="icons/menu.png" alt="Menu" onclick="window.location.href='table.html'" style="cursor:pointer;">
            
            <div class="logo">
                <img src="icons/Logo.png" alt="Walletok Logo">
            </div>
            
            <img src="icons/Growth Graph.png" alt="Stats" onclick="window.location.href='diagram.html'" style="cursor:pointer;">
            <img src="icons/Profile.png" alt="Profile" onclick="window.location.href='profile.php'" style="cursor:pointer;">
        </div>
    </footer>
</div>

</body>
</html>
