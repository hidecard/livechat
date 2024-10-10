<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch users for chat
$query = "SELECT id, name, profile_image FROM users WHERE id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
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
                window.location.href = 'chat.php?user_id=' + userId;
            });
        });
    </script>
</body>
</html>
