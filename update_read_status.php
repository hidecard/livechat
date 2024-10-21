<?php
session_start();
include 'config.php';

if (isset($_POST['receiver_id'])) {
    $user_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];

    // Update all unread messages from the receiver as read
    $query = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $receiver_id, $user_id);
    $stmt->execute();
}
?>
