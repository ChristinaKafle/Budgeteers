<?php
// Step 1: DB connection
$servername = "localhost";
$username = "root";
$password = ""; // Update this if your MySQL server has a password
$dbname = "expense_tracker";

// Step 2: Connect and create DB if not exists
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Step 3: Create 'users' table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        occupation VARCHAR(100),
        address VARCHAR(255),
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )
");

// Step 4: Create 'transactions' table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type ENUM('income', 'expense') NOT NULL,
        category VARCHAR(100),
        amount DECIMAL(10,2),
        date DATE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )
");

// Step 5: Handle form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & validate input
    $username = trim($_POST['username']);
    $occupation = trim($_POST['occupation']);
    $address = trim($_POST['address']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!$email) {
        showAlert('error', 'Invalid Email', 'Please enter a valid email address.');
        exit;
    }

    if ($password !== $confirm_password) {
        showAlert('error', 'Password Mismatch', 'Passwords do not match.');
        exit;
    }

    // Strong password enforcement
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        showAlert('error', 'Weak Password', 'Password must be at least 8 characters, include 1 uppercase letter, 1 number, and 1 special character.');
        exit;
    }

    // Step 6: Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Step 7: Insert user into DB
    $stmt = $conn->prepare("INSERT INTO users (username, occupation, address, email, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        showAlert('error', 'Database Error', addslashes($conn->error));
        exit;
    }

    $stmt->bind_param("sssss", $username, $occupation, $address, $email, $hashed_password);

    if ($stmt->execute()) {
        // ✅ Original Success SweetAlert
        echo "
<!DOCTYPE html>
<html>
<head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Signup Successful!',
            text: 'You can now log in.',
            confirmButtonText: 'Go to Login'
        }).then(() => {
            window.location.href = 'login.html';
        });
    </script>
</body>
</html>";
    } else {
        // ✅ Original Error SweetAlert with duplicate email handling
        $errorMsg = ($conn->errno === 1062) ? "Email already registered." : addslashes($stmt->error);
        echo "
<!DOCTYPE html>
<html>
<head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Signup Failed',
            text: '$errorMsg',
            confirmButtonText: 'Back'
        }).then(() => {
            window.history.back();
        });
    </script>
</body>
</html>";
    }

    $stmt->close();
}

$conn->close();

// ✅ Reusable SweetAlert helper
function showAlert($icon, $title, $text) {
    echo "
<!DOCTYPE html>
<html>
<head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            confirmButtonText: 'Back'
        }).then(() => {
            window.history.back();
        });
    </script>
</body>
</html>";
}
?>
