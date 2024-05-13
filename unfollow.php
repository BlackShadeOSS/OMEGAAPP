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

// Check if user is following the target
$query = "SELECT * FROM follow WHERE user_follower_id = ? AND ";
if ($target_type === 'user') {
    // User to user unfollow
    $query .= "user_ac_id = ?";
} elseif ($target_type === 'firm') {
    // User to firm unfollow
    $query .= "firm_ac_id = ?";
} else {
    echo "Error: Invalid target type.";
    exit;
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $follower_id, $target_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Error: You are not following this target.";
    exit;
}

$stmt->close();


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

    $query = "SELECT * FROM follow WHERE";
    if ($target_type === 'user') {
        $query .= " user_ac_id = ?";
    } elseif ($target_type === 'firm') {
        $query .= " firm_ac_id = ?";
    } else {
        echo "Error: Invalid target type.";
        exit;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $follow_count = $result->num_rows;

    if ($target_type === 'user') {
        $query = "UPDATE user_account SET followers = ? WHERE user_ac_id = ?";
    } elseif ($target_type === 'firm') {
        $query = "UPDATE firm_account SET followers = ? WHERE firm_ac_id = ?";
    } else {
        echo "Error: Invalid target type.";
        exit;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $follow_count, $target_id);
    $stmt->execute();
    $stmt->close();

    // Check if the HTTP_REFERER is set and not empty
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        // Redirect to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Fallback: Redirect to a default page if HTTP_REFERER is not available
        header("Location: main_page.php");
        exit;
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
