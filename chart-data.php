<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'Walletok';

$conn = new mysqli($host, $user, $pass, $db);

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];

$allCategories = ['shopping', 'bills', 'food', 'investment', 'transportation', 'others'];

function getPieData($conn, $username, $interval) {
    $sql = "SELECT type, SUM(amount) as total 
            FROM transactions 
            WHERE username = ? AND date >= DATE_SUB(CURDATE(), INTERVAL $interval)
            GROUP BY type";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['type']] = $row['total'];
    }
    return $data;
}

function getBarData($conn, $username, $interval, $categories) {
    $sql = "SELECT category, SUM(amount) as total 
            FROM transactions 
            WHERE username = ? AND type = 'expense' AND date >= DATE_SUB(CURDATE(), INTERVAL $interval)
            GROUP BY category";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $assoc = array_fill_keys($categories, 0);
    while ($row = $result->fetch_assoc()) {
        $assoc[$row['category']] = $row['total'];
    }

    $data = [];
    foreach ($assoc as $cat => $val) {
        $data[] = ['category' => $cat, 'total' => $val];
    }
    return $data;
}

function getHistogramData($conn, $username, $interval) {
    // For daily, return only today's data
    if ($interval === '1 DAY') {
        $sql = "SELECT DATE(date) as day, SUM(amount) as total 
                FROM transactions 
                WHERE username = ? AND type = 'expense' AND DATE(date) = CURDATE()
                GROUP BY DATE(date)";
    } else {
        $sql = "SELECT DATE(date) as day, SUM(amount) as total 
                FROM transactions 
                WHERE username = ? AND type = 'expense' AND date >= DATE_SUB(CURDATE(), INTERVAL $interval)
                GROUP BY DATE(date)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}


echo json_encode([
    'daily' => [
        'pie' => getPieData($conn, $username, '1 DAY'),
        'bar' => getBarData($conn, $username, '1 DAY', $allCategories),
        'histogram' => getHistogramData($conn, $username, '1 DAY')
    ],
    'monthly' => [
        'pie' => getPieData($conn, $username, '1 MONTH'),
        'bar' => getBarData($conn, $username, '1 MONTH', $allCategories),
        'histogram' => getHistogramData($conn, $username, '1 MONTH')
    ],
    'yearly' => [
        'pie' => getPieData($conn, $username, '1 YEAR'),
        'bar' => getBarData($conn, $username, '1 YEAR', $allCategories),
        'histogram' => getHistogramData($conn, $username, '1 YEAR')
    ]
]);
?>

