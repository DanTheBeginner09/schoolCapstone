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
                // Calculate the new balance
                $newBalance = $student['remainingbalance'] - $paymentAmount;

                // Ensure the balance doesn't go below 0
                if ($newBalance < 0) {
                    $newBalance = 0;
                }

                // Update the balance and payment amount in the database
                // Only update if the payment amount is valid and does not exceed the remaining balance
                $sql_update = "UPDATE students SET remainingbalance = ?, paymentAmount = ? WHERE accountID = ?";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bind_param("dii", $newBalance, $paymentAmount, $accountID);

                if ($stmt_update->execute()) {
                    $successMessage = "Payment successfully processed. New balance is $" . number_format($newBalance, 2);
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            margin: 20px auto;
        }
        .box{
            width: 80%;
            margin: 15px auto;
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
        <h2 class="text-center"> <br>" <?php echo htmlspecialchars($student['fname'] . " " . $student['lname']); ?> " </h2>

        <?php if ($successMessage): ?>
            <div class="alert alert-success mt-3"><?php echo $successMessage; ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form method="POST" action="receipt.php?ID=<?php echo htmlspecialchars($accountID); ?>">
       
        
            <div class="form-group mt-3">
                <label for="studentID">Student ID</label>
                <input type="text" id="studentID" class="form-control" value="<?php echo htmlspecialchars($student['studentID']); ?>" disabled>
            </div>
            <div class="form-group mt-3">
                <label for="currentBalance">Current Balance</label>
                <input type="text" id="currentBalance" class="form-control" value="Php <?php echo number_format($student['remainingbalance'], 2); ?>" disabled>
            </div>

            <div class="form-group mt-3">
    <label for="paymentAmount">Amount Paid</label>
    <input type="number" id="paymentAmount" name="paymentAmount" class="form-control" min="1" step="0.01" placeholder="Enter amount" required>
</div>


            <div class="text-center">
            <button type="submit" class="btn btn-primary mt-4" <?php echo ($errorMessage ? 'disabled' : ''); ?>>SUBMIT</button>
                <br>
                <a href="accounting.php" class="btn btn-secondary mt-3">Back to Search</a>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2024 Carlgeline Gabila & Jessabel Canaway - Capstone Project. All rights reserved.</p>
</footer>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
