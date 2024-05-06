<?php
// follow.php

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

// The ID of the target being followed
$target_id = $_POST['target_id'];

// Determine the type of the target (user or firm)
$target_type = isset($_POST['target_type']) ? $_POST['target_type'] : 'user'; // Default to 'user'

// Prepare the SQL query for following
if ($target_type === 'user') {
    // User to user follow
    $query = "INSERT INTO follow (user_follower_id, user_ac_id) VALUES (?, ?)";
} elseif ($target_type === 'firm') {
    // User to firm follow
    $query = "INSERT INTO follow (user_follower_id, firm_ac_id) VALUES (?, ?)";
} else {
    echo "Error: Invalid target type.";
    exit;
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $follower_id, $target_id);

if ($stmt->execute()) {
    echo "Followed successfully.";
    // header("location: profile.php?id=" . $target_id); // Uncomment and adjust as needed
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
