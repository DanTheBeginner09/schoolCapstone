<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Get accountID from URL
if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Fetch existing data
    $stmt = $con->prepare("SELECT accountID, studentID, fname, lname, gender, age, grade, email, remainingbalance, totalTuition, status, school_year, firstQuarter, secondQuarter, thirdQuarter, fourthQuarter FROM students WHERE accountID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Invalid Account ID.");
}

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Update student data on form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update-student'])) {
    $fname = $con->real_escape_string($_POST['fname']);
    $lname = $con->real_escape_string($_POST['lname']);
    $gender = $con->real_escape_string($_POST['gender']);
    $age = (int)$_POST['age'];
    $grade = $con->real_escape_string($_POST['grade']);
    $studentEmail = $con->real_escape_string($_POST['studentEmail']);
    
    // Update statement with additional fields
    $stmt = $con->prepare("UPDATE students SET fname = ?, lname = ?, gender = ?, age = ?, grade = ?, email = ? WHERE accountID = ?");
    $stmt->bind_param("sssissi", $fname, $lname, $gender, $age, $grade, $studentEmail, $accountID);

    if ($stmt->execute()) {
        $successMessage = "Student information updated successfully!";
    } else {
        $errorMessage = "Error updating student information: " . $stmt->error;
    }
    $stmt->close();
}

// Handle form submission to update clearance status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($student['accountID'])) {
    // Make sure the form has been submitted with clearance data
    if (isset($_POST['quarter1']) && isset($_POST['quarter2']) && isset($_POST['quarter3']) && isset($_POST['quarter4'])) {
        // Escape input to prevent SQL injection
        $quarter1 = mysqli_real_escape_string($con, $_POST['quarter1']);
        $quarter2 = mysqli_real_escape_string($con, $_POST['quarter2']);
        $quarter3 = mysqli_real_escape_string($con, $_POST['quarter3']);
        $quarter4 = mysqli_real_escape_string($con, $_POST['quarter4']);

        // Use a prepared statement to update clearance status
        $updateQuery = "UPDATE students SET 
                        firstQuarter = ?, 
                        secondQuarter = ?, 
                        thirdQuarter = ?, 
                        fourthQuarter = ? 
                        WHERE accountID = ?";

        $stmt = $con->prepare($updateQuery);
        $stmt->bind_param("ssssi", $quarter1, $quarter2, $quarter3, $quarter4, $accountID);

        if ($stmt->execute()) {
            echo "<script>alert('Clearance status updated successfully.');</script>";
        } else {
            echo "<script>alert('Update failed: " . mysqli_error($con) . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all clearance fields.');</script>";
    }
}
?>

<!-- In your HTML section where you are using the $student array, check if firstQuarter exists -->
<?php
if (isset($student['firstQuarter'])) {
    echo "First Quarter: " . htmlspecialchars($student['firstQuarter']);
} else {
    echo "First Quarter: Not set";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="mainheader">
            <p>Web-based School Fees <br>
            <span class="subheading">Management System</span></p>
        </div>
    </header>

    <div class="container mt-15 bg-light bg-opacity-75 p-4 rounded shadow" style="margin-top: 75px;">
        <div class="container mt-3 d-flex justify-content-end">
            <a href="dashboard.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h3 class="text-left">Display Information</h3>
  
        <div class="row mb-4">
            <div class="col-md-6 personalinfoContainer">
                <h4 class="personalinfo">Student Details</h4>
                <div class="personal-details bg-light p-3 rounded">
                    <div class="row mb-2">
                        <div class="col"><strong>ID Number:</strong> <span><?php echo htmlspecialchars($student['studentID']); ?></span></div>
                        <div class="col"><strong>Remaining Balance:</strong> <span><?php echo htmlspecialchars($student['remainingbalance']); ?></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><strong>First Name:</strong> <span><?php echo htmlspecialchars($student['fname']); ?></span></div>
                        <div class="col"><strong>Last Name:</strong> <span><?php echo htmlspecialchars($student['lname']); ?></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><strong>Gender:</strong> <span><?php echo htmlspecialchars($student['gender']); ?></span></div>
                        <div class="col"><strong>Age:</strong> <span><?php echo htmlspecialchars($student['age']); ?></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><strong>Grade:</strong> <span><?php echo htmlspecialchars($student['grade']); ?></span></div>
                        <div class="col"><strong>Email:</strong> <span><?php echo htmlspecialchars($student['email']); ?></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><strong>Status:</strong> <span class="status-button badge bg-success"><?php echo htmlspecialchars($student['status']); ?></span></div>
                        <div class="col"><strong>SY:</strong> <span><?php echo htmlspecialchars($student['school_year']); ?></span></div>
                    </div>
                    <h4>Student Clearance</h4>
                    <div class="row mb-2">
                        <div class="col"><strong>1st Quarter:</strong> <?php echo htmlspecialchars($student['firstQuarter']); ?></span></div>
                        <div class="col"><strong>2nd Quarter:</strong> <span> <?php echo htmlspecialchars($student['secondQuarter']); ?></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><strong>3rd Quarter:</strong> <span> <?php echo htmlspecialchars($student['thirdQuarter']); ?></span></div>
                        <div class="col"><strong>4th Quarter:</strong> <span> <?php echo htmlspecialchars($student['fourthQuarter']); ?></span></div>
                    </div>
                    
                </div>
            </div>

            <div class="col-md-6">
                <h4 class="personalinfo">Billing Details</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Item</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Registration:</strong></td>
                                <td><span>500</span></td>
                                <td><strong>Downpayment:</strong></td>
                                <td><span>500</span></td>
                            </tr>
                            <tr>
                                <td><strong>Miscellaneous:</strong></td>
                                <td><span>2000</span></td>
                                <td><strong>Lab/RLE:</strong></td>
                                <td><span>4000</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tuition:</strong></td>
                                <td><span>5000</span></td>
                                <td><strong>Per Exam:</strong></td>
                                <td><span>2875</span></td>
                            </tr>
                            <tr>
                                <td><strong>Quarter:</strong></td>
                                <td><span>4</span></td>
                                <td><strong>Total:</strong></td>
                                <td><span><?php echo htmlspecialchars($student['totalTuition']); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div>
        
        <!-- Centered Buttons -->
        <div class="d-flex justify-content-center mb-3">
            <button type="button" class="btn btn-success me-3" data-bs-toggle="modal" data-bs-target="#confirmEnableModal">Enable Access</button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDisableModal">Disable Access</button>
        </div>
    </div>
</div>

<!-- Modals for enabling/disabling access -->
<div class="modal fade" id="confirmEnableModal" tabindex="-1" role="dialog" aria-labelledby="confirmEnableModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmEnableModalLabel">Enable Access                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to enable access for this student?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDisableModal" tabindex="-1" role="dialog" aria-labelledby="confirmDisableModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDisableModalLabel">Disable Access</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to disable access for this student?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-Ol5c7lY7d6l2h9p1e8C3a7L2d7e7b8c8U2J1t6n6K1F1qg8B1t4e1t5h5f5e6f6" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-5f3Q1t9I1f2e2c9g6f6y6N1e1b1e2c9g6f6y6N1e1b1e2c9g6f6y6N1e1b1e2c9" crossorigin="anonymous"></script>
<script src="javascript/script.js"></script>

</body>
</html>