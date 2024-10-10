<?php
// index.php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];  // Assuming the user is logged in and has a session

// Fetch all users except the current logged-in user
$user_query = "SELECT * FROM users WHERE id != $user_id";
$users = $conn->query($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .chat-box { height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
        .message { margin-bottom: 10px; }
        .message.sent { text-align: right; color: blue; }
        .message.received { text-align: left; color: green; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Private Chat</h2>

    <div class="row">
        <div class="col-md-4">
            <h3>Select a User to Chat With:</h3>
            <ul class="list-group">
                <?php while ($user = $users->fetch_assoc()) { ?>
                    <li class="list-group-item">
                        <a href="?chat_with=<?= $user['id']; ?>"><?= $user['name']; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="col-md-8">
            <?php if (isset($_GET['chat_with'])): ?>
                <?php $chat_with = $_GET['chat_with']; ?>
                <h4>Chat with <?= $conn->query("SELECT name FROM users WHERE id = $chat_with")->fetch_assoc()['name']; ?></h4>

                <div class="chat-box" id="chat-box">
                    <!-- Chat messages will be loaded here via AJAX -->
                </div>

                <form id="chat-form">
                    <div class="input-group mb-3">
                        <input type="text" id="message" name="message" class="form-control" placeholder="Type a message" required>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    function loadMessages() {
        const chat_with = "<?= isset($_GET['chat_with']) ? $_GET['chat_with'] : ''; ?>";
        $.ajax({
            url: 'fetch_private_messages.php',
            method: 'GET',
            data: { chat_with: chat_with },
            success: function(data) {
                $('#chat-box').html(data);
                $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);  // Auto-scroll to the bottom
            }
        });
    }

    $('#chat-form').submit(function(e) {
        e.preventDefault();

        const message = $('#message').val();
        const receiver_id = "<?= isset($_GET['chat_with']) ? $_GET['chat_with'] : ''; ?>";

        if (message.trim() === '') {
            alert('Message cannot be empty.');
            return;
        }

        $.ajax({
            url: 'send_message.php',
            method: 'POST',
            data: { message: message, receiver_id: receiver_id },
            success: function() {
                $('#message').val('');  // Clear input
                loadMessages();  // Reload messages
            }
        });
    });

    setInterval(loadMessages, 1000);  // Refresh messages every second
</script>
</body>
</html>
