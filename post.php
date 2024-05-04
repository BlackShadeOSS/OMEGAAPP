<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Redirect to login page if not logged in
    header("location: login.php");
    exit;
}

// Check if the post ID is set
if (!isset($_GET['id'])) {
    // Redirect to an error page or the main page if the post ID is not provided
    header("location: main-page.php");
    exit;
}

$post_id = $_GET['id'];

// Fetch the post details
$query = "SELECT posts.content, posts.file_id,
          COALESCE(user_account.username, firm_account.firm_name) AS author_name,
          user_account.avatar_id,
          user_account.is_cn_admin 
          FROM posts 
          LEFT JOIN user_account ON posts.author = user_account.user_ac_id 
          LEFT JOIN firm_account ON posts.author_firm = firm_account.firm_ac_id 
          WHERE posts.post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch post data
    $post = $result->fetch_assoc();
} else {
    // Display an error message if the post does not exist
    echo "Post not found.";
    exit;
}

// Fetch community notes for the post
$query = "SELECT content FROM community_note WHERE cn_post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$community_notes = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Post Details</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="post-details-container">
        <h2>Post Details</h2>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($post['avatar_id'] !== null) : ?>
            <p><strong>Author Avatar:</strong> <img src="uploads/<?php echo htmlspecialchars($post['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="Author Avatar"></p>
        <?php endif; ?>
        <p><strong>Content:</strong> <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></p>
        <!-- Display file if file_id is not null -->
        <?php if ($post['file_id'] !== null) : ?>
            <p><strong>File:</strong> <img src="uploads/<?php echo htmlspecialchars($post['file_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="Post File"></p>
        <?php endif; ?>
        <!-- Display community notes -->
        <div class="community-notes-container">
            <h3>Community Notes:</h3>
            <?php foreach ($community_notes as $note) : ?>
                <p><?php echo htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add a comment form at the bottom -->
    <form action="add_comment.php" method="post" enctype="multipart/form-data">
        <textarea name="comment_content" placeholder="Your comment here" required></textarea>
        <input type="file" name="comment_image" accept="image/*">
        <input type="hidden" name="parent_post_id" value="<?php echo $post_id; ?>">
        <button type="submit" name="add_comment">Add Comment</button>
    </form>


    <?php
    // Fetch comments for the post, including nested comments
    $query = "SELECT p1.* FROM posts p1
          LEFT JOIN posts p2 ON p1.post_id_for_comment = p2.post_id
          WHERE p2.post_id = ? OR p1.post_id = ?
          ORDER BY p1.post_id_for_comment ASC, p1.post_id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $post_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);

    // Function to display comments, including nested comments
    function displayComments($comments, $parentId = null)
    {
        foreach ($comments as $comment) {
            if ($comment['post_id_for_comment'] == $parentId) {
                echo "<div class='comment'>";
                echo "<p>" . htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') . "</p>";
                // Check if the comment has replies and display a "See more" link if it does
                $hasReplies = array_search($comment['post_id'], array_column($comments, 'post_id_for_comment')) !== false;
                if ($hasReplies) {
                    echo "<a href='post.php?id=" . $comment['post_id'] . "'>See more</a>";
                }
                echo "</div>";
                // Recursively display replies to this comment
                displayComments($comments, $comment['post_id']);
            }
        }
    }

    // Display all comments for the post
    displayComments($comments);
    ?>
</body>

</html>