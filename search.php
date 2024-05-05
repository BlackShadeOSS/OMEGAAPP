<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the search query is set
if (!isset($_GET['query'])) {
    // Redirect to an error page or the main page if the search query is not provided
    header("location: main-page.php");
    exit;
}

$search_query = $_GET['query']; // Get the search query from the URL

// Prepare the SQL query to search for posts and comments
$query = "SELECT posts.post_id, posts.content, posts.likes, posts.file_id, 'post' as type FROM posts WHERE content LIKE ?
          UNION ALL
          SELECT posts.post_id, posts.content, posts.likes, posts.file_id, 'comment' as type FROM posts WHERE post_id_for_comment IS NOT NULL AND content LIKE ?
          ORDER BY likes DESC LIMIT 25";
$stmt = $conn->prepare($query);
$search_query_like = '%' . $search_query . '%'; // Prepare the search query for LIKE clause
$stmt->bind_param("ss", $search_query_like, $search_query_like);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="search-results-container">
        <h2>Search Results for "<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>"</h2>
        <?php while ($item = $result->fetch_assoc()) : ?>
            <div class="search-result">
                <p><?php echo htmlspecialchars($item['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Likes:</strong> <?php echo $item['likes']; ?></p>
                <?php if ($item['file_id'] !== null) : ?>
                    <img src="uploads/<?php echo htmlspecialchars($item['file_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="Post/Comment Image">
                <?php endif; ?>
                <a href="post.php?id=<?php echo $item['post_id']; ?>">View Post/Comment</a>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>