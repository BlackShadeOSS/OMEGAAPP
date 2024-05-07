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
if (isset($_POST['add_post'])) {
    // Sanitize inputs to prevent XSS
    $post_content = htmlspecialchars($_POST['post_content'], ENT_QUOTES, 'UTF-8');
    $file_id = null; // Initialize file_id as null

    // Handle file upload
    if (isset($_FILES['post_image'])) {
        $file_name = $_FILES['post_image']['name'];
        $file_size = $_FILES['post_image']['size'];
        $file_tmp = $_FILES['post_image']['tmp_name'];
        $file_type = $_FILES['post_image']['type'];
        $file_ext = strtolower(end(explode('.', $_FILES['post_image']['name'])));

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
                // Handle GIFs with a palette by converting them to PNG first
                $image = imagecreatefromgif($file_tmp);
                if (imageistruecolor($image)) {
                    // If the GIF is not a palette image, proceed as usual
                    break;
                }
                // Convert the palette-based GIF to PNG
                $pngImage = imagecreatetruecolor(imagesx($image), imagesy($image));
                imagecopy($pngImage, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                $image = $pngImage;
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
    // Modify the query to include the file_id
    $query = "INSERT INTO posts (author, content, file_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $_SESSION["id"], $post_content, $file_id);

    if ($stmt->execute()) {
        // Get the ID of the newly inserted post
        $new_post_id = $conn->insert_id;
        // Redirect to the post site with the new post ID
        header("location: post.php?id=" . $new_post_id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>
    <link rel="stylesheet" href="add_post.css">
</head>

<body>
    <div class="add-post-container">
        <h1><a href="./main-page.php">Ωmega App</a></h1>
        <h2>Add Post</h2>
        <form action="add_post.php" method="post" enctype="multipart/form-data">
            <textarea name="post_content" placeholder="Your post content here" required></textarea>
            <input type="file" id="post_image" name="post_image" accept="image/*" onchange="previewImage(event)">
            <label for="post_image" class="custom-file-upload">Upload Image</label>
            <img id="imagePreview" src="#" alt="Image Preview" style="display:none; max-width: 100%; height: auto;">
            <button type="submit" name="add_post">Add Post</button>
        </form>
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