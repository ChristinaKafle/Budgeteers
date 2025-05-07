<?php
session_start();

// Connect to existing DB
$conn = new mysqli("localhost", "root", "", "Walletok");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));  // Clean and lower case
    $password = trim($_POST['password']);  // Clean the password

    // Debug: Show the submitted username
    echo "Submitted username: " . htmlspecialchars($username) . "<br>";

    if (empty($username) || empty($password)) {
        showAlert('error', 'Empty Fields', 'Please fill in all fields.');
        exit;
    }

    // Prepare and execute the query with case-insensitive username comparison
    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE LOWER(username) = LOWER(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Debug: Show the number of rows found
    echo "Rows found: " . $stmt->num_rows . "<br>";

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;

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
                    title: 'Login Successful!',
                    confirmButtonText: 'Continue'
                }).then(() => {
                    window.location.href = 'Home.php';
                });
              </script>
            </body>
            </html>";
        } else {
            showAlert('error', 'Incorrect Password', 'Please try again.');
        }
    } else {
        showAlert('error', 'User Not Found', 'Username does not exist.');
    }

    $stmt->close();
}

$conn->close();

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
