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

// Fetch the post ID from the URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
} else {
    // Redirect to an error page or the main page if the post ID is not provided
    header("location: main-page.php");
    exit;
}

// Prepare a select statement to prevent SQL Injection
$query = "SELECT posts.content, posts.file_id,
          COALESCE(user_account.username, firm_account.firm_name) AS author_name,
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
    <?php if ($post['is_cn_admin']) : ?>
        <div class="add-community-note-container">
            <a href="add_community_note.php?post_id=<?php echo $post_id; ?>">Add Community Note</a>
        </div>
    <?php endif; ?>
</body>

</html>