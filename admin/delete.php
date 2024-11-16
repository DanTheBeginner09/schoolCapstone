<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Check if `ID` parameter is set in the URL
if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Prepare and execute delete query
    $stmt = $con->prepare("DELETE FROM students WHERE accountID = ?");
    $stmt->bind_param("i", $accountID);

    if ($stmt->execute()) {
        // Set success message
        $_SESSION['message'] = "Record deleted successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        // Set error message
        $_SESSION['message'] = "Error deleting record.";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
} else {
    // Set error message if no valid ID provided
    $_SESSION['message'] = "Invalid Account ID.";
    $_SESSION['msg_type'] = "danger";
}

// Redirect back to dashboard page
header("Location: dashboard.php");
exit();
?>

