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

    <!-- Show number of likes and button to like that will be pink if you already liked the post -->
    <?php
    // Fetch the number of likes for the post form the posts table
    $query = "SELECT likes FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $likes = $result->fetch_assoc();
    $stmt->close();

    $query = "SELECT COUNT(*) as liked FROM likes WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $post_id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $liked = $result->fetch_assoc();
    $stmt->close();
    ?>
    <div class="like-container">
        <p><strong>Likes:</strong> <?php echo $likes['likes']; ?></p>
        <form id="likeForm" action="<?php echo $liked['liked'] ? 'unlike_post.php' : 'like_post.php'; ?>" method="post">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <button type="submit" id="likeButton" name="like" style="background-color: <?php echo $liked['liked'] ? 'pink' : 'white'; ?>">
                <?php echo $liked['liked'] ? 'Unlike' : 'Like'; ?>
            </button>
        </form>
    </div>


    <!-- Add a comment form at the bottom -->
    <form action="add_comment.php" method="post" enctype="multipart/form-data">
        <textarea name="comment_content" placeholder="Your comment here" required></textarea>
        <input type="file" name="comment_image" accept="image/*">
        <input type="hidden" name="parent_post_id" value="<?php echo $post_id; ?>">
        <button type="submit" name="add_comment">Add Comment</button>
    </form>

    <div class="comments-container">
        <h3>Comments:</h3>
        <?php
        // Fetch comments for the post, excluding the post itself
        $query = "SELECT p1.* FROM posts p1
          LEFT JOIN posts p2 ON p1.post_id_for_comment = p2.post_id
          WHERE p2.post_id = ? AND p1.post_id != ?
          ORDER BY p1.post_id_for_comment ASC, p1.post_id ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $post_id, $post_id); // Bind the post ID twice
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);

        // Function to display comments, including nested comments
        function displayComments($comments, $parentId = null, $conn)
        {
            foreach ($comments as $comment) {
                if ($comment['post_id_for_comment'] == $parentId) {
                    echo "<div class='comment'>";
                    // Wrap the comment content and image in an anchor tag
                    echo "<a href='post.php?id=" . $comment['post_id'] . "'>"; // Adjust 'comment.php' and the query parameter as needed
                    echo "<p>" . htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') . "</p>";
                    if ($comment['file_id'] !== null) {
                        echo "<img src='uploads/" . htmlspecialchars($comment['file_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Comment Image'>";
                    }
                    echo "</a>"; // Close the anchor tag
                    // Check if the comment has replies and display them

                    $query = "SELECT p1.* FROM posts p1
                    LEFT JOIN posts p2 ON p1.post_id_for_comment = p2.post_id
                    WHERE p2.post_id = ? AND p1.post_id != ?
                    ORDER BY p1.post_id_for_comment ASC, p1.post_id ASC";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $comment['post_id'], $comment['post_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $replies = $result->fetch_all(MYSQLI_ASSOC);
                    if (!empty($replies)) {
                        echo "<a href='post.php?id=" . $comment['post_id'] . "'>See More</a>";
                    }
                    echo "</div>";
                }
            }
        }

        // Display all comments for the post
        displayComments($comments, $post_id, $conn);
        ?>
    </div>
</body>

</html>