<?php
include_once("dbconnection/connect.php");
$con = connection();

// Password to hash
$password = "admin"; // This is the password you want to set for your user

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update the user in the database
$id_number = "2024-2106"; // Change this to the appropriate user ID
$query = "UPDATE users SET password = ? WHERE id_number = ?";
$stmt = $con->prepare($query);

if ($stmt) {
    $stmt->bind_param("ss", $hashedPassword, $id_number);
    if ($stmt->execute()) {
        echo "Password updated successfully!";
    } else {
        echo "Error updating password: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Prepare failed: " . $con->error;
}

$con->close();
?>
