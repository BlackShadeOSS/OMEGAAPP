<?php
// unfollow.php

// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the follow target ID is set
if (!isset($_POST['target_id'])) {
    echo "Error: Target ID not provided.";
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "Error: User not logged in.";
    exit;
}

// Get the user ID from the session (this is the follower)
$follower_id = $_SESSION["id"];

// The ID of the target being unfollowed
$target_id = $_POST['target_id'];

// Determine the type of the target (user or firm)
$target_type = isset($_POST['target_type']) ? $_POST['target_type'] : 'user'; // Default to 'user'

// Prepare the SQL query for unfollowing
if ($target_type === 'user') {
    // User to user unfollow
    $query = "DELETE FROM follow WHERE user_follower_id = ? AND user_ac_id = ?";
} elseif ($target_type === 'firm') {
    // User to firm unfollow
    $query = "DELETE FROM follow WHERE user_follower_id = ? AND firm_ac_id = ?";
} else {
    echo "Error: Invalid target type.";
    exit;
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $follower_id, $target_id);

if ($stmt->execute()) {
    echo "Unfollowed successfully.";
    // header("location: profile.php?id=" . $target_id); // Uncomment and adjust as needed
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
