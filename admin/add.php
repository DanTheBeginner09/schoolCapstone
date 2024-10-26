<?php
// Start the session at the very beginning
session_start();

include_once("dbconnection/connect.php");
$con = connection(); // Establish database connection

// Initialize messages
$errorMessage = "";
$successMessage = "";

// Function to check if email already exists
function checkEmailExists($email, $con) {
    // Prepare the SQL statement
    $stmt = $con->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0; // Returns true if email exists
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add-student'])) {
    // Collect and sanitize input values
    $fname = $con->real_escape_string($_POST['fname']);
    $lname = $con->real_escape_string($_POST['lname']);
    $gender = $con->real_escape_string($_POST['gender']);
    $age = (int)$_POST['age']; // Cast to integer for safety
    $studentEmail = $con->real_escape_string($_POST['studentEmail']);
    $schoolYear = $con->real_escape_string($_POST['schoolYear']);
    $grade = isset($_POST['grade']) ? $con->real_escape_string($_POST['grade']) : ''; // Check for existence

    // Initialize error message variable
    $errorMessage = '';

    // Check if the email already exists
    if (checkEmailExists($studentEmail, $con)) {
        $errorMessage = "Email already in use. Please enter another email.";
    } else {
        // Handle file upload if a file is selected
        $uploadFile = null; // Initialize variable
        if (isset($_FILES['myfile']) && $_FILES['myfile']['error'] == UPLOAD_ERR_OK) {
            // Set the upload directory
            $uploadDir = 'uploads/'; // Ensure this directory exists

            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadFile = $uploadDir . basename($_FILES['myfile']['name']);

            // Move the uploaded file to the designated directory
            if (!move_uploaded_file($_FILES['myfile']['tmp_name'], $uploadFile)) {
                $errorMessage = "File upload failed.";
            }
        }

        // Only proceed if there was no error during file upload
        if (empty($errorMessage)) {
            // Auto-generate the StudentID in the format "2024-0000"
            $currentYear = date("Y");
            $stmt = $con->prepare("SELECT MAX(studentID) as last_id FROM students WHERE studentID LIKE ?");
            $likePattern = $currentYear . '-%'; // Match IDs for the current year
            $stmt->bind_param("s", $likePattern);

            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                // Get the last studentID
                $lastID = isset($row['last_id']) ? $row['last_id'] : '';
                $nextID = 1; // Default to 1 if there's no lastID
                
                // Extract the numeric part and increment
                if ($lastID) {
                    $parts = explode('-', $lastID);
                    if (count($parts) == 2 && $parts[0] == $currentYear) {
                        $nextID = intval($parts[1]) + 1; // Increment the last four digits
                    }
                }
                
                // Generate the new StudentID
                $studentID = $currentYear . '-' . str_pad($nextID, 4, '0', STR_PAD_LEFT); // Ensure 4 digits
            } else {
                $errorMessage = "Failed to prepare SQL for fetching last ID: " . $con->error;
            }

            // Prepare SQL statement based on whether a file was uploaded
            if ($uploadFile) {
                $stmt = $con->prepare("INSERT INTO students (fname, lname, gender, age, studentID, email, school_year, grade, import_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiissss", $fname, $lname, $gender, $age, $studentID, $studentEmail, $schoolYear, $grade, $uploadFile);
            } else {
                $stmt = $con->prepare("INSERT INTO students (fname, lname, gender, age, studentID, email, school_year, grade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiisss", $fname, $lname, $gender, $age, $studentID, $studentEmail, $schoolYear, $grade);
            }

            // Check if the statement preparation was successful
            if (!$stmt) {
                die("Error preparing SQL statement: " . $con->error);
            }

            // Execute the statement
            if ($stmt->execute()) {
                $successMessage = "Student added successfully!";
            } else {
                $errorMessage = "Error adding student: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Pagination Logic
$page_no = 1;
if (isset($_GET['page_no']) && is_numeric($_GET['page_no']) && $_GET['page_no'] > 0) {
    $page_no = (int)$_GET['page_no'];
}

// Calculate previous and next pages
$previous_page = $page_no - 1;
$next_page = $page_no + 1;

$total_records_per_page = 5; // Records per page
$offset = ($page_no - 1) * $total_records_per_page;

// Count total records
$sql_count = "SELECT COUNT(*) as total_records FROM students";
$result_count = mysqli_query($con, $sql_count);
$total_records = 0;
if ($result_count) {
    $row = mysqli_fetch_assoc($result_count);
    $total_records = $row['total_records'];
}

// Calculate total pages
$total_no_of_pages = ceil($total_records / $total_records_per_page);

// Fetch paginated records using prepared statements
$sql = "SELECT * FROM students ORDER BY studentID DESC LIMIT ?, ?";
$stmt_fetch = $con->prepare($sql);
if ($stmt_fetch) {
    $stmt_fetch->bind_param("ii", $offset, $total_records_per_page);
    $stmt_fetch->execute();
    $fetch = $stmt_fetch->get_result();
} else {
    $fetch = false;
    $errorMessage = "Error preparing the statement for fetching records.";
}

// Summary: Total students enrolled
$sql = "SELECT COUNT(studentID) AS total_students FROM students";
$result = $con->query($sql);
$total_students = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_students'] : 0;

// Close the connection at the end of the script
$con->close(); // Ensures all operations are complete
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

    
    <!-- Add Student Container -->
    <div id="addStudentContainer" class="container container1" style="margin-top: 70px;">



    <div class="d-flex justify-content-center align-items-start mb-2 flex-wrap">
        <!-- Add Student Modal Content -->
        <div class="box">

            <!-- Display Success and Error Messages -->
<?php if (!empty($successMessage)) : ?>
    <div id="success-message" class="alert alert-success mt-3">
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
    <script>
        // Automatically hide the success message after 5 seconds
        setTimeout(function() {
            document.getElementById('success-message').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>

<?php if (!empty($errorMessage)) : ?>
    <div id="error-message" class="alert alert-danger mt-3">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
    <script>
        // Automatically hide the error message after 5 seconds
        setTimeout(function() {
            document.getElementById('error-message').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>


            <div class="modal-content" id="modal">
     


        <h2 class="modal-title" style="margin-bottom: 20px;">Add Student</h2>

                

                <form id="addStudentForm" action="#addStudentContainer" method="POST" enctype="multipart/form-data">
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
                            <input type="number" id="age" name="age" required>
                        </div>
                    </div>

                    <div class="row">

                    <div class="col">
                            <label for="grade">Grade:</label>
                            <select id="grade" name="grade" required>
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
                            <label for="studentEmail">Email:</label>
                            <input type="email" id="studentEmail" name="studentEmail" required>
                        </div>
                       

                        
             </div>
                    <div class="row">
                         
                    <div class="col">
    <label for="studentID">Student ID:</label>
    <input type="text" id="studentID" name="studentID" 
       value="<?php echo isset($studentID) ? htmlspecialchars($studentID) : ''; ?>" 
       placeholder="Auto-filled Student ID" readonly>

</div>

<div class="col">
    <label for="schoolYear">School Year:</label>
    <select id="schoolYear" name="schoolYear" required>
        <option value="SY 2024-2025">SY 2024-2025</option>
        <option value="SY 2025-2026">SY 2025-2026</option>
    </select>
</div>

<div class="col">
    <label for="myfile" class="lblImport">Import Data (Optional):</label>
    <input type="file" id="myfile" name="myfile">
</div>
</div>


                    <button type="submit" class="btn-submit" name="add-student">SAVE</button>

                    <div class="container mt-3 d-flex justify-content-center">
    <a href="dashboard.php" class="btn btn-light btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

                </form>
            </div>
        </div>
    </div>
</div>



</body>

<script src="javascript/script.js">






</script>



</html>
