<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch chat user details
$query = "SELECT name, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $chat_user_id);
$stmt->execute();
$chat_user = $stmt->get_result()->fetch_assoc();

// Fetch messages between users
$query = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
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
    <title>Chat with <?php echo $chat_user['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: scroll;
            padding: 15px;
            background-color: #f7f7f7;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .sent {
            background-color: #d1e7dd;
            text-align: right;
        }
        .received {
            background-color: #f1f3f5;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Chat with <?php echo $chat_user['name']; ?></h2>
        <div class="chat-box" id="chat-box">
            <?php while ($msg = $messages->fetch_assoc()) {
                $message_class = ($msg['sender_id'] == $user_id) ? 'message sent' : 'message received';
            ?>
                <div class="<?php echo $message_class; ?>">
                    <strong><?php echo ($msg['sender_id'] == $user_id) ? 'You' : $chat_user['name']; ?>:</strong> <?php echo $msg['message']; ?>
                </div>
            <?php } ?>
        </div>

        <form id="chat-form">
            <div class="input-group mb-3">
                <input type="text" id="message" name="message" class="form-control" placeholder="Type a message" required>
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </form>

        <a href="chat_list.php" class="btn btn-secondary">Back to Chat List</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to refresh the chat messages
            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php?user_id=<?php echo $chat_user_id; ?>',
                    method: 'GET',
                    success: function(data) {
                        $('#chat-box').html(data);
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight); // Auto-scroll to the bottom
                    }
                });
            }

            // Load messages every 3 seconds
            setInterval(loadMessages, 3000);

            // Handle form submission using AJAX
            $('#chat-form').submit(function(e) {
                e.preventDefault();

                const message = $('#message').val();
                if (message.trim() === '') {
                    alert('Message cannot be empty.');
                    return;
                }

                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: { message: message, receiver_id: <?php echo $chat_user_id; ?> },
                    success: function(response) {
                        $('#message').val(''); // Clear the input after sending
                        loadMessages(); // Reload messages
                    }
                });
            });

            // Load messages on initial page load
            loadMessages();
        });
    </script>
</body>
</html>
