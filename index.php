<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch chat messages (For initial loading)
$query = "SELECT * FROM messages ORDER BY created_at ASC";
$messages = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat System</title>
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
        <h2 class="text-center">Welcome, <?php echo $user_name; ?></h2>

        <div class="chat-box" id="chat-box">
            <!-- Chat messages will be loaded here using AJAX -->
            <?php while ($msg = $messages->fetch_assoc()) {
                $user_id_msg = $msg['user_id'];
                $user_query = "SELECT name FROM users WHERE id = $user_id_msg";
                $user_result = $conn->query($user_query);
                $user_data = $user_result->fetch_assoc();
                $message_class = ($user_id == $user_id_msg) ? 'message sent' : 'message received';
            ?>
                <div class="<?php echo $message_class; ?>">
                    <strong><?php echo $user_data['name']; ?>:</strong> <?php echo $msg['message']; ?>
                </div>
            <?php } ?>
        </div>

        <form id="chat-form">
            <div class="input-group mb-3">
                <input type="text" id="message" name="message" class="form-control" placeholder="Type a message" required>
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </form>

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to refresh the chat messages
            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php',
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
                    data: { message: message },
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
