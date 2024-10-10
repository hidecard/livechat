<?php
session_start();
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']); 

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO private_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}
?>
