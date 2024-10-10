<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
}
?>
