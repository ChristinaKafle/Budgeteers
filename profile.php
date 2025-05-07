<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "Walletok"); // change DB name

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get email
$user_stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result()->fetch_assoc();
$email = $user_result['email'] ?? 'N/A';

// Get total income
$income_stmt = $conn->prepare("SELECT SUM(amount) AS total_income FROM transactions WHERE username = ? AND type = 'income'");
$income_stmt->bind_param("s", $username);
$income_stmt->execute();
$income_result = $income_stmt->get_result()->fetch_assoc();
$total_income = $income_result['total_income'] ?? 0;

// Get total spending
$spending_stmt = $conn->prepare("SELECT SUM(amount) AS total_spending FROM transactions WHERE username = ? AND type = 'expense'");
$spending_stmt->bind_param("s", $username);
$spending_stmt->execute();
$spending_result = $spending_stmt->get_result()->fetch_assoc();
$total_spending = $spending_result['total_spending'] ?? 0;

// Rating logic
$rating = 'N/A';
if ($total_income > 0) {
    $ratio = $total_spending / $total_income;
    if ($ratio < 0.5) $rating = "Excellent";
    elseif ($ratio < 0.8) $rating = "Good";
    elseif ($ratio < 1) $rating = "Fair";
    else $rating = "Poor";
}

// Top 3 categories
$popular_stmt = $conn->prepare("
    SELECT category, COUNT(*) as count 
    FROM transactions 
    WHERE username = ? AND type = 'expense'
    GROUP BY category 
    ORDER BY count DESC 
    LIMIT 3
");
$popular_stmt->bind_param("s", $username);
$popular_stmt->execute();
$popular_result = $popular_stmt->get_result();
$popular_categories = [];
while ($row = $popular_result->fetch_assoc()) {
    $popular_categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Profile - <?php echo htmlspecialchars($username); ?></title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Kay+Pho+Du&display=swap'); /* Import the font, if available */

:root {
    --iphone-width: 430px;
    --iphone-height: 932px;
    --primary-color: #5E35B1;
    --secondary-color: #7E57C2;
    --text-dark: #0F366D;  /* Updated text color */
    --text-light: #78909C;
    --border-color: #E0E0E0;
    --card-bg: #FFFFFF;
    --section-bg: #FAFAFA;
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-tap-highlight-color: transparent;
}

body {
    font-family: 'Kay Pho Du', sans-serif; /* Updated font */
    background-color: var(--section-bg);
    color: var(--text-dark);  /* Updated text color */
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}

.iphone-container {
    width: var(--iphone-width);
    height: var(--iphone-height);
    background-color: var(--card-bg);
    position: relative;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 40px;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.2);
    padding: 60px 24px 80px;
}

/* ✅ Back Button - Fixed */
.back-button {
    position: fixed;
    top: 20px;
    left: 20px;
    width: 40px;
    height: 40px;
    background-image: url('icons/Back.png'); /* adjust if your image path differs */
    background-size: 24px 24px;
    background-repeat: no-repeat;
    background-position: center;
    background-color: rgba(0, 0, 0, 0.05); /* fallback circle background */
    border: none;
    border-radius: 50%;
    cursor: pointer;
    z-index: 1000;
    transition: all 0.3s ease;
}

.back-button:hover {
    transform: scale(1.1);
    background-color: rgba(0, 0, 0, 0.1);
}

/* Profile Section */
.profile-container {
    background-color: #103a70;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    color: white;
    width: 365px;
}

.profile-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 15px;
}

.profile-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #6c86aa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-name {
    font-size: 20px;
    font-weight: bold;
    margin-top: 10px;
}

.profile-title {
    font-size: 16px;
    color: #cbd6e2;
}

.profile-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
}

.stat-title {
    font-size: 13px;
}

.stat-value {
    font-size: 14px;
    font-weight: bold;
}

/* Section Title */
.section-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 24px;
    margin-top: 20px;
    color: var(--text-dark);  /* Updated text color */
    display: flex;
    align-items: center;
}

.section-title::before {
    display: inline-block;
    width: 4px;
    height: 18px;
    background-color: var(--primary-color);
    margin-right: 12px;
    border-radius: 2px;
}

/* Popular List */
/* Popular List */
.popular-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 40px;
    text-align: center;
    align-items: center;
}

.popular-item {
    background-color: transparent;
    box-shadow: none;
    border-radius: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.popular-item img {
    width: 90px;
    height: 90px;
    margin-bottom: 8px;
}

.popular-item span {
    font-size: 15px;
    font-weight: 700; /* Make the text bold */
    color: #103a70;
}

.popular-item:active {
    transform: scale(0.98);
}

/* Sign Out Button */
.sign-out-btn {
    width: 100%;
    padding: 12px;
    background-color: #103a70;
    color: #CBD4E2;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    margin-top: 20px;
    cursor: pointer;
    transition: background-color 0.2s;
    box-shadow: none;
}

.sign-out-btn:active {
    background-color: #6A83A7;
}
</style>
</head>
<body>
  <div class="iphone-container">
    <button class="back-button" onclick="goBack()"></button>

    <div class="profile-container">
      <div class="profile-header">
        <img src="icons/profile.png" alt="Profile" class="profile-image" />
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($username); ?></div>
          <div class="profile-title"><?php echo htmlspecialchars($email); ?></div>
        </div>
      </div>
      <div class="profile-stats">
        <div class="stat-item">
          <div class="stat-title">Total income</div>
          <div class="stat-value">₹<?php echo number_format($total_income, 2); ?></div>
        </div>
        <div class="stat-item">
          <div class="stat-title">Total spendings</div>
          <div class="stat-value">₹<?php echo number_format($total_spending, 2); ?></div>
        </div>
        <div class="stat-item">
          <div class="stat-title">Ratings</div>
          <div class="stat-value"><?php echo $rating; ?></div>
        </div>
      </div>
    </div>

    <div class="section-title">Most popular</div>
    <div class="popular-list">
      <?php foreach ($popular_categories as $cat): ?>
        <div class="popular-item">
          <img src="icons/<?php echo ucfirst($cat); ?>.png" alt="<?php echo htmlspecialchars($cat); ?>" />
          <span><?php echo htmlspecialchars($cat); ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <button class="sign-out-btn">Sign out</button>
    <script>
  function goBack() {
    window.location.href = "Home.php";
  }

  document.querySelector(".sign-out-btn").addEventListener("click", function () {
    Swal.fire({
      title: 'Sign out?',
      text: "Are you sure you want to sign out?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, sign out',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "landingpage.html";
      }
    });
  });
</script>

</body>
</html>
