<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_POST['user_id'];

// Update messages to mark them as read
$query = "UPDATE messages SET is_read = 1 
          WHERE (sender_id = ? AND receiver_id = ?) 
          OR (sender_id = ? AND receiver_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
$stmt->execute();

echo "Messages marked as read.";
?>
