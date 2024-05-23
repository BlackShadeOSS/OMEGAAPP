<?php
// Include the database connection file
require_once 'db_connect.php';

if (isset($_POST['login'])) {
    // Sanitize inputs to prevent XSS
    $username = strip_tags(htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8'));
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
    } else {
        // Prepare a select statement to prevent SQL Injection
        $query = "SELECT * FROM user_account WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch user data
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $user["user_ac_id"];
                $_SESSION["username"] = $username;

                // Redirect user to main-page.php
                header("location: main-page.php");
                exit; // Ensure no further code is executed
            } else {
                // Display an error message if password is not valid
                echo "The password you entered was not valid.";
            }
        } else {
            // Display an error message if username doesn't exist
            echo "No account found with that username.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <div class="login-container">
        <h1>Ωmega App</h1>
        <h2>Login</h2>
        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>

</html>