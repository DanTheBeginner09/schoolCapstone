<?php
session_start();
include_once("dbconnection/connect.php");

// Establish the database connection
$con = connection();

// Ensure accountID is set in the URL
if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) {
    echo "No valid account ID provided.";
    exit;
}

$accountID = intval($_GET['ID']); // Ensure accountID is an integer

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

$stmt->close();
$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management - Clearance Update</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .box {
            width: 100%;
            max-width: 600px;
            margin: 15px auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .clearance-details p {
            margin-bottom: 8px;
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
    <div class="box p-4 shadow-sm">
        <h3 class="text-center mb-4">CLEARANCE UPDATE <br>

        </h3>

        
        <!-- Success/Error Message -->
<?php if (isset($_GET['success'])): ?>
    <div id="message" class="alert alert-success text-center">Quarter statuses successfully updated.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div id="message" class="alert alert-danger text-center">Error updating quarter statuses. Please try again.</div>
<?php endif; ?>


        <!-- Clearance Details -->
        <div class="clearance-details mb-4">
    <div class="row">
        <div class="col-md-6 mb-3">
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['studentID']); ?></p>
        </div>
        <div class="col-md-6 mb-3">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['fname'] . " " . $student['lname']); ?></p>
        </div>
        <div class="col-md-6 mb-3">
            <p><strong>Tuition:</strong>Php  <?php echo htmlspecialchars($student['totalTuition']); ?></p>
        </div>
        <div class="col-md-6 mb-3">
            <p><strong>Remaining Balance:</strong>Php  <?php echo htmlspecialchars($student['remainingbalance']); ?></p>
        </div>
        <div class="col-md-6 mb-3">
            <p><strong>Per Exam:</strong> Php 3000</p>
        </div>
    
    
        
    </div>
    <div class="row">
    <div class="col-md-3 mb-3">
        <p><strong>1Q:</strong> <?php echo htmlspecialchars($student['firstQuarter']); ?></p>
    </div>
    <div class="col-md-3 mb-3">
        <p><strong>2Q:</strong> <?php echo htmlspecialchars($student['secondQuarter']); ?></p>
    </div>
    <div class="col-md-3 mb-3">
        <p><strong>3Q:</strong> <?php echo htmlspecialchars($student['thirdQuarter']); ?></p>
    </div>
    <div class="col-md-3 mb-3">
        <p><strong>4Q:</strong> <?php echo htmlspecialchars($student['fourthQuarter']); ?></p>
    </div>
</div>

</div>


        <!-- Clearance Form -->
<form method="POST" action="clearance_update.php">
    <input type="hidden" name="accountID" value="<?php echo htmlspecialchars($accountID); ?>">
    
    <div class="row">
        <?php
        $quarters = [
            'firstQuarter' => 'First Quarter',
            'secondQuarter' => 'Second Quarter',
            'thirdQuarter' => 'Third Quarter',
            'fourthQuarter' => 'Fourth Quarter'
        ];
        $counter = 0;
        foreach ($quarters as $field => $label): 
            // Start a new row after every 2 quarters
            if ($counter % 2 == 0 && $counter > 0) {
                echo '</div><div class="row">'; // Close current row and start a new one
            }
        ?>
            <div class="col-md-6 mb-3">
                <label for="<?php echo $field; ?>"><?php echo $label; ?> </label>
                <select class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" required>
                    <option value="" disabled>Select Option</option>
                    <option value="Partial" <?php echo ($student[$field] == 'Partial') ? 'selected' : ''; ?>>Partial</option>
                    <option value="Paid" <?php echo ($student[$field] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Not Cleared" <?php echo ($student[$field] == 'Not Cleared') ? 'selected' : ''; ?>>Not Cleared</option>
                </select>
            </div>
        <?php 
            $counter++; 
        endforeach; 
        ?>
    </div>

    <div class="text-center mt-4">
        <button type="submit" class="btn btn-success w-100">Update Clearance</button>
    </div>
</form>

        <!-- Return and Dashboard Buttons -->
        <div class="row justify-content-center mt-4">
            
            <div class="col-auto">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Check if the message is present in the DOM
    window.onload = function() {
        var message = document.getElementById("message");
        if (message) {
            // Show the message if it's present
            message.style.display = "block";

            // Hide the message after 4 seconds
            setTimeout(function() {
                message.style.display = "none";
            }, 4000); // 4 seconds
        }
    }
</script>

</body>
</html>
