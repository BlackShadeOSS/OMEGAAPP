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

// Check if the user is a CN admin
$is_cn_admin = false;
$query = "SELECT is_cn_admin FROM user_account WHERE user_ac_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $is_cn_admin = $user['is_cn_admin'];
}
$stmt->close();

// If not a CN admin, redirect to an error page or the main page
if (!$is_cn_admin) {
    header("location: main-page.php");
    exit;
}

// Fetch the post ID from the URL
$post_id = isset($_GET['post_id']) ? $_GET['post_id'] : null;

// Fetch the post details
$query = "SELECT content FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
} else {
    echo "Post not found.";
    exit;
}
$stmt->close();

// Check if form is submitted
if (isset($_POST['add_community_note'])) {
    // Sanitize inputs to prevent XSS
    $note_content = htmlspecialchars($_POST['note_content'], ENT_QUOTES, 'UTF-8');

    // Prepare an insert statement to prevent SQL Injection
    $query = "INSERT INTO community_note (cn_post_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $post_id, $note_content);

    if ($stmt->execute()) {
        // Redirect to the post site with the new post ID
        header("location: post.php?id=" . $post_id);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Community Note</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="add-community-note-container">
        <h2>Add Community Note</h2>
        <p><strong>Post Content:</strong> <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></p>
        <form action="add_community_note.php?post_id=<?php echo $post_id; ?>" method="post">
            <textarea name="note_content" placeholder="Your community note content here" required></textarea>
            <button type="submit" name="add_community_note">Add Community Note</button>
        </form>
    </div>
</body>

</html>