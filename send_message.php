<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit('User not logged in');
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = $_POST['message'];

// Insert the message into the database
$query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iis', $sender_id, $receiver_id, $message);
$stmt->execute();

$stmt->close();
$conn->close();
?>
