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

// check if post of the given id has parent post and get the parent post id
$query = "SELECT post_id_for_comment FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$parentid = $result->fetch_assoc()['post_id_for_comment'];

if ($parentid == null) {
    $is_parent = false;
} else {
    $is_parent = true;
}
function displayPostDetails($postId, $conn, $session_id, $is_parent)
{
    // Prepare and execute the SQL query to fetch the post details
    $query = "SELECT posts.content, posts.file_id,
              COALESCE(user_account.username, firm_account.firm_name) AS author_name,
              user_account.avatar_id,
              user_account.is_cn_admin,
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

    // Check if user is cn admin
    $query = "SELECT is_cn_admin FROM user_account WHERE user_ac_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_cn_admin = $result->fetch_assoc()['is_cn_admin'];
    $stmt->close();

    // Check if the post exists
    if ($post) {
        // Display the author details
        echo "<div class='post";
        if ($is_parent) {
            echo " parent-post";
        }
        echo "'>";
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

        if ($is_parent) {
            echo "<a href='post.php?id=" . $postId . "'>";
        }

        // Display the post content
        echo "<p>" . htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') . "</p>";

        // Display the file if file_id is not null
        if ($post['file_id'] !== null) {
            echo "<div class='image-container'>";
            echo "<img src='uploads/" . htmlspecialchars($post['file_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Post File'>";
            echo "</div>";
        }

        if ($is_parent) {
            echo "</a>";
        }

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

        // Display cn form if not parent post
        if (!$is_parent && $is_cn_admin) {
            echo '<div class="cn-form">';
            echo '<form action="add_community_note.php" method="post">';
            echo '<textarea name="note_content" placeholder="Add a community note"></textarea>';
            echo '<input type="hidden" name="post_id" value="' . $postId . '">';
            echo '<button type="submit" name="add_community_note">Add Community Note</button>';
            echo '</form>';
            echo '</div>';
        }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Details</title>
    <link rel="stylesheet" href="post.css">
    <link rel="stylesheet" href="navbar.css">
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="post-details-container">
        <h1><a href="./main-page.php">Ωmega App</a></h1>
        <?php
        if ($is_parent) {
            echo "<h2>Parent Post Details</h2>";
            displayPostDetails($parentid, $conn, $_SESSION['id'], true);
        }
        echo "<h2>Post Details</h2>";
        displayPostDetails($post_id, $conn, $_SESSION['id'], false);
        ?>
    </div>

    <!-- Comment form-->
    <div class="comment-form">
        <form action="add_comment.php" method="post" enctype="multipart/form-data">
            <textarea name="comment_content" placeholder="Your comment here" required></textarea>
            <label for="comment_image" class="file-upload-label">Upload Image</label>
            <input type="file" name="comment_image" id="comment_image" class="file-upload" accept="image/*" onchange="previewImage(event)">
            <input type="hidden" name="parent_post_id" value="<?php echo $post_id; ?>">
            <img id="imagePreview" src="#" alt="Picture Preview">
            <button type="submit" name="add_comment">Add Comment</button>
        </form>
    </div>


    <div class="comments-container">
        <h3>Comments:</h3>
        <?php
        // Adjust the SQL query to also fetch the author's information for comments
        $query = "SELECT p1.*, COALESCE(user_account.username, firm_account.firm_name) AS author_name, user_account.avatar_id, COUNT(follow.user_follower_id) AS follower_count
          FROM posts p1
          LEFT JOIN user_account ON p1.author = user_account.user_ac_id
          LEFT JOIN firm_account ON p1.author_firm = firm_account.firm_ac_id
          LEFT JOIN follow ON follow.user_ac_id = user_account.user_ac_id
          WHERE p1.post_id_for_comment = ?
          GROUP BY p1.post_id
          ORDER BY p1.post_id_for_comment ASC, p1.post_id ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);

        // Function to display comments, including nested comments
        function displayComments($comments, $parentId = null, $conn)
        {
            if (!empty($comments)) {

                $query = "SELECT p1.*, COALESCE(user_account.username, firm_account.firm_name) AS author_name, user_account.avatar_id, COUNT(follow.user_follower_id) AS follower_count
                 (SELECT COUNT(*) FROM likes WHERE post_id = p1.post_id) AS likes_count,
                 (SELECT COUNT(*) FROM likes WHERE post_id = p1.post_id AND user_id = ?) AS liked_by_user
                 FROM posts p1
                 LEFT JOIN user_account ON p1.author = user_account.user_ac_id
                 LEFT JOIN firm_account ON p1.author_firm = firm_account.firm_ac_id
                 LEFT JOIN follow ON (follow.user_ac_id = user_account.user_ac_id OR follow.firm_ac_id = firm_account.firm_ac_id) AND follow.user_follower_id = ?
                 WHERE p1.post_id_for_comment = ?
                 GROUP BY p1.post_id
                 ORDER BY p1.post_id_for_comment ASC, p1.post_id ASC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iii", $_SESSION["id"], $_SESSION["id"], $parentId);
                $stmt->execute();
                $result = $stmt->get_result();
                $comments = $result->fetch_all(MYSQLI_ASSOC);
                foreach ($comments as $comment) {
                    if ($comment['post_id_for_comment'] == $parentId) {
                        echo "<div class='comment'>";
                        echo "<div class='author-info'>";
                        echo "<img src='uploads/" . htmlspecialchars($comment['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Author Avatar'>";
                        echo "<p> " . htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8') . "</p>";
                        echo "<span class='follower-count'>(" . $comment['follower_count'] . " followers)</span>";

                        $query = "SELECT COUNT(*) as is_following FROM follow WHERE user_ac_id = ? AND user_follower_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("ii", $comment['author'], $_SESSION["id"]);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $is_following = $result->fetch_assoc();

                        if ($is_following['is_following']) {
                            echo "<form action='unfollow.php' method='post' class='follow-button'>";
                            echo "<input type='hidden' name='target_id' value='" . $comment['author'] . "'>";
                            echo "<input type='hidden' name='target_type' value='user'>"; // Adjust based on whether the author is a user or firm
                            echo "<button type='submit'>Unfollow</button>";
                        } else {
                            echo "<form action='follow.php' method='post' class='follow-button'>";
                            echo "<input type='hidden' name='target_id' value='" . $comment['author'] . "'>";
                            echo "<input type='hidden' name='target_type' value='user'>"; // Adjust based on whether the author is a user or firm
                            echo "<button type='submit'>Follow</button>";
                        }
                        echo "</form>";
                        echo "</div>";
                        echo "<a href='post.php?id=" . $comment['post_id'] . "'>"; // Adjust 'comment.php' and the query parameter as needed
                        echo "<p>" . htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') . "</p>";
                        if ($comment['file_id'] !== null) {
                            echo "<img src='uploads/" . htmlspecialchars($comment['file_id'] . ".webp", ENT_QUOTES, 'UTF-8') . "' alt='Comment Image'>";
                        }
                        echo "<a/>"; // Close the anchor tag

                        // Display like button and like count for comments
                        echo "<div class='like-container'>";
                        echo "<p><strong>Likes:</strong> " . $comment['likes_count'] . "</p>";
                        if ($comment['liked_by_user'] > 0) {
                            echo "<form action='unlike_post.php' method='post'>";
                            echo "<input type='hidden' name='post_id' value='" . $comment['post_id'] . "'>";
                            echo "<button type='submit' name='unlike' class='unlike'>Unlike</button>";
                        } else {
                            echo "<form action='like_post.php' method='post'>";
                            echo "<input type='hidden' name='post_id' value='" . $comment['post_id'] . "'>";
                            echo "<button type='submit' name='like' class='like'>Like</button>";
                        }
                        echo "</form>";
                        echo "</div>";

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
                            echo "<a href='post.php?id=" . $comment['post_id'] . "'" . "class='seeMore'>See More</a>";
                        }
                        echo "</div>";
                    }
                }
            } else {
                echo '<p style="text-align: center;">No comments found!</p>';
            }
        }

        // Display all comments for the post
        displayComments($comments, $post_id, $conn);
        ?>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block'; // Display the image preview
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>

</html>