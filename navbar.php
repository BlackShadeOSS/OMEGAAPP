<?php
$query = "SELECT * FROM user_account WHERE user_ac_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<nav class="navbar">
    <div class="navbar-brand">
        <a href="main-page.php">Ωmega App</a>
    </div>
    <div class="navbar-menu">
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) : ?>
            <div class="navbar-item">
                <a href="./profile.php?id=<?php echo $_SESSION["id"] ?>">
                    <img src="uploads/<?php echo $user["avatar_id"]; ?>.webp" alt="Avatar" class="avatar" width="50px" height="50px" />
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
            <div class="navbar-item">
                <a href="logout.php" class="logout"><button class="navbar-button">Logout</button></a>
            </div>
        <?php else : ?>
            <div class="navbar-item">
                <a href="login.php"><button class="navbar-button">Login</button></a>
            </div>
            <div class="navbar-item">
                <a href="register.php"><button class="navbar-button">Register</button></a>
            </div>
        <?php endif; ?>
    </div>
</nav>