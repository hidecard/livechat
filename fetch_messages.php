<?php
session_start();
include 'config.php';

// Fetch chat messages
$query = "SELECT * FROM messages ORDER BY created_at ASC";
$messages = $conn->query($query);

while ($msg = $messages->fetch_assoc()) {
    $user_id_msg = $msg['user_id'];
    $user_query = "SELECT name FROM users WHERE id = $user_id_msg";
    $user_result = $conn->query($user_query);
    $user_data = $user_result->fetch_assoc();
    $message_class = ($_SESSION['user_id'] == $user_id_msg) ? 'message sent' : 'message received';
    echo '<div class="' . $message_class . '">';
    echo '<strong>' . $user_data['name'] . ':</strong> ' . $msg['message'];
    echo '</div>';
}
?>
