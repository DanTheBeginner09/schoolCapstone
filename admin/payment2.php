<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Ensure accountID is set in the URL
if (!isset($_GET['ID'])) {
    echo "No account ID provided.";
    exit;
}

$accountID = $_GET['ID'];

// Fetch student details based on accountID
$sql = "SELECT * FROM students WHERE accountID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $accountID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "No student found with the provided account ID.";
    exit;
}

// Initialize messages
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paymentAmount = $_POST['paymentAmount'];

    // Validate the payment amount
    if (!is_numeric($paymentAmount) || $paymentAmount <= 0) {
        $errorMessage = "Please enter a valid payment amount.";
    } elseif ($paymentAmount > $student['remainingbalance']) {
        $errorMessage = "Payment amount cannot exceed the current balance of Php " . number_format($student['remainingbalance'], 2);
    } else {
        // Calculate the new balance
        $newBalance = max(0, $student['remainingbalance'] - $paymentAmount);

        // Determine status based on payment amount
        $status = ($paymentAmount == 3000) ? "Paid" : "Partial";

        // Update the database with the new payment amount and status
        // Start transaction to ensure consistency
        $con->begin_transaction();

        try {
            // SQL query to update the balance and payment status (no quarter logic)
            $sql_update = "UPDATE students 
                           SET remainingbalance = ?, paymentAmount = ?, status = ? 
                           WHERE accountID = ?";
            $stmt_update = $con->prepare($sql_update);
            $stmt_update->bind_param("dssi", $newBalance, $paymentAmount, $status, $accountID);
            $stmt_update->execute();

            // Check if the update was successful
            if ($stmt_update->affected_rows > 0) {
                $successMessage = "Payment successfully processed. New balance is Php " . number_format($newBalance, 2);
            } else {
                $errorMessage = "No changes were made to the database.";
            }

            // Commit the transaction
            $con->commit();
        } catch (Exception $e) {
            // Rollback in case of error
            $con->rollback();
            $errorMessage = "Error processing payment: " . $e->getMessage();
        }

        $stmt_update->close();
    }
}

$stmt->close();
$con->close();
?>

<!-- The rest of the HTML content remains unchanged -->



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

        select {
            width: 200px;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
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
                <a href="javascript:history.back()" class="btn btn-secondary mt-3">Return</a>
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
