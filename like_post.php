<?php
// like_post.php

// Include the database connection file
require_once 'db_connect.php';

// Check if the post ID is set
if (!isset($_POST['post_id'])) {
    echo "Error: Post ID not provided.";
    exit;
}

$post_id = $_POST['post_id'];

// Check if the user already liked the post
$query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Error: Post already liked by the user.";
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
}

$stmt->close();

$query = "POST INTO likes (post_id, user_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $post_id, $user_id);

if ($stmt->execute()) {
    echo "Post liked successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
