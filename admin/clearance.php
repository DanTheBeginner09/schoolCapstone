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

// Handle form submission for updating quarter statuses
$successMessage = "";
$errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .box {
            margin-top: 50px;
            width: 80%;
            margin: 15px auto;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .btn-custom {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-custom:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="mainheader">
        <p>Web-based School Fees <br><span class="subheading">MANAGEMENT SYSTEM</span></p>
    </div>
</div>

<div class="main-content">
    <div class="box">
        <h3 class="text-center">Clearance Update for: <?php echo htmlspecialchars($student['fname'] . " " . $student['lname']); ?></h3>

        <?php if ($successMessage): ?>
            <div class="alert alert-success mt-3"><?php echo $successMessage; ?></div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form id="clearanceForm" method="POST">

            <div class="form-group mt-3">
                <label for="firstQuarter">First Quarter Status</label>
                <input type="text" class="form-control" name="firstQuarter" value="<?php echo $student['firstQuarter']; ?>" required>
            </div>

            <div class="form-group mt-3">
                <label for="secondQuarter">Second Quarter Status</label>
                <input type="text" class="form-control" name="secondQuarter" value="<?php echo $student['secondQuarter']; ?>" required>
            </div>

            <div class="form-group mt-3">
                <label for="thirdQuarter">Third Quarter Status</label>
                <input type="text" class="form-control" name="thirdQuarter" value="<?php echo $student['thirdQuarter']; ?>" required>
            </div>

            <div class="form-group mt-3">
                <label for="fourthQuarter">Fourth Quarter Status</label>
                <input type="text" class="form-control" name="fourthQuarter" value="<?php echo $student['fourthQuarter']; ?>" required>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-custom" id="updateButton">UPDATE CLEARANCE</button>
            </div>
        </form>
        
    </div>
</div>

<!-- Modal for success message -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Quarter statuses were successfully updated.
            </div>
            <div class="modal-footer">
                <button type="button" id="okButton" class="btn btn-primary">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('updateButton').addEventListener('click', function(event) {
        event.preventDefault();  // Prevent form submission

        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();

        // When the user clicks 'OK', submit the form and redirect
        document.getElementById('okButton').onclick = function() {
            // Submit the form after the modal interaction
            document.getElementById('clearanceForm').submit();
            window.location.href = 'clearance.php';  // Redirect to clearance.php after form submission
        };
    });
</script>

</body>
</html>
