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

$query = "SELECT is_cn_admin FROM user_account WHERE user_ac_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect to the main page if the user is not a CN admin
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        // Redirect to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Fallback: Redirect to a default page if HTTP_REFERER is not available
        header("location: main_page.php");
        exit;
    }
}


// Check if form is submitted
if (isset($_POST['add_community_note'])) {
    // Sanitize inputs to prevent XSS
    $note_content = htmlspecialchars($_POST['note_content'], ENT_QUOTES, 'UTF-8');
    $post_id = $_POST['post_id']; // Get the post ID from the form

    // Prepare an insert statement to prevent SQL Injection
    $query = "INSERT INTO community_note (cn_post_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $post_id, $note_content);

    if ($stmt->execute()) {
        // Redirect to the post site with the new post ID
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
    }

    $stmt->close();
}
