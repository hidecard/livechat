<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? '';

// Validate inputs
if ($receiver_id && $message) {
    $query = "INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<div class='message-wrapper sent'>
                <img src='" . htmlspecialchars($_SESSION['profile_image'] ?? 'default.png') . "' class='profile-image' alt='You'>
                <div class='message'>" . htmlspecialchars($message) . "
                    <div class='timestamp'>" . (new DateTime())->format('Y-m-d H:i:s') . "
                        <span class='status'>✓ Delivered</span>
                    </div>
                </div>
              </div>";
    } else {
        echo "Error sending message";
    }
}
