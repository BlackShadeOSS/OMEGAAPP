<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';
function displayPostDetails($postId, $conn, $session_id)
{
    // Prepare and execute the SQL query to fetch the post details
    $query = "SELECT posts.content, posts.file_id,
              COALESCE(user_account.username, firm_account.firm_name) AS author_name,
              user_account.avatar_id,
              user_account.user_ac_id AS author_id,
              'user' AS author_type,
              COUNT(follow.user_follower_id) AS follower_count
              FROM posts 
              LEFT JOIN user_account ON posts.author = user_account.user_ac_id 
              LEFT JOIN firm_account ON posts.author_firm = firm_account.firm_ac_id 
              LEFT JOIN follow ON follow.user_ac_id = user_account.user_ac_id
              WHERE posts.post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    // Fetch community notes for the post
    $query = "SELECT content FROM community_note WHERE cn_post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $community_notes = $result->fetch_all(MYSQLI_ASSOC);


    // Check if the post exists
    if ($post) {
        // Display the author details
        echo "<div class='post'>";
        echo "<div class='author-details'>";
        echo "<img class='author-avatar' src='uploads/" . htmlspecialchars($post['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Author Avatar'>";
        echo "<span class='author-name'>" . htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8') . "</span>";
        echo "<span class='follower-count'> (" . $post['follower_count'] . " followers)</span>";

        $query = "SELECT COUNT(*) as is_following FROM follow WHERE user_ac_id = ? AND user_follower_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $post['author_id'], $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $is_following = $result->fetch_assoc();


        if ($is_following['is_following']) {
            echo '<form action="unfollow.php" method="post" class="follow-button">';
            echo    '<input type="hidden" name="target_id" value="' . $post['author_id'] . '">';
            echo    '<input type="hidden" name="target_type" value="' . $post['author_type'] . '">';
            echo    '<button type="submit">Unfollow</button>';
            echo '</form>';
        } else {
            echo '<form action="follow.php" method="post" class="follow-button">';
            echo '<input type="hidden" name="target_id" value="' . $post['author_id'] . '">';
            echo '<input type="hidden" name="target_type" value="' . $post['author_type'] . '">';
            echo '<button type="submit">Follow</button>';
            echo '</form>';
        }
        echo "</div>";
        echo "<a href='post.php?id=" . $postId . "'>";

        // Display the post content
        echo "<p>" . htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') . "</p>";

        // Display the file if file_id is not null
        if ($post['file_id'] !== null) {
            echo "<div class='image-container'>";
            echo "<img src='uploads/" . htmlspecialchars($post['file_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Post File'>";
            echo "</div>";
        }
        echo "</a>";

        // Display community notes
        if (!empty($community_notes)) {
            echo '<div class="community-notes-container">';
            echo '<h3>Community Notes:</h3>';
            foreach ($community_notes as $note) {
                echo '<p>' . htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            echo '</div>';
        }

        $query = "SELECT likes FROM posts WHERE post_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $likes = $result->fetch_assoc();

        $query = "SELECT COUNT(*) as liked FROM likes WHERE post_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $postId, $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $liked = $result->fetch_assoc();

        echo '<div class="like-container">';
        echo '<p><strong>Likes: </strong>' . $likes['likes'] . '</p>';
        echo '<form id="likeForm" action="';
        echo $liked['liked'] ? 'unlike_post.php' : 'like_post.php';
        echo '" method="post">';
        echo '<input type="hidden" name="post_id" value="' . $postId . '">';
        echo '<button type="submit" class="';
        echo $liked['liked'] ? 'unlike' : 'like';
        echo '" name="like">';
        echo $liked['liked'] ? 'Unlike' : 'Like';
        echo '</button>';
        echo '</form>';
        echo '</div>';

        echo "</div>";
    } else {
        echo "<p>Post not found.</p>";
    }

    $stmt->close();
}
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
            // Prepare and execute the SQL query to fetch the recent posts
            $query = "SELECT post_id FROM posts ORDER BY post_id DESC";
            $result = $conn->query($query);
            // Check if the query returned any posts
            if ($result->num_rows > 0) {
                // Fetch and display the post details
                while ($row = $result->fetch_assoc()) {
                    displayPostDetails($row['post_id'], $conn, $_SESSION['id']);
                }
            } else {
                echo "<p>No posts found.</p>";
            }
            ?>
        </div>

    </div>
</body>

</html>