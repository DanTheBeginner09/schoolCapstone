<?php
session_start();
include_once("dbconnection/connect.php");

// Establish the database connection
$con = connection();

// Debugging: Check database connection
if ($con === false) {
    die("Error: Unable to connect to the database.");
}

// Ensure accountID is set in the URL
if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) {
    echo "No valid account ID provided.";
    exit;
}

$accountID = $_GET['ID'];

// Fetch student details based on accountID
$sql = "SELECT * FROM students WHERE accountID = ?";
$stmt = $con->prepare($sql);

// Debugging: Check if the prepared statement was successful
if ($stmt === false) {
    die("Error: Failed to prepare the SQL statement. Error: " . $con->error);
}

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

// Handle form submission for updating quarter statuses
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
    
    // Check if prepare statement succeeded
    if ($stmt_update_quarters === false) {
        die("Error: Failed to prepare the update query. Error: " . $con->error);
    }

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
            margin-top: 50px;
            width: 80%;
            margin: 15px auto;
        }

        .btn-custom {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-custom:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 15px;
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
        <?php elseif ($errorMessage): ?>
            <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mt-3">
                        <label for="firstQuarter">First Quarter Status</label>
                        <input type="text" name="firstQuarter" id="firstQuarter" class="form-control" value="<?php echo htmlspecialchars($student['firstQuarter']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mt-3">
                        <label for="secondQuarter">Second Quarter Status</label>
                        <input type="text" name="secondQuarter" id="secondQuarter" class="form-control" value="<?php echo htmlspecialchars($student['secondQuarter']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mt-3">
                        <label for="thirdQuarter">Third Quarter Status</label>
                        <input type="text" name="thirdQuarter" id="thirdQuarter" class="form-control" value="<?php echo htmlspecialchars($student['thirdQuarter']); ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt-3">
                        <label for="fourthQuarter">Fourth Quarter Status</label>
                        <input type="text" name="fourthQuarter" id="fourthQuarter" class="form-control" value="<?php echo htmlspecialchars($student['fourthQuarter']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-custom">UPDATE CLEARANCE</button>
            </div>
        </form>
        
        <div class="text-center mt-4">
        <a href="accounting.php?ID=<?php echo urlencode($accountID); ?>" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2024 Carlgeline Gabilla & Jessa Mae Canaway - Capstone Project. All rights reserved.</p>
</footer>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
            