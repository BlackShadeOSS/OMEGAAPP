<?php
// Include the database connection file
require_once 'db_connect.php';

if (isset($_POST['register'])) {
    // Sanitize inputs to prevent XSS
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $profile_picture = null; // Initialize profile picture as null

    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        echo "All fields are required.";
    } elseif ($password != $confirm_password) {
        echo "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile picture upload
        if (isset($_FILES['profile_picture'])) {
            $file_name = $_FILES['profile_picture']['name'];
            $file_size = $_FILES['profile_picture']['size'];
            $file_tmp = $_FILES['profile_picture']['tmp_name'];
            $file_type = $_FILES['profile_picture']['type'];
            $file_ext = strtolower(end(explode('.', $_FILES['profile_picture']['name'])));

            // Define allowed file types and maximum file size
            $extensions = array("jpeg", "jpg", "png", "gif");
            $max_file_size = 16 * 1024 * 1024; // 16MB

            // Check file size and type
            if (in_array($file_ext, $extensions) === false) {
                echo "Error: File extension not allowed.";
                exit;
            }
            if ($file_size > $max_file_size) {
                echo "Error: File size exceeds the limit.";
                exit;
            }

            // Generate a randomized file name with more entropy
            $new_file_name = uniqid('', true) . '.webp';
            $avatar_id = $new_file_name; // Set avatar_id to the new file name

            // Convert the image to WebP format
            $image = null;
            switch ($file_ext) {
                case 'jpeg':
                case 'jpg':
                    $image = imagecreatefromjpeg($file_tmp);
                    break;
                case 'png':
                    $image = imagecreatefrompng($file_tmp);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($file_tmp);
                    break;
            }

            if ($image !== null) {
                // Save the converted image
                $upload_dir = 'uploads/';
                $upload_path = $upload_dir . $new_file_name;
                imagewebp($image, $upload_path);
                imagedestroy($image);
            } else {
                echo "Error: Failed to convert image to WebP format.";
                exit;
            }
        }

        // Prepare an insert statement to prevent SQL Injection
        $query = "INSERT INTO user_account (username, password, email, avatar_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $avatar_id);

        if ($stmt->execute()) {
            echo "Registration successful.";

            // Start a new session and set session variables
            session_start();
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["id"] = $conn->insert_id;

            // Redirect to main-page.php or any other page you prefer
            header("Location: main-page.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="register-container">
        <h2>Register</h2>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit" name="register">Register</button>
        </form>
    </div>
</body>

</html>