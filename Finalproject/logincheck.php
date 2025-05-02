<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trading";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get username and password from POST request
$user = isset($_POST['username']) ? $_POST['username'] : '';
$pwd = isset($_POST['password']) ? $_POST['password'] : '';

// Prepare and execute SQL query
$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user, $pwd);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    echo "Login successful!";
} else {
    echo "Invalid username or password.";
}

// Close connection
$stmt->close();
$conn->close();
?>