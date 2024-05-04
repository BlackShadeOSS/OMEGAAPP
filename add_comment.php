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

// Check if form is submitted
if (isset($_POST['add_comment'])) {
    // Sanitize inputs to prevent XSS
    $comment_content = htmlspecialchars($_POST['comment_content'], ENT_QUOTES, 'UTF-8');
    $parent_post_id = $_POST['parent_post_id']; // Get the parent post ID
    $file_id = null; // Initialize file_id as null

    // Handle file upload for comments
    if (isset($_FILES['comment_image'])) {
        $file_name = $_FILES['comment_image']['name'];
        $file_size = $_FILES['comment_image']['size'];
        $file_tmp = $_FILES['comment_image']['tmp_name'];
        $file_type = $_FILES['comment_image']['type'];
        $file_ext = strtolower(end(explode('.', $_FILES['comment_image']['name'])));

        // Define allowed file types and maximum file size
        $extensions = array("jpeg", "jpg", "png", "gif");
        $max_file_size = 16 * 1024 * 1024; // 16MB

        // Check file size and type
        if (in_array($file_ext, $extensions) === false) {
            echo "Error: File extension not allowed.";
            exit;
        }
        if ($file_size > $max_file_size) {
            echo "Error: File size exceeds the limit.";
            exit;
        }

        // Generate a randomized file name with more entropy
        $new_file_name = uniqid('', true) . '.webp';
        $file_id = $new_file_name; // Set file_id to the new file name

        // Convert the image to WebP format
        $image = null;
        switch ($file_ext) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file_tmp);
                break;
            case 'png':
                $image = imagecreatefrompng($file_tmp);
                break;
            case 'gif':
                $image = imagecreatefromgif($file_tmp);
                break;
        }

        if ($image !== null) {
            // Save the converted image
            $upload_dir = 'uploads/';
            $upload_path = $upload_dir . $new_file_name;
            imagewebp($image, $upload_path);
            imagedestroy($image);
        } else {
            echo "Error: Failed to convert image to WebP format.";
            exit;
        }
    }

    // Prepare an insert statement to prevent SQL Injection
    $query = "INSERT INTO posts (author, content, post_id_for_comment, file_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $_SESSION["id"], $comment_content, $parent_post_id, $file_id);

    if ($stmt->execute()) {
        // Redirect to the post site with the new comment ID
        header("location: post.php?id=" . $parent_post_id);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
