<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connect.php';

// Check if the firm ID is set in the URL
if (!isset($_GET['id'])) {
    // Redirect to an error page or the main page if the firm ID is not provided
    header("location: main-page.php");
    exit;
}

$firm_id = $_GET['id']; // Get the firm ID from the URL

// Fetch the firm's profile information
$query = "SELECT firm_name, followers, avatar_id FROM firm_account WHERE firm_ac_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $firm_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the firm's profile data
    $firm = $result->fetch_assoc();
} else {
    // Display an error message if the firm does not exist
    echo "Firm not found.";
    exit;
}

// Fetch the number of followers the firm has
$query = "SELECT COUNT(*) as follower_count FROM follow WHERE firm_ac_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $firm_id);
$stmt->execute();
$result = $stmt->get_result();
$followers = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Firm Profile</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your own CSS for styling -->
</head>

<body>
    <div class="profile-container">
        <h2>Firm Profile</h2>
        <div class="profile-info">
            <p><strong>Firm Name:</strong> <?php echo htmlspecialchars($firm['firm_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Followers:</strong> <?php echo $followers['follower_count']; ?></p>
            <?php if ($firm['avatar_id'] !== null) : ?>
                <p><strong>Avatar:</strong> <img src="uploads/<?php echo htmlspecialchars($firm['avatar_id'] . ".webp", ENT_QUOTES, 'UTF-8'); ?>" alt="Firm Avatar"></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>