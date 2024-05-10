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
        if ($post['is_following']) {
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
                    <img src="uploads/<?php echo htmlspecialchars($user['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="User Avatar">
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
            <form action="profile.php?id=<?php echo $user_id; ?>" method="post">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="most-liked">Most Liked</option>
                    <option value="least-liked">Least Liked</option>
                </select>
                <button type="submit">Sort</button>
            </form>
        </div>
        <div class="profile-posts">
            <?php
            // Fetch the user's posts based on the selected option
            if ($show == 'posts') {
                $query = "SELECT post_id, post_title, post_content, post_date, post_likes FROM posts WHERE user_ac_id = ?";
            } else {
                $query = "SELECT post_id, post_title, post_content, post_date, post_likes FROM posts WHERE user_ac_id = ? UNION SELECT post_id, post_title, post_content, post_date, post_likes FROM comment WHERE user_ac_id = ?";
            }

            if (isset($_POST['sort'])) {
                switch ($_POST['sort']) {
                    case 'newest':
                        $query .= " ORDER BY post_date DESC";
                        break;
                    case 'oldest':
                        $query .= " ORDER BY post_date ASC";
                        break;
                    case 'most-liked':
                        $query .= " ORDER BY post_likes DESC";
                        break;
                    case 'least-liked':
                        $query .= " ORDER BY post_likes ASC";
                        break;
                    default:
                        $query .= " ORDER BY post_date DESC";
                }
            } else {
                $query .= " ORDER BY post_date DESC";
            }

            $stmt = $conn->prepare($query);
            if ($show == 'posts') {
                $stmt->bind_param("i", $user_id);
            } else {
                $stmt->bind_param("ii", $user_id, $user_id);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="profile-post">';
                    echo '<h3>' . htmlspecialchars($row['post_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
                    echo '<p>' . htmlspecialchars($row['post_content'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p class="post-date">' . htmlspecialchars($row['post_date'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p class="post-likes">' . $row['post_likes'] . ' likes</p>';
                    echo '<a href="post.php?id=' . $row['post_id'] . '">View Post</a>';
                    echo '</div>';
                }
            } else {
                echo "No posts found.";
            }

            $stmt->close();
            ?>
        </div>
    </div>
</body>

</html>