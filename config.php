<?php
// Database connection setup
$host = 'localhost'; // Database host
$user = 'root'; // Database username
$pass = 'hidecard'; // Database password
$db_name = 'livechat'; // Database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
