<?php
// like_post.php

// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the post ID is set
if (!isset($_POST['post_id'])) {
    echo "Error: Post ID not provided.";
    // Redirect back to the post page after 5 seconds
    echo "<script>setTimeout(function(){ window.location.href = 'main-page.php'; }, 5000);</script>";
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "Error: User not logged in.";
    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 5000);</script>";
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

$post_id = $_POST['post_id'];

// Check if the user already liked the post
$query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Error: Post already liked by the user.";
    echo "<script>setTimeout(function(){ window.location.href = 'post.php?id=" . $post_id . "'; }, 5000);</script>";
    exit;
}

$stmt->close();

// Prepare the SQL query to increment the likes count for the post
$query = "UPDATE posts SET likes = likes + 1 WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    echo "Post liked successfully.";
} else {
    echo "Error: " . $stmt->error;
    echo "<script>setTimeout(function(){ window.location.href = 'post.php?id=" . $post_id . "'; }, 5000);</script>";
    exit;
}

$stmt->close();

$query = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $post_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();
    echo "Post liked successfully.";
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        // Redirect to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Fallback: Redirect to a default page if HTTP_REFERER is not available
        header("location: post.php?id=" . $post_id);
        exit;
    }
} else {
    echo "Error: " . $stmt->error;
    echo "<script>setTimeout(function(){ window.location.href = 'post.php?id=" . $post_id . "'; }, 5000);</script>";
    exit;
}
