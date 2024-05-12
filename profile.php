<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the user ID is set in the URL
if (!isset($_GET['id'])) {
    // Redirect to an error page or the main page if the user ID is not provided
    header("location: main-page.php");
    exit;
}

$user_id = $_GET['id']; // Get the user ID from the URL

if (isset($_GET['show'])) {
    switch ($_GET['show']) {
        case 'posts':
            $show = 'posts';
            break;
        case 'postscom':
            $show = 'postscom';
            break;
        default:
            $show = 'posts';
    }
} else {
    $show = 'posts';
}

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'newest':
            $sort = 'newest';
            break;
        case 'oldest':
            $sort = 'oldest';
            break;
        case 'most-liked':
            $sort = 'most-liked';
            break;
        case 'least-liked':
            $sort = 'least-liked';
            break;
        default:
            $sort = 'newest';
    }
} else {
    $sort = 'newest';
}

function displayPostDetails($postId, $conn)
{
    // Prepare and execute the SQL query to fetch the post details
    $query = "SELECT posts.content, posts.file_id,
              COALESCE(user_account.username, firm_account.firm_name) AS author_name,
              user_account.avatar_id,
              user_account.user_ac_id AS author_id,
              'user' AS author_type,
              COUNT(follow.user_follower_id) AS follower_count,
              CASE WHEN follow.user_follower_id IS NOT NULL THEN true ELSE false END AS is_following
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
        // if ($post['is_following']) {
        //     echo '<form action="unfollow.php" method="post" class="follow-button">';
        //     echo    '<input type="hidden" name="target_id" value="' . $post['author_id'] . '">';
        //     echo    '<input type="hidden" name="target_type" value="' . $post['author_type'] . '">';
        //     echo    '<button type="submit">Unfollow</button>';
        //     echo '</form>';
        // } else {
        //     echo '<form action="follow.php" method="post" class="follow-button">';
        //     echo '<input type="hidden" name="target_id" value="' . $post['author_id'] . '">';
        //     echo '<input type="hidden" name="target_type" value="' . $post['author_type'] . '">';
        //     echo '<button type="submit">Follow</button>';
        //     echo '</form>';
        // }
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
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="navbar.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="profile-container">
        <h2>Profile</h2>
        <div class="profile-info">
            <?php
            // Fetch the user's profile information
            $query = "SELECT username, avatar_id FROM user_account WHERE user_ac_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Fetch the user's profile data
                $user = $result->fetch_assoc();
            } else {
                // Display an error message if the user does not exist
                echo "User not found.";
                exit;
            }

            // Fetch the number of followers the user has
            $query = "SELECT COUNT(*) as follower_count FROM follow WHERE user_ac_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $followers = $result->fetch_assoc();

            $stmt->close();
            ?>
            <div class="profile-header">
                <?php if ($user['avatar_id'] !== null) : ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="User Avatar" class="profile-pic">
                <?php endif; ?>
                <div>
                    <h1><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p><strong>Followers:</strong> <?php echo $followers['follower_count']; ?></p>
                </div>
                <?php
                $isFollowing = false;
                $query = "SELECT * FROM follow WHERE user_ac_id =? AND user_follower_id =?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $user_id, $_SESSION['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $isFollowing = true;
                }
                $stmt->close();

                // Display the follow/unfollow button based on the $isFollowing variable
                if ($isFollowing) {
                    echo '<form action="unfollow.php" method="post" class="follow-button">';
                    echo '<input type="hidden" name="target_id" value="' . $user_id . '">';
                    echo '<input type="hidden" name="target_type" value="user">';
                    echo '<button type="submit">Unfollow</button>';
                    echo '</form>';
                } else {
                    echo '<form action="follow.php" method="post" class="follow-button">';
                    echo '<input type="hidden" name="target_id" value="' . $user_id . '">';
                    echo '<input type="hidden" name="target_type" value="user">';
                    echo '<button type="submit">Follow</button>';
                    echo '</form>';
                }
                ?>
            </div>
        </div>
        <div class="profile-post-selector">
            <a href="./profile.php?id=<?php echo $user_id; ?>&show=posts" <?php if ($show == 'posts') echo 'class="active"' ?>>Posts</a>
            <a href="./profile.php?id=<?php echo $user_id; ?>&show=postscom" <?php if ($show == 'postscom') echo 'class="active"' ?>>Posts & Comments</a>
        </div>
        <div class="profile-post-sorter">
            <form action="profile.php?id=<?php echo $user_id; ?>" method="get">
                <label for="sort">Sort by:</label>
                <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="show" value="<?php echo $show; ?>">
                <select name="sort" id="sort">
                    <option value="newest" <?php if ($sort == 'newest') echo "selected" ?>>Newest</option>
                    <option value="oldest" <?php if ($sort == 'oldest') echo "selected" ?>>Oldest</option>
                    <option value="most-liked" <?php if ($sort == 'most-liked') echo "selected" ?>>Most Liked</option>
                    <option value="least-liked" <?php if ($sort == 'least-liked') echo "selected" ?>>Least Liked</option>
                </select>
                <button type="submit">Sort</button>
            </form>
        </div>
        <div class="profile-posts">
            <?php
            // Fetch the posts based on the selected option
            $query = "SELECT post_id FROM posts WHERE author = ?";

            if ($show == 'posts') {
                $query .= " AND post_id_for_comment  IS NULL";
            }

            // Sort the posts based on the selected option
            switch ($sort) {
                case 'newest':
                    $query .= " ORDER BY create_datetime DESC";
                    break;
                case 'oldest':
                    $query .= " ORDER BY create_datetime ASC";
                    break;
                case 'most-liked':
                    $query .= " ORDER BY likes DESC";
                    break;
                case 'least-liked':
                    $query .= " ORDER BY likes ASC";
                    break;
            }

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Fetch and display the posts
                while ($row = $result->fetch_assoc()) {
                    displayPostDetails($row['post_id'], $conn);
                }
            } else {
                // Display a message if the user has no posts
                echo "<p>No posts found.</p>";
            }

            ?>
        </div>
    </div>
</body>

</html>