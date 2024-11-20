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
    <link rel="stylesheet" href="css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css"  type="text/css"   rel="stylesheet">
    <style>

        /* Basic modal styling */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.4); /* Background color with transparency */
    padding-top: 100px;
}

/* Modal content */
.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 10px;
    border: 1px solid #888;
    border-radius: 8px;
    width: 30%; /* You can adjust the width as needed */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Modal text */
.modal-content p {
    font-size: 16px;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

/* Button styling */
button.close {
    background-color: #f44336; /* Red */
    color: white;
    padding: 12px 24px;
    margin: 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

button.close:hover {
    background-color: #e53935; /* Darker red */
  
}

button#cancelLogout {
   
    background-color: #4CAF50; /* Green */
}

button#cancelLogout:hover {
    background-color: #45a049; /* Darker green */
}

/* Modal show and hide animation */
.modal.fade-in {
    animation: fadeIn 0.3s ease-in;
}

.modal.fade-out {
    animation: fadeOut 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

    

        </style>

</head>
<body>
    <header>
        <div class="mainheader">
            <p>Web-based School Fees <br>
            <span class="subheading">Management System</span></p>
        </div>
    </header>

    <main>
       <!-- Burger Menu -->
<input type="checkbox" class="toggle-Sidebar" id="toggle-Sidebar">
<label for="toggle-Sidebar" class="toggle-icon">
    <div class="bar-top"></div>
    <div class="bar-center"></div>
    <div class="bar-bottom"></div>
</label>

 <!-- Sidebar -->
       <!-- Sidebar -->
       <div class="sidebar">
            <ul class="menu">
                <div class="profile">
                    <a href="dashboard.php"><img src="img/school-logo.png" alt="school logo"></a>  
                    <a href="dashboard.php" id="dashboardLink"><h3>ADMIN DASHBOARD</h3></a>
                </div>

                <!-- Menu links -->
                <li><a href="add.php">Add Student</a></li>
                <li><a href="studentInfo.php" >Student Info</a></li>
                <li><a href="accounting.php" >Accounting</a></li>
                <li><a href="javascript:void(0);" id="logoutLink">Logout</a></li>
            </ul>
        </div>





        <!-- Dashboard Section -->
        <div class="dashboard" id="dashboardSummary" class="container">
            <!-- Summary Section -->
            <div class="dashboard-header text-left mb-4">
                <h3>Summary</h3>
            </div>
            <div class="dashboard-content d-flex justify-content-around flex-wrap  custom-margin">
                <div class="stat-card">
                    <img src="img/students.png" alt="student" class="stat-icon">
                    <h5>ENROLLEES</h5>
                    <p>No. <?php echo  $total_students ?></p>
                </div>
                <div class="stat-card">
                    <img src="img/earnings.png" alt="earnings" class="stat-icon">
                    <h5 class="text-center mb-3" style="color: black; ">COLLECTED FEES</h5>
                    <p>₱ <?php echo number_format($total_paymentAmount, 2) ?></p>
                </div>
               
            

                <div class="stat-card">
                    <img src="img/invoice.png" alt="bill" class="stat-icon">
                    <h5 class="text-center mb-3" style="color: black; ">TO BE COLLECT</h5>

                    <p>₱  <?php echo number_format($total_tuition, 2) ?></p>
                </div>
            </div>

            <!-- Student Records Table -->
            <div class="table-container" class="container">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <h3 class="mb-1">Student Records</h3>
                </div>
                <!-- Search bar -->

                <form action="result.php" method="GET" class="mb-4 d-flex" style="max-width: 300px;">
    <input name="search" type="search" placeholder="Search by ID..." class="form-control me-2" style="padding: 15px;">
    <button type="submit" class="btn btn-primary" style="height: 40px; padding: 0 20px;">Search </button>
        </form>


                <!-- Table content -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                          
                                <th>Student ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Grade</th>
                                <th>Section</th> 
                                <th>Email</th>
                                <th>School Year</th>
                                <th>Current Balance</th>
                                <th>Total Tuition</th>
                                <th>SOA request</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = mysqli_fetch_array($fetch)) { ?>
                                <tr>
                               
                                    <td><?php echo htmlspecialchars($r['studentID']); ?></td>
                                    <td><?php echo htmlspecialchars($r['fname']); ?></td>
                                    <td><?php echo htmlspecialchars($r['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($r['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($r['age']); ?></td>
                                    <td><?php echo htmlspecialchars($r['grade']); ?></td>   
                                    <td><?php echo htmlspecialchars($r['section']); ?></td> 
                                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                                    <td><?php echo htmlspecialchars($r['school_year']); ?></td>
                                    <td><?php echo htmlspecialchars($r['remainingbalance']); ?></td>
                                    <td><?php echo htmlspecialchars($r['totalTuition']); ?></td>
                                    <td>Yes</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
        <a href="view.php?ID=<?php echo htmlspecialchars($r['accountID']); ?>" class="btn btn-sm btn-primary me-2">View Info</a>
        <a href="update.php?ID=<?php echo htmlspecialchars($r['accountID']); ?>" class="btn btn-sm btn-secondary me-2">Update</a>
                            <!--Hide Delete-->
                            <a href="delete.php?ID=<?php echo htmlspecialchars($r['accountID']); ?>" class="btn btn-sm btn-danger" style="display:none;">Delete</a>




                            </div>
                                    </td>
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

      

</main>

   <!-- Footer -->
    <!-- Footer -->
 <footer class="footer bg-dark text-light text-center py-2">
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




</body> <!-- JavaScript for Modal -->
<script src="bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
<script src="javascript/script.js">

</script>


</html>
