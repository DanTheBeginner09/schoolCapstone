<?php
// Start the session at the very beginning
session_start();

// Include the database connection file
include_once("dbconnection/connect.php");

// Establish database connection
$con = connection();

// Check if the user is logged in
if (!isset($_SESSION['id_number'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Check if the form is submitted and 'request_soa' button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_soa'])) {
    
    // Get the studentID from the POST data
    $studentID = $_POST['studentID'];

    // Check if the studentID is provided
    if (empty($studentID)) {
        $_SESSION['errorMessage'] = "Student ID is missing. Please try again.";
        header("Location: requestsoa.php");
        exit;
    }

    // Prepare the SQL query to update the 'student_request' field to 'yes'
    $query = "UPDATE students SET student_request = 'yes' WHERE studentID = ?";
    $stmt = $con->prepare($query);

    // Check if the query is prepared successfully
    if ($stmt === false) {
        $_SESSION['errorMessage'] = "Failed to prepare the request. Please try again.";
        header("Location: requestsoa.php");
        exit;
    }

    // Bind the studentID parameter
    $stmt->bind_param("s", $studentID);

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        // Success message
        $_SESSION['successMessage'] = "Request for Statement of Account sent successfully.";
    } else {
        // Error message if the query fails
        $_SESSION['errorMessage'] = "Failed to send the request. Please try again.";
    }

    // Close the statement after execution
    $stmt->close();
} else {
    // If the form is not submitted or 'request_soa' is not set
    $_SESSION['errorMessage'] = "Invalid request. Please try again.";
}

// Redirect back to the requestsoa.php page with feedback
header("Location: requestsoa.php"); 
exit;

// Close the database connection
$con->close();
?>
