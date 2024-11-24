<?php
session_start();

// Include the database connection file
include_once("../dbconnection/connect.php"); // Adjust path as needed
$con = connection(); // Establish the database connection

$student = null; // Initialize the $student variable

if (isset($_GET['ID'])) {
    $accountID = $_GET['ID'];

    $sql = "SELECT * FROM students WHERE accountID = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc(); // Fetch the student data

        if (!$student) {
            echo "No student found with the provided account ID.";
        }
    } else {
        echo "Failed to prepare the SQL statement.";
    }
} else {
    echo "No account ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School Fees Management System</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="buttons-container">
    <a href="javascript:history.back()" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
        Return
    </a>
</div>

<div class="invoice-container" id="invoice">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img
                                src="school-logo.png"
                                style="width: 100%; max-width: 100px"
                            />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            ISSUED TO:<br />
                            Name: <strong> <?php echo isset($student['fname']) ? htmlspecialchars($student['fname']) : 'N/A'; ?> <?php echo isset($student['lname']) ? htmlspecialchars($student['lname']) : 'N/A'; ?></strong><br />
                            EMAIL: <strong> <?php echo isset($student['email']) ? htmlspecialchars($student['email']) : 'N/A'; ?></strong>
                        </td>
                        <td>
                            DATE: <strong> <?php echo isset($student['date']) ? htmlspecialchars($student['date']) : 'N/A'; ?></strong> <br />
                            ID NUMBER: <strong> <?php echo isset($student['studentID']) ? htmlspecialchars($student['studentID']) : 'N/A'; ?></strong><br />
                            Grade Level: <strong> <?php echo isset($student['grade']) ? htmlspecialchars($student['grade']) : 'N/A'; ?></strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="heading">
            <td>Description</td>
            <td>Remarks</td>
        </tr>

        <tr class="item last">
            <td>Remaining Balance:</td>
            <td><strong> <?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['remainingbalance']) : 'N/A'; ?></strong></td>
        </tr> 
        
        <tr class="item">
            <td>Paid Amount</td>
            <td><strong> <?php echo isset($student['paymentAmount']) ? htmlspecialchars($student['paymentAmount']) : 'N/A'; ?></strong></td>
        </tr>
        
        <tr class="total">
            <td></td>
            <td>Total Paid: Php <strong> <?php echo isset($student['paymentAmount']) ? htmlspecialchars($student['paymentAmount']) : 'N/A'; ?></strong></td>
        </tr>
    </table>
</div>

<script src="html2canvas.js"></script>
<script>
    // Function to save the invoice as an image
    document.getElementById('save').addEventListener('click', function() {
        html2canvas(document.getElementById('invoice')).then(function(canvas) {
            // Create a link element
            const link = document.createElement('a');
            // Set the link's download attribute
            link.download = 'invoice.png';
            // Convert the canvas to a data URL
            link.href = canvas.toDataURL();
            // Programmatically click the link to trigger the download
            link.click();
        });
    });

    // Function to print the invoice
    document.getElementById('print').addEventListener('click', function() {
        window.print();
    });
</script>
</body>
</html>
