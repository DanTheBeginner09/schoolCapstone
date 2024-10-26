<?php
    // Start the session at the very beginning
    session_start();

    include_once("dbconnection/connect.php");
    $con = connection(); // Establish database connection

    // Initialize messages
    $errorMessage = "";
    $successMessage = "";

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
    
    // Debug: Output the lastID
    error_log("Last Student ID: " . $lastID);
    
    // Extract the numeric part and increment
    if ($lastID) {
        $parts = explode('-', $lastID);
        if (count($parts) == 2 && $parts[0] == $currentYear) {
            $nextID = intval($parts[1]) + 1; // Increment the last four digits
        }
    }
    
    // Debug: Output the nextID before formatting
    error_log("Next ID before formatting: " . $nextID);
    
    // Generate the new StudentID
    $studentID = $currentYear . '-' . str_pad($nextID, 4, '0', STR_PAD_LEFT); // Ensure 4 digits
    
    // Debug: Output the generated studentID
    error_log("Generated Student ID: " . $studentID);
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

    
  


    <div id="accountingContainer" class="container container3" style="margin-top: 75px; background-color: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 8px;">
  


<h3 class="text-left">School Cashier</h3>

 
    <!-- Search bar -->
    <div class="search-container d-flex align-items-center mb-3">
                    <input type="search" placeholder="Search..." class="form-control" style="max-width: 200px;">
                    <button class="btn btn-primary ms-2">Search</button>

                    <div class="container mt-3 d-flex justify-content-end">
    <a href="dashboard.php" class="btn btn-light btn-sm custom-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>



                </div>


    
    <div class="row">
        <div class="col-md-6 mb-3">
            <h3 class="personalinfo">Billing Details</h3>
            <div class="personal-details">
                <div class="row mb-2">
                    <div class="col"><strong>ID Number:</strong> <span>2024-2123</span></div>
                    <div class="col"><strong>Name:</strong> <span>John Doe</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col"><strong>Grade:</strong> <span>1</span></div>
                    <div class="col"><strong>Email:</strong> <span>sample@gmail.com</span></div>
                </div>
                
                <table class="table table-bordered mt-3 billing-details">
                    <thead class="table-dark">
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
                            <td><input type="number" name="registration" value="500" class="form-control" required min="0" step="0.01"></td>
                            <td><strong>Downpayment:</strong></td>
                            <td><input type="number" name="downpayment" value="500" class="form-control" required min="0" step="0.01"></td>
                        </tr>
                        <tr>
                            <td><strong>Miscellaneous:</strong></td>
                            <td><input type="number" name="miscellaneous" value="2000" class="form-control" required min="0" step="0.01"></td>
                            <td><strong>Lab/RLE:</strong></td>
                            <td><input type="number" name="lab/rle" value="4000" class="form-control" required min="0" step="0.01"></td>
                        </tr>
                        <tr>
                            <td><strong>Tuition:</strong></td>
                            <td><input type="number" name="tuition" value="5000" class="form-control" required min="0" step="0.01"></td>
                            <td><strong>Total:</strong></td>
                            <td><input type="number" name="total" value="12000" class="form-control" required min="0" step="0.01"></td>
                        </tr>
                        <tr>
                            <td><strong>Quarter:</strong></td>
                            <td>
                                <select name="quarter" class="form-control" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </td>
                            <td><strong>Per Exam:</strong></td>
                            <td><input type="number" name="exam" value="12000" class="form-control" required min="0" step="0.01"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <h3 class="personalinfo">Update Student Account</h3>
            <form id="payment-form">
                <p><strong>Current Balance:</strong> <span id="current-balance">12000</span></p>
                <h5>Student Clearance</h5>
                <div class="row mb-3">
                    <div class="col">
                        <strong>1st Quarter:</strong>
                        <select name="first_quarter" class="form-control" required>
                            <option value="cleared" class="text-success">Cleared</option>
                            <option value="not_cleared" class="text-danger">Not Cleared</option>
                        </select>
                    </div>
                    <div class="col">
                        <strong>2nd Quarter:</strong>
                        <select name="second_quarter" class="form-control" required>
                            <option value="cleared" class="text-success">Cleared</option>
                            <option value="not_cleared" class="text-danger">Not Cleared</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <strong>3rd Quarter:</strong>
                        <select name="third_quarter" class="form-control" required>
                            <option value="cleared" class="text-success">Cleared</option>
                            <option value="not_cleared" class="text-danger">Not Cleared</option>
                        </select>
                    </div>
                    <div class="col">
                        <strong>4th Quarter:</strong>
                        <select name="fourth_quarter" class="form-control" required>
                            <option value="cleared" class="text-success">Cleared</option>
                            <option value="not_cleared" class="text-danger">Not Cleared</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Update</button>
            </form>
        </div>
    </div>
</div>




</body>

<script src="javascript/script.js">






</script>



</html>
