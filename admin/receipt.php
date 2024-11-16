<?php
// Start the session and include database connection
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Check if the accountID is provided in the URL
if (isset($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Retrieve student details based on accountID
    $sql = "SELECT * FROM students WHERE accountID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // Check if student data exists
    if (!$student) {
        echo "No student found with the provided account ID.";
        exit;
    }

    // Initialize success and error messages
    $successMessage = "";
    $errorMessage = "";

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $paymentAmount = $_POST['paymentAmount'];
        
        // Validate payment amount
        if (is_numeric($paymentAmount) && $paymentAmount > 0) {
            // Ensure payment does not exceed the current balance
            if ($paymentAmount > $student['remainingbalance']) {
                $errorMessage = "Payment amount cannot exceed the current balance of $" . number_format($student['remainingbalance'], 2);
            } else {
                // Add the payment to the existing paymentAmount
                $newPaymentAmount = $student['paymentAmount'] + $paymentAmount;

                // Calculate the new balance
                $newBalance = $student['remainingbalance'] - $paymentAmount;

                // Ensure the balance doesn't go below 0
                if ($newBalance < 0) {
                    $newBalance = 0;
                }

                // Update the balance and add the payment amount to the paymentAmount in the database
                $sql_update = "UPDATE students SET remainingbalance = ?, paymentAmount = ? WHERE accountID = ?";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bind_param("dii", $newBalance, $newPaymentAmount, $accountID);

                if ($stmt_update->execute()) {
                    $successMessage = "Payment successfully processed. New balance is Php    " . number_format($newBalance, 2);
                    $_SESSION['payment_success'] = true; 
                } else {
                    $errorMessage = "Error processing payment. Please try again.";
                }

                $stmt_update->close();
            }
        } else {
            $errorMessage = "Please enter a valid payment amount.";
        }
    }

    $stmt->close();
} else {
    echo "No account ID provided.";
    exit;
}

// Handle updating quarter statuses if necessary
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['firstQuarter'])) {
    $firstQuarter = $_POST['firstQuarter'];
    $secondQuarter = $_POST['secondQuarter'];
    $thirdQuarter = $_POST['thirdQuarter'];
    $fourthQuarter = $_POST['fourthQuarter'];

    // Prepare the SQL query to update quarter statuses
    $sql_update_quarters = "UPDATE students SET 
        firstQuarter = ?, 
        secondQuarter = ?, 
        thirdQuarter = ?, 
        fourthQuarter = ? 
        WHERE accountID = ?";

    $stmt_update_quarters = $con->prepare($sql_update_quarters);
    $stmt_update_quarters->bind_param("ssssi", $firstQuarter, $secondQuarter, $thirdQuarter, $fourthQuarter, $accountID);

    if ($stmt_update_quarters->execute()) {
        $successMessage = "Quarter statuses successfully updated.";
    } else {
        $errorMessage = "Error updating quarter statuses. Please try again.";
    }

    $stmt_update_quarters->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management - Payment</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .main-content {
            display: flex;
    justify-content: center;  /* Horizontally center content */
    align-items: center;      /* Vertically center content */
    height: 100vh;            /* Make the parent container take the full height of the viewport */
    padding: 20px;       
         
        }

        
        .box {
            margin-top: 100px;
            width: 80%;
            margin: 15px auto;
        }
        .receipt-box {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .receipt-box h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-box .row {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="mainheader">
        <p>Web-based school fees <br><span class="subheading">MANAGEMENT SYSTEM</span></p>
    </div>
</div>

<div class="main-content">
    <div class="box">
       
        <?php if ($successMessage): ?>
            <div class="alert alert-success mt-3"><?php echo $successMessage; ?></div>
            <!-- Receipt Format -->
            <div class="receipt-box">
                <h3>Payment Receipt</h3>
                <div class="row">
                  
                    <div class="col-md-6"><strong>Referencec no:</strong><br> <?php echo htmlspecialchars($student['accountID']); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Student Name:</strong> <br><?php echo htmlspecialchars($student['fname'] . " " . $student['lname']); ?></div>
                    <div class="col-md-6"><strong>Student ID:</strong><br> <?php echo htmlspecialchars($student['studentID']); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Previous Balance:</strong> <br>Php <?php echo number_format($student['remainingbalance'] + $paymentAmount, 2); ?></div>
                    <div class="col-md-6"><strong>Payment Amount:</strong> <br>Php <?php echo number_format($paymentAmount, 2); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>New Balance:</strong><br>Php <?php echo number_format($newBalance, 2); ?></div>
                    <div class="col-md-6"><strong>Date of Payment:</strong><br> <?php echo date('F j, Y'); ?></div>
                </div>
                <div class="text-center mt-3">
                    <a href="accounting.php" class="btn btn-secondary">Back to Search</a>
                </div>
            </div>
        <?php elseif ($errorMessage): ?>
            <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

     
    </div>
</div>

<footer class="footer">
    <p>&copy; 2024 Carlgeline Gabilla & Jessa Mae Canaway - Capstone Project. All rights reserved.</p>
</footer>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
