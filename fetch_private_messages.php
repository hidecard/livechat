<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'];

// Fetch chat messages between the two users
$query = "SELECT * FROM messages 
          WHERE (sender_id = ? AND receiver_id = ?) 
          OR (sender_id = ? AND receiver_id = ?) 
          ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Generate message HTML
$messageHtml = '';
while ($msg = $messages->fetch_assoc()) {
    $messageHtml .= '<div class="message-wrapper ' . ($msg['sender_id'] == $user_id ? 'sent' : 'received') . '">';
    $messageHtml .= '<img src="' . ($msg['sender_id'] == $user_id ? $_SESSION['profile_image'] : $chat_user_id) . '" class="profile-image" alt="' . ($msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($chat_user_id)) . '">';
    $messageHtml .= '<div class="message">' . htmlspecialchars($msg['message']) . '</div>';
    $messageHtml .= '</div>';
}

// Return the generated HTML
echo $messageHtml;
?>
