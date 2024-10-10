<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Chat</title>
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
            display: flex;
            align-items: center;
        }
        .message img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
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
        <h2 class="text-center">Private Chat with [Other User]</h2>

        <div class="chat-box" id="chat-box">
            <!-- Private messages will be loaded here using AJAX -->
        </div>

        <form id="chat-form">
            <input type="hidden" id="receiver_id" name="receiver_id" value="[Receiver User ID]">
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
            function loadPrivateMessages() {
                const receiverId = $('#receiver_id').val();

                $.ajax({
                    url: 'fetch_private_messages.php',
                    method: 'GET',
                    data: { receiver_id: receiverId },
                    success: function(data) {
                        $('#chat-box').html(data);
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight); // Auto-scroll to the bottom
                    }
                });
            }

            setInterval(loadPrivateMessages, 3000); // Reload messages every 3 seconds

            $('#chat-form').submit(function(e) {
                e.preventDefault();
                const message = $('#message').val();
                const receiverId = $('#receiver_id').val();

                $.ajax({
                    url: 'private_send_message.php',
                    method: 'POST',
                    data: { message: message, receiver_id: receiverId },
                    success: function() {
                        $('#message').val(''); // Clear input
                        loadPrivateMessages();  // Reload messages
                    }
                });
            });

            // Load messages on page load
            loadPrivateMessages();
        });
    </script>
</body>
</html>
