<?php
session_start();
include_once("dbconnection/connect.php");

// Establish the database connection
$con = connection();

// Check if POST data is received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $accountID = intval($_POST['accountID']);
    $firstQuarter = htmlspecialchars(trim($_POST['firstQuarter']));
    $secondQuarter = htmlspecialchars(trim($_POST['secondQuarter']));
    $thirdQuarter = htmlspecialchars(trim($_POST['thirdQuarter']));
    $fourthQuarter = htmlspecialchars(trim($_POST['fourthQuarter']));

    // Prepare the SQL query to update quarter statuses
    $sql_update_quarters = "UPDATE students SET 
        firstQuarter = ?, 
        secondQuarter = ?, 
        thirdQuarter = ?, 
        fourthQuarter = ? 
        WHERE accountID = ?";
    $stmt = $con->prepare($sql_update_quarters);
    $stmt->bind_param("ssssi", $firstQuarter, $secondQuarter, $thirdQuarter, $fourthQuarter, $accountID);

    if ($stmt->execute()) {
        // Redirect back to the form page with a success message
        header("Location: clearance.php?ID=$accountID&success=1");
    } else {
        // Redirect back to the form page with an error message
        header("Location: clearance.php?ID=$accountID&error=1");
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}



$con->close();
?>



