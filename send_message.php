<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_POST['message']) || !isset($_POST['receiver_id'])) {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $_POST['message'];
$receiver_id = $_POST['receiver_id'];

// Insert the message into the database
$query = "INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $user_id, $receiver_id, $message);
$stmt->execute();

// Return the message text as response
echo htmlspecialchars($message);
?>
