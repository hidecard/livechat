<?php
// fetch_private_messages.php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];
$chat_with = $_GET['chat_with'];

$query = "SELECT * FROM messages 
          WHERE (sender_id = $user_id AND receiver_id = $chat_with) 
             OR (sender_id = $chat_with AND receiver_id = $user_id)
          ORDER BY created_at ASC";
$messages = $conn->query($query);

if ($messages->num_rows > 0) {
    while ($msg = $messages->fetch_assoc()) {
        $user_id_msg = $msg['sender_id'];
        $user_query = "SELECT name FROM users WHERE id = $user_id_msg";
        $user_result = $conn->query($user_query);
        $user_data = $user_result->fetch_assoc();

        $message_class = ($user_id == $user_id_msg) ? 'message sent' : 'message received';
        echo '<div class="' . $message_class . '">';
        echo '<strong>' . $user_data['name'] . ':</strong> ' . htmlspecialchars($msg['message']);
        echo '</div>';
    }
} else {
    echo '<p>No messages yet.</p>';
}
?>
