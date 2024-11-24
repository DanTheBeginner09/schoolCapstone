<?php
include_once("dbconnection/connect.php");
$con = connection(); // Establish database connection

// Handle the SOA (Statement of Account) update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soa'], $_POST['accountID'])) {
    $accountID = $_POST['accountID']; // Get the Account ID
    $soa = $_POST['soa']; // Get the SOA value

    // Update the SOA value in the database for the corresponding accountID
    $sql = "UPDATE students SET soa = ? WHERE accountID = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        // Bind parameters: "si" means string and integer (for SOA and accountID)
        $stmt->bind_param("si", $soa, $accountID);
        
        if ($stmt->execute()) {
            // Redirect back to the SOA page with a success message
            header("Location: check_soa_status.php?ID=" . $accountID . "&status_updated=true");
            exit(); // Ensure no further code is executed after redirect
        } else {
            die("Error updating SOA: " . $stmt->error); // Show error message if execution fails
        }
    } else {
        die("Error preparing statement: " . $con->error); // Show error if SQL statement preparation fails
    }
}
?>
