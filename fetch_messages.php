<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'] ?? null;

// Validate chat user ID
if ($chat_user_id) {
    $query = "SELECT * FROM messages 
              WHERE (sender_id = ? AND receiver_id = ?) 
              OR (sender_id = ? AND receiver_id = ?) 
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result();

    while ($msg = $messages->fetch_assoc()) {
        echo "<div class='message-wrapper " . ($msg['sender_id'] == $user_id ? 'sent' : 'received') . "'>
                    <img src='" . ($msg['sender_id'] == $user_id ? htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') : 'chat_user_image') . "' class='profile-image' alt='" . ($msg['sender_id'] == $user_id ? 'You' : 'chat_user_name') . "'>
                <div class='message'>" . htmlspecialchars($msg['message']) . "
                    <div class='timestamp'>" . (new DateTime($msg['created_at']))->format('Y-m-d H:i:s') . "</div>
                </div>
              </div>";
    }
}
