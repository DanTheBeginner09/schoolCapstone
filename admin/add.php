<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

$errorMessage = "";
$successMessage = "";

// Function to check if email already exists
function checkEmailExists($email, $con) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
    if (!$stmt) {
        die('Prepare failed: ' . $con->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Function to generate new studentID
function generateStudentID($con) {
    $currentYear = date("Y");
    $likePattern = $currentYear . '-%';

    $stmt = $con->prepare("SELECT MAX(studentID) as last_id FROM students WHERE studentID LIKE ?");
    if (!$stmt) {
        die('Prepare failed: ' . $con->error);
    }
    $stmt->bind_param("s", $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $lastID = isset($row['last_id']) ? $row['last_id'] : '';
    $nextID = 1;

    if ($lastID) {
        $parts = explode('-', $lastID);
        if (count($parts) == 2 && $parts[0] == $currentYear) {
            $nextID = intval($parts[1]) + 1;
        }
    }

    return $currentYear . '-' . str_pad($nextID, 4, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add-student'])) {
    // Sanitize and validate input values
    $fname = $con->real_escape_string(trim($_POST['fname']));
    $lname = $con->real_escape_string(trim($_POST['lname']));
    $gender = $con->real_escape_string(trim($_POST['gender']));
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $studentEmail = filter_var($con->real_escape_string($_POST['studentEmail']), FILTER_SANITIZE_EMAIL);
    $schoolYear = $con->real_escape_string(trim($_POST['schoolYear']));
    $grade = filter_var($con->real_escape_string($_POST['grade']), FILTER_VALIDATE_INT);
    $section = $con->real_escape_string(trim($_POST['section']));
    $date = date("Y-m-d");

    // Input validation
    if (!$age || $age < 1 || $age > 120) {
        $errorMessage = "Invalid age provided.";
    } elseif (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (checkEmailExists($studentEmail, $con)) {
        $errorMessage = "Email already in use. Please enter another email.";
    } else {
        // Generate studentID
        $studentID = generateStudentID($con);

        // Insert student data
        $stmt = $con->prepare("INSERT INTO students (fname, lname, gender, age, studentID, email, school_year, grade, section, date, totalTuition, remainingBalance, paymentAmount, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die('Prepare failed: ' . $con->error);
        }

        $totalTuition = 12000;
        $remainingBalance = 12000;
        $paymentAmount = 0;
        $status = 'Active';

        $stmt->bind_param(
            "sssiissssdddds", 
            $fname, 
            $lname, 
            $gender, 
            $age, 
            $studentID, 
            $studentEmail, 
            $schoolYear, 
            $grade, 
            $section, 
            $date, 
            $totalTuition, 
            $remainingBalance, 
            $paymentAmount, 
            $status
        );

        if ($stmt->execute()) {
            $successMessage = "Student successfully added.";
        } else {
            $errorMessage = "Failed to add student. Please try again.";
        }
        
        // Close the prepared statement
        $stmt->close();
    }
}

$con->close();
?>  




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" type="text/css" rel="stylesheet">
</head>
<body>
<div class="header">
    <div class="mainheader">
        <p>Web-based school fees <br><span class="subheading">MANAGEMENT SYSTEM</span></p>
    </div>
</div>

<input type="checkbox" class="toggle-Sidebar" id="toggle-Sidebar">
<label for="toggle-Sidebar" class="toggle-icon">
    <div class="bar-top"></div>
    <div class="bar-center"></div>
    <div class="bar-bottom"></div>
</label>

<div class="sidebar">
    <div class="profile">
        <a href="dashboard.php"><img src="img/school-logo.png" alt="school logo"></a>  
        <a href="dashboard.php"><h3>ADMIN DASHBOARD</h3></a>
    </div>
    <ul class="menu">
        
    <li><a href="add.php">Add Student</a></li>
                <li><a href="studentInfo.php" >Student Info</a></li>
                <li><a href="accounting.php" >Accounting</a></li>
                <li><a href="javascript:void(0);" id="logoutLink">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="box">
        <h2 class="modal-title" style="margin-bottom: 20px;">ADD STUDENT FORM</h2>
        <div class="addStudentForm">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col">
                        <label for="date" class="lblName">Date:</label>
                        <input type="text" id="date" name="date" value="<?php echo date("Y-m-d"); ?>" readonly>
                    </div>
                    <div class="col">
                        <label for="studentID">Student ID:</label>
                        <input type="text" id="studentID" name="studentID" 
                               value="<?php echo isset($studentID) ? htmlspecialchars($studentID) : ''; ?>" 
                               placeholder="Auto-filled Student ID" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="fname" class="lblName">First Name:</label>
                        <input type="text" id="fname" name="fname" required style="text-transform: capitalize;">

                    </div>
                    <div class="col">
                        <label for="lname">Last Name:</label>
                        <input type="text" id="lname" name="lname" required style="text-transform: capitalize;">
                    </div>  
                </div>
                <div class="row">
                    <div class="col">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required style="text-transform: capitalize;">
                            <option value=""></option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="age" required> 
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="grade">Grade:</label>
                        <select id="grade" name="grade" required >
                            <option value=""></option>
                            <option value="1">Grade 1</option>
                            <option value="2">Grade 2</option>
                            <option value="3">Grade 3</option>
                            <option value="4">Grade 4</option>
                            <option value="5">Grade 5</option>
                            <option value="6">Grade 6</option>
                           
                            <!-- Add other grades as needed -->
                        </select>
                    </div>
                    <div class="col">
                        <label for="studentEmail">Email:</label>
                        <input type="email" id="studentEmail" name="studentEmail" required >
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="section">Section:</label>
                        <select id="section" name="section" required style="text-transform: capitalize;">
                            <option value=""></option>
                            <option value="Rambutan">Rambutan</option>
                            <option value="Lansones">Lansones</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="schoolYear">School Year:</label>
                        <select id="schoolYear" name="schoolYear" required style="text-transform: capitalize;">
                            <option value=""></option>
                            <option value="SY 2024-2025">SY 2024-2025</option>
                            <option value="SY 2025-2026">SY 2025-2026</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit" name="add-student">Submit</button>
            </form>

            <?php if (!empty($errorMessage)) : ?>
                <div id="errorMessage" class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php elseif (!empty($successMessage)) : ?>
                <div id="successMessage" class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>


        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer bg-dark text-light text-center py-2 mt-5">
        <p>&copy; Carlgeline Gabilla & Jessabel Canaway Capstone Project  2024. All rights reserved.</p>
    </footer>


    <!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to logout?</p>
        <button id="confirmLogout" class="close">Yes, Logout</button>
        <button id="cancelLogout" class="close">Cancel</button>
    </div>
</div>

</body>

<script src="javascript/script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if the errorMessage or successMessage exists
        var errorMessage = document.getElementById('errorMessage');
        var successMessage = document.getElementById('successMessage');

        // If either message exists (error or success)
        if (errorMessage || successMessage) {
            // Set a timeout to hide them after 4 seconds
            setTimeout(function() {
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }

                if (successMessage) {
                    successMessage.style.display = 'none';
                }
            }, 3000); // 4000 milliseconds = 4 seconds
        }
    });
</script>


<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</html>
