<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Check if email already exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email already exists.";
    } else {
        // Insert new user
        $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        echo "User registered successfully!";
        header("Location: login.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body>
    <form action="register.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Sign Up</button>
    </form>
</body>
</html>
