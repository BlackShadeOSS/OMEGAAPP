<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Redirect to login page if not logged in
    header("location: login.php");
    exit;
}

// Include the database connection file
require_once 'db_connect.php';

// Check if form is submitted
if (isset($_POST['add_post'])) {
    // Sanitize inputs to prevent XSS
    $post_content = htmlspecialchars($_POST['post_content'], ENT_QUOTES, 'UTF-8');

    // Prepare an insert statement to prevent SQL Injection
    $query = "INSERT INTO posts (author, content) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $_SESSION["id"], $post_content);

    if ($stmt->execute()) {
        echo "Post added successfully.";
        // Optionally, redirect to a page showing the post or a list of posts
        // header("location: posts_list.php");
        // exit;
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
    <title>Add Post</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="add-post-container">
        <h2>Add Post</h2>
        <form action="add_post.php" method="post">
            <textarea name="post_content" placeholder="Your post content here" required></textarea>
            <button type="submit" name="add_post">Add Post</button>
        </form>
    </div>
</body>

</html>