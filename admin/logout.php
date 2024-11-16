<?php
// Start the session to access session variables
session_start();

// Destroy the session, effectively logging the user out
session_destroy();

// Redirect to the login page (or homepage)
header("Location: login.php"); // Adjust this to your login page's URL
exit();
?>
