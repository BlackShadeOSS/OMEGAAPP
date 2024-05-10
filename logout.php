<?php
// Start the session
session_start();

// Destroy the session
session_destroy();

// Redirect to the main page
header("Location: main-page.php");
exit;
