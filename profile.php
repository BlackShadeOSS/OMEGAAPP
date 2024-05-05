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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="profile-container">
        <h2>Profile</h2>
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Followers:</strong> <?php echo $followers['follower_count']; ?></p>
            <?php if ($user['avatar_id'] !== null) : ?>
                <p><strong>Avatar:</strong> <img src="uploads/<?php echo htmlspecialchars($user['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="User Avatar"></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>