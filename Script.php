<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dyffatopup";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    
    // Check if email already exists
    $checkSql = "SELECT * FROM users WHERE email = '$email'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_order') {
    $game = $conn->real_escape_string($_POST['game']);
    $package = $conn->real_escape_string($_POST['package']);
    $userId = isset($_POST['userId']) ? $conn->real_escape_string($_POST['userId']) : '';
    $server = isset($_POST['server']) ? $conn->real_escape_string($_POST['server']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $method = isset($_POST['method']) ? $conn->real_escape_string($_POST['method']) : '';
    $payment = $conn->real_escape_string($_POST['payment']);
    $total = $conn->real_escape_string($_POST['total']);
    
    $orderNumber = 'ORD-' . mt_rand(100000, 999999);
    
    $sql = "INSERT INTO orders (order_number, game, package, user_id, server, email, name, method, payment, total, status) 
            VALUES ('$orderNumber', '$game', '$package', '$userId', '$server', '$email', '$name', '$method', '$payment', '$total', 'pending')";
    
    if ($conn->query($sql) {
        echo json_encode(['success' => true, 'order_number' => $orderNumber]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting order']);
    }
    exit;
}

$conn->close();
?>
