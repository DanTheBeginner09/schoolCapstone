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
$sql_students = "SELECT COUNT(studentID) AS total_students FROM students";
$result_students = $con->query($sql_students);

// Check if the query was successful
if ($result_students === FALSE) {
die("Error in SQL query: " . $con->error);
}

// Fetch the total number of students
$row_students = $result_students->fetch_assoc();
$total_students = isset($row_students['total_students']) ? intval($row_students['total_students']) : 0;

// Step 1: Get the total tuition amount
$sql_tuition = "SELECT SUM(totalTuition) AS totalTuition FROM students"; // Adjust this query based on your actual table structure
$result_tuition = $con->query($sql_tuition);

// Check if the query was successful
if ($result_tuition === FALSE) {
die("Error in SQL query: " . $con->error);
}

// Fetch the total tuition amount
$row_tuition = $result_tuition->fetch_assoc();
$total_tuition = isset($row_tuition['totalTuition']) ? floatval($row_tuition['totalTuition']) : 0;

// Step 2: Get the total payment amount
$sql_paymentAmount = "SELECT SUM(paymentAmount) AS paymentAmount FROM students"; // Adjust this query based on your actual table structure
$result_paymentAmount = $con->query($sql_paymentAmount);

// Check if the query was successful
if ($result_paymentAmount === FALSE) {
die("Error in SQL query: " . $con->error);
}

// Fetch the total payment amount
$row_paymentAmount = $result_paymentAmount->fetch_assoc();
$total_paymentAmount = isset($row_paymentAmount['paymentAmount']) ? floatval($row_paymentAmount['paymentAmount']) : 0;

// Step 3: Get the count of students with zero balance
$sql_zero_balance = "SELECT COUNT(studentID) AS remainingbalance FROM students WHERE remainingbalance = 0";
$result_zero_balance = $con->query($sql_zero_balance);

// Check if the query was successful
if ($result_zero_balance === FALSE) {
die("Error in SQL query: " . $con->error);
}

// Step 4: Calculate the amount to be collected per student
$amount_per_student = $total_students > 0 ? $total_tuition / $total_students : 0;

// Output the results
//echo "Total Students Enrolled: " . $total_students . "<br>";
//echo "Total Tuition Amount: ₱" . number_format($total_tuition, 2) . "<br>";
//echo "Total Payment Amount: ₱" . number_format($total_paymentAmount, 2) . "<br>";
//echo "Amount to be Collected per Student: ₱" . number_format($amount_per_student, 2) . "<br>";
//echo "Number of Students with Zero Balance: " . $zero_balance_count . "<br>";

// Close the connection at the end of the script
$con->close(); // Ensures all operations are complete

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<style>

    /* Sidebar styles */
.sidebar {
    position: fixed; /* Ensure the sidebar is fixed */
    z-index: 2; /* Lower z-index to keep it behind the content */
    transition: transform 0.3s ease;
}

/* Main content styles */
.main-content {
    position: relative; /* Ensure that the main content is positioned above the sidebar */
    z-index: 1; /* Higher z-index to make sure the content stays in front */
}
      .tagName {
        
        background-color: red;
        color: #fff;
        padding: 10px;
        font-size: 18px;
        font-weight: bold;
    }

    .border{
        width: 100%;
       
    }

    .container {
        margin-top: 50px;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
         margin-bottom: 10px;
    }
    .content {
        padding: 20px;
    }
    .content label {
        margin-top: -10px;
        display: block;
        margin-bottom: 5px;
        font-size: 16px;
    }
    .content input[type="file"] {
        width: 100%;
        padding: 10px;
       
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .content p {
        font-size: 14px;
        color: #666;
     
    }
    .content button {
        background-color: green;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }
    .content button i {
        margin-right: 5px;
    }

    

        .main-content{
            margin: 40px 0;
        }


</style>


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

    <div class="container">

        <!-- Title and Search Bar Block -->
        <div class="search-block text-center my-4">
            <h4 class="mb-3">ACCOUNTING DEPARTMENT</h4>
            <form action="accountingView.php" method="GET" class="d-flex flex-column flex-md-row align-items-center justify-content-center">
                <input name="search" type="search" placeholder="Search By ID..." class="form-control mb-2 mb-md-0 me-md-2" style="padding: 8px; flex: 1; max-width: 400px;">
                <button type="submit" class="btn btn-primary" style="height: 40px; padding: 0 20px; min-width: 120px;">Search</button>
            </form>
        </div>

       
        <!-- Student Details Section -->
        <div class="container table-responsive">
            <div class="tagName">List of enrolled students</div>
            <div class="row mb-4">
                    
                <!-- Table content -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                          
                                <th>Student ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                               
                                <th>Grade</th>
                                <th>Email</th>
                                <th>School Year</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = mysqli_fetch_array($fetch)) { ?>
                                <tr>
                               
                                    <td><?php echo htmlspecialchars($r['studentID']); ?></td>
                                    <td><?php echo htmlspecialchars($r['fname']); ?></td>
                                    <td><?php echo htmlspecialchars($r['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($r['gender']); ?></td>
                                   
                                    <td><?php echo htmlspecialchars($r['grade']); ?></td>
                                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                                    <td><?php echo htmlspecialchars($r['school_year']); ?></td>
                                   
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (without page numbers) -->
                <nav aria-label="Page navigation example">
    <ul class="pagination">
        <!-- First Page -->
        <li class="page-item <?= ($page_no <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page_no=1">First</a>
        </li>

        <!-- Previous Page -->
        <li class="page-item <?= ($page_no <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?= ($page_no > 1) ? '?page_no=' . $previous_page : '#'; ?>">Previous</a>
        </li>

        <!-- Next Page -->
        <li class="page-item <?= ($page_no >= $total_no_of_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?= ($page_no < $total_no_of_pages) ? '?page_no=' . $next_page : '#'; ?>">Next</a>
        </li>

        <!-- Last Page -->
        <li class="page-item <?= ($page_no >= $total_no_of_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page_no=<?= $total_no_of_pages; ?>">Last</a>
        </li>
    </ul>
</nav>

    <!-- Page Info -->
    <div class="p-2">
        <strong>Page <?= $page_no; ?> of <?= $total_no_of_pages; ?> </strong>
    </div>


</div> 


            
            </div>
        </div>
    </div>
</div>




<!-- Footer -->
<footer class="footer bg-dark text-light text-center py-2 mt-5">
        <p>&copy; Carlgeline Gabilla & Jessa Mae Canaway Capstone Project  2024. All rights reserved.</p>
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
