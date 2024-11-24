<?php
// Start the session at the very beginning
session_start();

// Include database connection
include_once("dbconnection/connect.php");
$con = connection(); // Establish database connection

// Initialize messages
$errorMessage = "";
$successMessage = "";

// Handle form submission for adding a student
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

// Initialize fetch variable
$fetch = null;

// Fetch records based on search term if provided
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $con->real_escape_string($_GET['search']); // Sanitize the search term

    // Prepare the SQL query to search by studentID first, then by fname or lname
    $sql = "SELECT * FROM students WHERE studentID = ? OR fname LIKE ? OR lname LIKE ? ORDER BY studentID DESC LIMIT ?, ?";


    // Prepare the statement
    if ($stmt = $con->prepare($sql)) {
        $likeSearch = "%" . $search . "%"; // For searching by first name or last name
        $stmt->bind_param("ssiii", $search, $likeSearch, $likeSearch, $offset, $total_records_per_page);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Get the result
            $result = $stmt->get_result();

            // Check if there are results
            if ($result && $result->num_rows > 0) {
                $fetch = $result; // Store results for later use in table display
            } else {
                $errorMessage = "No records found.";
            }
        } else {
            $errorMessage = "Error executing query: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $errorMessage = "Error preparing statement: " . $con->error;
    }
} else {
    // Fetch all records with pagination if no search term is provided
    $sql = "SELECT * FROM students LIMIT ?, ?";
    $stmt_fetch = $con->prepare($sql);

    if ($stmt_fetch) {
        $stmt_fetch->bind_param("ii", $offset, $total_records_per_page);
        
        // Execute the statement
        if ($stmt_fetch->execute()) {
            $fetch = $stmt_fetch->get_result(); // Get the result set
            
            // Check if there are results
            if (!$fetch || $fetch->num_rows <= 0) {
                $errorMessage = "No records found.";
            }
        } else {
            $errorMessage = "Error executing fetch query: " . $stmt_fetch->error;
        }
    } else {
        $errorMessage = "Error preparing fetch statement: " . $con->error;
    }

    // Close the statement
    $stmt_fetch->close();
}

// Summary: Total students enrolled
$sql = "SELECT COUNT(studentID) AS total_students FROM students";
$result = $con->query($sql);
$total_students = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_students'] : 0;

// Display total students
echo "Total Students Enrolled: " . htmlspecialchars($total_students) . "<br>";

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
</head>
<body>
    <header>
        <div class="mainheader">
            <p>Web-based School Fees <br>
            <span class="subheading">Management System</span></p>
        </div>
    </header>

  
 
  
    <!-- Student Records Table -->
    <div class="container table-container" style="margin-top: 70px; background-color: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 20px; width: 100%;">

    
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <h3 class="mb-1">Student Records</h3>

      </div>

       <!-- Search bar -->
<form method="GET" action="">
<div class="search-container d-flex align-items-center mb-3">
        <input name="search" type="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search..." class="form-control" style="max-width: 200px;">
        <button class="btn btn-primary ms-2">Search</button>
    </div>
    
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
                                <th>Payment</th>
                                <th>Total Tuition</th>
                                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display fetched records or a message if no records are found
            if ($fetch && $fetch->num_rows > 0) {
                while ($r = $fetch->fetch_assoc()) {
                    ?>
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
                                    <td><?php echo htmlspecialchars($r['paymentAmount']); ?></td>
                                    <td><?php echo htmlspecialchars($r['totalTuition']); ?></td>
                        <td>
                            <div class="d-flex justify-content-center">
                            <a href="view.php?ID=<?php echo htmlspecialchars($r['accountID']); ?>" class="btn btn-sm btn-primary me-2">View Info</a>
                                <a href="update.php?ID=<?php echo htmlspecialchars($r['accountID']); ?>" class="btn btn-sm btn-secondary me-2">Update</a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='8'>No records found.</td></tr>";
            }
            ?>
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


    <div style="text-align: center; margin-top: 20px;">
    <a href="dashboard.php" class="btn btn-light btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>


</div> 


     

</body>




</html>
