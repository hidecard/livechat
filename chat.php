<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$chat_user_id = $_GET['user_id'] ?? null;
$user_id = $_SESSION['user_id'];

// Check if the chat user is provided
if ($chat_user_id) {
    // Fetch the chat user's information
    $query = "SELECT name, profile_image, last_activity FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $chat_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chat_user = $result->fetch_assoc();

    // Ensure the user exists
    if (!$chat_user) {
        echo "User not found!";
        exit();
    }

    // Fetch the last activity of the chat user and calculate the active status
    $current_time = new DateTime();
    
    // Handle null `last_activity`
    $last_activity = isset($chat_user['last_activity']) ? new DateTime($chat_user['last_activity']) : null;
    $interval = $last_activity ? $current_time->getTimestamp() - $last_activity->getTimestamp() : null;
    $is_active = ($interval !== null && $interval < 300); // 300 seconds = 5 minutes

    // Update current user's activity
    $query = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
} else {
    echo "No user selected!";
    exit();
}

// Fetch chat messages between the two users
$query = "SELECT * FROM messages 
          WHERE (sender_id = ? AND receiver_id = ?) 
          OR (sender_id = ? AND receiver_id = ?) 
          ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($chat_user['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #e5ddd5;
    }
    .chat-container {
        position: relative;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .top-bar {
        background-color: #0088cc;
        color: #fff;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .top-bar img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }
    .top-bar .username {
        font-size: 18px;
        font-weight: bold;
    }
    .chat-box {
        height: 500px;
        overflow-y: scroll;
        padding: 15px;
        background-color: #fff;
        margin-top: 10px;
    }
    .message-wrapper {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .message-wrapper.sent {
        flex-direction: row-reverse;
    }
    .message {
        padding: 10px 15px;
        border-radius: 15px;
        max-width: 70%;
        word-wrap: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .received .message {
        background-color: #ffffff;
        margin-right: 10px;
    }
    .sent .message {
        background-color: #dcf8c6;
        margin-left: 10px;
    }
    .profile-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .sent .profile-image {
        margin-left: 10px;
        margin-right: 0;
    }
    .timestamp {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    .status {
        font-size: 12px;
        color: #0088cc;
        margin-left: 5px;
    }
    .input-group {
        position: relative;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        border-radius: 30px;
        padding: 5px;
    }
    .input-group .form-control {
        border: none;
        box-shadow: none;
        outline: none;
    }
    .input-group .btn {
        background-color: #0088cc;
        color: #fff;
        border: none;
        border-radius: 50%;
        padding: 10px 15px;
    }
</style>
</head>
<body>
    <div class="container mt-5">
        <div class="chat-container">
            <div class="top-bar">
                <img src="<?php echo htmlspecialchars($chat_user['profile_image']); ?>" alt="Profile Image">
                <div class="username">
                    <?php echo htmlspecialchars($chat_user['name']); ?>
                    <br>
                    <small style="color: green;">
                        <?php if ($is_active): ?>
                            Active Now
                        <?php elseif ($last_activity): ?>
                            Last seen: <?php echo $last_activity->format('Y-m-d H:i:s'); ?>
                        <?php else: ?>
                            Last activity unavailable
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <div class="chat-box" id="chat-box">
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="message-wrapper <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <img src="<?php echo $msg['sender_id'] == $user_id ? htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') : htmlspecialchars($chat_user['profile_image']); ?>" class="profile-image" alt="<?php echo $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($chat_user['name']); ?>">
                        <div class="message">
                            <?php echo htmlspecialchars($msg['message']); ?>
                            <div class="timestamp">
                                <?php echo (new DateTime($msg['created_at']))->format('Y-m-d H:i:s'); ?>
                                <?php if ($msg['sender_id'] == $user_id): ?>
                                    <span class="status">
                                        <?php if ($msg['is_read']): ?>
                                            ✓✓ Read
                                        <?php else: ?>
                                            ✓ Delivered
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <form id="chat-form" method="POST" action="send_message.php">
            <input type="hidden" name="receiver_id" value="<?php echo $chat_user_id; ?>">
            <div class="input-group mb-3">
                <input type="text" name="message" id="message" class="form-control" placeholder="Type a message" required>
                <button class="btn" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                        <path d="M15.964.686a.75.75 0 0 0-.85-.088L.681 7.151a.75.75 0 0 0-.012 1.348l4.802 2.268 2.268 4.802a.75.75 0 0 0 1.348-.012l6.554-14.433a.75.75 0 0 0-.087-.85z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);

            // AJAX form submission
            $('#chat-form').submit(function(e) {
                e.preventDefault();
                const message = $('#message').val();
                const receiver_id = $('input[name="receiver_id"]').val();

                $.ajax({
                    url: 'send_message.php',
                    type: 'POST',
                    data: {message: message, receiver_id: receiver_id},
                    success: function(response) {
                        // Clear the input field and reload chat
                        $('#message').val('');
                        $('#chat-box').append(response);
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                    },
                    error: function() {
                        alert('Error sending message');
                    }
                });
            });

            // Auto-refresh chat every 5 seconds
            setInterval(function() {
                $.ajax({
                    url: 'fetch_messages.php',
                    type: 'GET',
                    data: {user_id: '<?php echo $chat_user_id; ?>'},
                    success: function(response) {
                        $('#chat-box').html(response);
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>
