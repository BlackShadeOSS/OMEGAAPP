<?php
// Include the database connection file
require_once 'db_connect.php';

if (isset($_POST['register'])) {
    // Sanitize inputs to prevent XSS
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

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

        // Prepare a select statement to prevent SQL Injection
        $query = "SELECT * FROM user_account WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Username already exists.";
        } else {
            // Insert new user
            $query = "INSERT INTO user_account (username, password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $username, $hashed_password, $email);

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
        <form action="register.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" name="register">Register</button>
        </form>
    </div>
</body>

</html>