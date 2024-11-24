<?php
// Password you want to hash
$password = "admin"; // the password you used

// Generate the hashed password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Output the hashed password
echo "Hashed Password: " . $hashedPassword;
?>
