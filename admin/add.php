<?php
if (!isset($_SESSION)) {
    session_start();
}

include_once("dbconnection/connect.php");
$con = connection();

$errorMessage = ''; // Initialize empty error message
$successMessage = ''; // Initialize empty success message

if (isset($_POST['add-student'])) {
    // Get the input data safely
    $fname = isset($_POST['fname']) ? $_POST['fname'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $age = isset($_POST['age']) ? $_POST['age'] : '';
    $gradelvl = isset($_POST['gradelvl']) ? $_POST['gradelvl'] : null;
    $email = isset($_POST['studentEmail']) ? $_POST['studentEmail'] : '';

    // Check for required field - Grade level
    if ($gradelvl === null || empty($gradelvl)) {
        $errorMessage = "Grade level not selected!";
    }

    // Step 1: Auto-increment logic for student ID
    $sql = "SELECT studentID FROM students ORDER BY studentID DESC LIMIT 1"; // Fetch the last studentID
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastStudentID = $row['studentID'];

        // Extract the last 4 digits and increment them
        $lastDigits = substr($lastStudentID, -4); // Get last 4 digits
        $newDigits = str_pad((int)$lastDigits + 1, 4, '0', STR_PAD_LEFT); // Increment and pad to 4 digits
    } else {
        $newDigits = "0001"; // If no student records, start from "0001"
    }

    // Generate the new student ID
    $studentID = "2024-" . $newDigits;

    // Prepare the SQL statement only if all required fields are filled
    if (!empty($fname) && !empty($lname) && !empty($gender) && $gradelvl !== null && empty($errorMessage)) {
        $sql = "INSERT INTO students (fname, lname, gender, age, gradelvl, studentID, email) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Prepare the statement
        $stmt = $con->prepare($sql);

        // Bind the parameters to the query
        $stmt->bind_param("sssisss", $fname, $lname, $gender, $age, $gradelvl, $studentID, $email);

        // Execute the query
        if ($stmt->execute()) {
            $successMessage = "Student added successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        if (empty($errorMessage)) {
            $errorMessage = "Please fill out all required fields!";
        }
    }
}
?> 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


</head>
<body>

    <div class="header">
        <div class="mainheader">
            <p>Web-based school fees <br>
            <span class="subheading">MANAGEMENT SYSTEM</span></p>
        </div>
    </div>

        <!-- HTML Section -->
<div class="box">
    <div class="modal-content" id="modal">
        <span class="close" id="closeBtn">&times;</span>
        <h2 class="modal-title">Student Information</h2>

        <form id="addStudentForm" action="" method="POST">
            <div class="row">
                <div class="col">
                    <label for="fname" class="lblName">First Name:</label>
                    <input type="text" id="fname" name="fname" required>
                </div>
                <div class="col">
                    <label for="lname">Last Name:</label>
                    <input type="text" id="lname" name="lname" required>
                </div>  
            </div>

            <div class="row">
                <div class="col">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value=""></option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                <div class="col">
                    <label for="age">Age:</label>
                    <input type="text" id="age" name="age" required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="gradelvl">Grade:</label>
                    <select id="gradelvl" name="gradelvl" required>
                        <option value=""></option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                    </select>
                </div>
                <div class="col">
                    <label for="studentID">Student ID:</label>
                    <input type="text" id="studentID" name="studentID" value="<?php echo isset($studentID) ? $studentID : ''; ?>" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="studentEmail">Email:</label>
                    <input type="text" id="studentEmail" name="studentEmail" required>
                </div>
                <div class="col">
                    <label for="myfile" class="lblImport">Import Data:</label>
                    <input type="file" id="myfile" name="myfile">
                </div>
            </div>

            <button type="submit" class="btn-submit" name="add-student">SAVE</button>

            <!-- Error and Success Messages -->
            <?php if (!empty($errorMessage)) : ?>
                <div id="error-message" class="error" style="display: block; color: red; text-align: center; margin: 10px auto;">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)) : ?>
                <div id="success-message" class="success" style="display: block; color: green; text-align: center; margin: 10px auto;">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>


    <div class="main-content">
        <!-- Your main content goes here -->
    </div>

    <footer>
        <p>&copy; Gabila & Canaway 2024 Capstone. All rights reserved.</p>
    </footer>

    <script>
        // Select the close button and the modal
        const closeBtn = document.getElementById('closeBtn');
        const modal = document.getElementById('modal');

        // Close the modal when the close button is clicked and redirect to the dashboard
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none'; // Hide the modal
            window.location.href = 'dashboard.php'; // Redirect to dashboard
        });


        // If a success or error message exists, hide it after 4 seconds and redirect
    window.onload = function() {
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        if (successMessage || errorMessage) {
            setTimeout(function() {
                if (successMessage) successMessage.style.display = 'none';
                if (errorMessage) errorMessage.style.display = 'none';
                window.location.href = 'dashboard.php'; // Redirect after 4 seconds
            }, 4000);
        }
    }
    </script>

</body>
</html>
