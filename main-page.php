<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Fetch the newest 25 posts that are not comments
$query = "SELECT post_id, content, file_id FROM posts WHERE post_id_for_comment IS NULL ORDER BY create_datetime DESC LIMIT 25";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="main-page-container">
        <h2>Newest 25 Posts</h2>
        <?php while ($post = $result->fetch_assoc()) : ?>
            <div class="post">
                <p><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php if ($post['file_id'] !== null) : ?>
                    <img src="uploads/<?php echo htmlspecialchars($post['file_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="Post Image">
                <?php endif; ?>
                <a href="post.php?id=<?php echo $post['post_id']; ?>">View Post</a>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>