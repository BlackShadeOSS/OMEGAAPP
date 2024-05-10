<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <link rel="stylesheet" href="main-page.css">
    <link rel="stylesheet" href="navbar.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="main-page-container">
        <h2>Recent Posts</h2>
        <div class="posts-container">
            <?php
            // Prepare a select statement to prevent SQL Injection
            $query = "SELECT posts.post_id, posts.author, posts.content, posts.file_id FROM posts WHERE posts.post_id_for_comment IS NULL ORDER BY posts.post_id DESC LIMIT 25";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($post_id, $post_author, $post_content, $file_name);

            while ($stmt->fetch()) {
                echo "<div class='post'>";
                echo "<h3>Post by User ID: $post_author</h3>";
                echo "<p>$post_content</p>";
                echo "<img src='uploads/$file_name.webp' alt='Image'>";
                echo "<a href='post.php?id=$post_id'>View Post</a>";
                echo "</div>";
            }

            $stmt->close();
            ?>
        </div>

    </div>
</body>

</html>