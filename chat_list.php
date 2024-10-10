<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch users for chat with unread message count
$query = "SELECT u.id, u.name, u.profile_image, 
                 COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 END) AS unread_count
          FROM users u
          LEFT JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
          WHERE u.id != ?
          GROUP BY u.id";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-list {
            height: 400px;
            overflow-y: scroll;
            padding: 15px;
            background-color: #f7f7f7;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .user {
            display: flex;
            align-items: center;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
        }
        .user:hover {
            background-color: #e9ecef;
        }
        .user img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Chat List</h2>
        <div class="user-list" id="user-list">
            <?php foreach ($users as $user): ?>
                <div class="user" data-user-id="<?php echo $user['id']; ?>">
                    <img src="<?php echo $user['profile_image']; ?>" alt="<?php echo $user['name']; ?>">
                    <div>
                        <strong><?php echo $user['name']; ?></strong>
                    </div>
                    <?php if ($user['unread_count'] > 0): ?>
                        <div class="notification-badge"><?php echo $user['unread_count']; ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Open chat window on user click
            $('.user').click(function() {
                const userId = $(this).data('user-id');
                
                // Mark messages as read when chat is opened
                $.post('mark_as_read.php', { user_id: userId });

                // Redirect to chat
                window.location.href = 'chat.php?user_id=' + userId;
            });
        });
    </script>
</body>
</html>
