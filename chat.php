<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user_id'] ?? null;

// Fetch chat user's information
$query = "SELECT name, profile_image FROM users WHERE id = ?";
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
            background-color: #f7f7f7; /* Light background for the whole page */
        }
        .chat-box {
            height: 400px;
            overflow-y: scroll;
            padding: 15px;
            background-color: #fff; /* White background for chat box */
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .message-wrapper {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .message {
            padding: 10px;
            border-radius: 10px;
            max-width: 60%;
            word-wrap: break-word; /* Ensure messages wrap properly */
        }
        .received .message {
            background-color: #e2e3e5; /* Light grey for received messages */
            margin-right: 10px; /* Space between image and message */
        }
        .sent .message {
            background-color: #d1e7dd; /* Light green for sent messages */
            margin-left: 10px; /* Space between image and message */
        }
        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover; /* Maintain aspect ratio */
        }
        .input-group {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Chat with <?php echo htmlspecialchars($chat_user['name']); ?></h2>
        <div class="chat-box" id="chat-box">
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="message-wrapper <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                    <img src="<?php echo $msg['sender_id'] == $user_id ? $_SESSION['profile_image'] : $chat_user['profile_image']; ?>" class="profile-image" alt="<?php echo $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($chat_user['name']); ?>">
                    <div class="message">
                        <?php echo htmlspecialchars($msg['message']); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <form id="chat-form" method="POST" action="send_message.php">
            <input type="hidden" name="receiver_id" value="<?php echo $chat_user_id; ?>">
            <div class="input-group mb-3">
                <input type="text" name="message" id="message" class="form-control" placeholder="Type a message" required>
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto scroll to bottom
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);

            // Handle sending a message via AJAX
            $('#chat-form').submit(function(e) {
                e.preventDefault();
                const message = $('#message').val();

                if (message.trim() !== '') {
                    $.post('send_message.php', {
                        message: message,
                        receiver_id: '<?php echo $chat_user_id; ?>'
                    }, function(data) {
                        $('#message').val(''); // Clear input
                        $('#chat-box').append('<div class="message-wrapper sent"><img src="<?php echo $_SESSION['profile_image']; ?>" class="profile-image" alt="You"><div class="message">' + message + '</div></div>'); // Append message with image
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight); // Scroll down
                    });
                }
            });
        });
    </script>
</body>
</html>
