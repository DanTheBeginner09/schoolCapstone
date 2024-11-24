<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Get accountID from URL
if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Fetch existing data
    $stmt = $con->prepare("SELECT * FROM students WHERE accountID = ?");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
       
    </style>
</head>
<body>
    <header>
        <div class="mainheader">
            <p>Web-based School Fees <br>
            <span class="subheading">Management System</span></p>
        </div>
    </header>

    <div id="addStudentContainer" class="container container1" style="margin-top: 70px;">
        <div class="d-flex justify-content-center align-items-start mb-2 flex-wrap">
            <div class="box">
                <div class="modal-content" id="modal">
                    <h2 class="modal-title" style="margin-bottom: 20px;">Update Student</h2>

                    <form action="update.php?ID=<?php echo $accountID; ?>" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col">
                                <label for="fname" class="lblName">First Name:</label>
                                <input type="text" name="fname" value="<?php echo htmlspecialchars($student['fname']); ?>" required>
                            </div>
                            <div class="col">
                                <label for="lname">Last Name:</label>
                                <input type="text" name="lname" value="<?php echo htmlspecialchars($student['lname']); ?>" required>
                            </div>  
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="gender">Gender:</label>
                                <input type="text" name="gender" value="<?php echo htmlspecialchars($student['gender']); ?>" required>
                            </div>
                            <div class="col">
                                <label for="age">Age:</label>
                                <input type="text" name="age" value="<?php echo htmlspecialchars($student['age']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="grade">Grade:</label>
                                <input type="text" name="grade" value="<?php echo htmlspecialchars($student['grade']); ?>" required>
                            </div>
                            <div class="col">
                                <label for="studentEmail">Email:</label>
                                <input type="text" name="studentEmail" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                        </div>



                        <div style="text-align: center; margin-top: 20px;">
                            <button type="submit" name="update-student" class="btn btn-primary">Update</button>
                        </div>

                        <div class="container mt-3 d-flex justify-content-center">
                            <a href="dashboard.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Display Success or Error Message -->
        <?php if (!empty($successMessage)): ?>
            <div class="overlay" id="successOverlay">
                <div class="alert-overlay">
                    <h5 class="alert alert-success"><?php echo $successMessage; ?></h5>
                </div>
            </div>
            <script>
                // Show overlay
                document.getElementById('successOverlay').style.display = 'flex';
                // Redirect to dashboard after 5 seconds
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 5000);
            </script>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center" style="margin-top: 20px;">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
