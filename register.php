<?php
session_start();
include 'config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if the image file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image = $_FILES['image'];

        // Handle image upload
        if ($image['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $file_name = time() . '_' . basename($image['name']);
            $target_file = $upload_dir . $file_name;

            // Check if the uploads folder exists, if not, create it
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
            }

            // Move the uploaded file to the target directory
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                // File uploaded successfully
            } else {
                echo "Failed to upload image.";
                exit;
            }
        } else {
            // Handle specific upload errors
            switch ($image['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "The uploaded file is too large.";
                    exit;
                case UPLOAD_ERR_PARTIAL:
                    echo "The file was only partially uploaded.";
                    exit;
                case UPLOAD_ERR_NO_FILE:
                    echo "No file was uploaded.";
                    exit;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Missing a temporary folder.";
                    exit;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Failed to write file to disk.";
                    exit;
                default:
                    echo "An unknown error occurred.";
                    exit;
            }
        }
    } else {
        echo "No file uploaded or an error occurred.";
        exit;
    }

    // Insert user data into the database
    $query = "INSERT INTO users (name, email, password, profile_image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $password, $target_file);

    if ($stmt->execute()) {
        echo "Registration successful!";
        // Optionally, redirect to login page or login directly
        // header("Location: login.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
