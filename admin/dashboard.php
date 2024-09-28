<?php
if (!isset($_SESSION)) {
    session_start();
}

include_once("dbconnection/connect.php");
$con = connection(); // Establish database connection

// Get the current page number or set to 1 if not set
if (isset($_GET['page_no']) && $_GET['page_no'] !== "") {
    $page_no = $_GET['page_no'];
} else {
    $page_no = 1;
}

$total_records_per_page = 5; // Set the number of records per page
$offset = ($page_no - 1) * $total_records_per_page; // Calculate the offset for the SQL query

// Count the total number of records
$sql_count = "SELECT COUNT(*) as total_records FROM students";
$result_count = mysqli_query($con, $sql_count);
$total_records = mysqli_fetch_array($result_count)['total_records'];

// Calculate total pages
$total_no_of_pages = ceil($total_records / $total_records_per_page);

// Fetch the paginated records
$sql = "SELECT * FROM students ORDER BY studentID DESC LIMIT $offset, $total_records_per_page";
$fetch = mysqli_query($con, $sql);

if ($fetch && mysqli_num_rows($fetch) > 0) {
    // Display student records
} else {
    // Display a message if there are no student records
    echo "<p>No student records found!</p>";
}

// get previous page
$previous_page = $page_no - 1;
// get next page
$next_page = $page_no + 1;

//total records
$result_count = mysqli_query($con, "SELECT COUNT(*) as total_records FROM 
schooldb.students") or die(mysqli_error($con));

//store to variable
$records = mysqli_fetch_array($result_count);
$total_records=$records['total_records'];
//get total pages
$total_no_of_pages= ceil($total_records / $total_records_per_page);


//Another operation for summary get total student enrolled.
// SQL query to count the total number of students
$sql = "SELECT COUNT(accountID) AS total_students FROM students";

// Execute the query
$result = $con->query($sql);
// Check if the query returned any result
if ($result && $result->num_rows > 0) {
    // Fetch the result as an associative array
    $row = $result->fetch_assoc();
    
    // Get the total number of students
    $total_students = $row['total_students'];
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/dashboard.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Include Bootstrap CSS and JS (required for dropdown to work) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        
 
       .page-link{
            padding: 1px;
            color:  #0d6efd;
        }


  .pagination .page-link:hover {
    cursor: pointer;
    background-color: #0d6efd ; 
    color: #fff;/* Light hover effect */
  }

  .pagination .disabled .page-link {
    cursor: pointer;
    color: #fff; 
  } 

  .table>:not(caption)>*>* {
    padding: .5rem .5rem;
    color: var(--bs-table-color-state, var(--bs-table-color-type, var(--bs-table-color)));
    background-color: rgb(0 0 0 / 0%);
    border-bottom-width: var(--bs-border-width);
    box-shadow: inset 0 0 0 9999px var(--bs-table-bg-state, var(--bs-table-bg-type, var(--bs-table-accent-bg)));
}

.page-link {
    padding: 5px;
}

    </style>
</head>
<body>


 <div class="header">
        <div class="mainheader">
            <p>Web-based school fees <br>
            <span class="subheading">MANAGEMENT SYSTEM</span></p>
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
            <li><a href="studentinfo.php">Student Info</a></li>
            <li><a href="#">  Accounting</a></li>
            <li><a href="#" id="logoutLink">Logout</a></li>
        </ul>
    </div>


    <!-- Dashboard Section -->
<div class="dashboard" id="dashboard">
    <div class="dashboard-header text-left mb-4">
        <h3>Summary</h3>
    </div>
    <div class="dashboard-content d-flex justify-content-around flex-wrap">
        <div class="stat-card">
            <img src="img/students.png" alt="student" class="stat-icon">
            <h5>Total Students</h5>
            <p><?php echo  $total_students ?></p>
        </div>
        <div class="stat-card">
            <img src="img/earnings.png" alt="earnings" class="stat-icon">
            <h5>Total Earnings</h5>
            <p>₱0</p>
        </div>
        <div class="stat-card">
            <img src="img/validating-ticket.png" alt="paid" class="stat-icon">
            <h5>Paid</h5>
            <p>₱0</p>
        </div>
        <div class="stat-card">
            <img src="img/bill.png" alt="bill" class="stat-icon">
            <h5>Unpaid</h5>
            <p>₱10</p>
        </div>
    </div>
</div>


<div class="table">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
        <h3 class="text-left mb-1">Student Records</h3>
    </div>

    <!-- Search bar -->
    <div class="search-container d-flex align-items-center mb-3">
        <input type="search" placeholder="Search..." class="form-control" style="width: 200px;">
        <button class="btn btn-primary ms-2">Search</button>
    </div>

    <!-- Table content -->
    <div class="table-content" style="max-height: 400px;">
        <table class="table table-striped table-bordered table-hover table-sm text-left align-middle" style="min-width: 1000px;">
            <thead class="head-dark">
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Grade</th>
                    <th>Email</th>
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
                        <td><?php echo htmlspecialchars($r['gradelvl']); ?></td>
                        <td><?php echo htmlspecialchars($r['email']); ?></td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <a href="view.php"> <button class="btn btn-sm btn-primary me-2">View</button></a>
                               
                                <button class="btn btn-sm btn-info me-2">Update</button>
                                
                                <!-- Dropdown button for Enable/Disable -->
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle w-100" type="button" id="dropdownMenuButton_<?php echo $r['studentID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            Enable
                        </button>
                        <ul class="dropdown-menu w-100 p-0 m-0" aria-labelledby="dropdownMenuButton_<?php echo $r['studentID']; ?>" style="box-sizing: border-box;">
                            <li><a class="dropdown-item py-2" href="#" onclick="toggleDropdown('Disable', '<?php echo $r['studentID']; ?>')">Disable</a></li>
                            <li><a class="dropdown-item py-2" href="#" onclick="toggleDropdown('Enable  ', '<?php echo $r['studentID']; ?>')">Enable</a></li>
                        </ul>
                    </div>


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

<div class="main-content">
        <!-- Your main content goes here -->
       

    </div>


    <footer>
        <p>&copy; Gabila & Canaway 2024 Capstone. All rights reserved.</p>
    </footer>
</body>

<!-- JavaScript to toggle the dropdown -->
<script>
    function toggleDropdown(status, studentID) {
        const dropdownButton = document.getElementById('dropdownMenuButton_' + studentID);
        const dropdownMenu = dropdownButton.nextElementSibling;
        const activeOption = dropdownMenu.children[1]; // "Active" option
        const inactiveOption = dropdownMenu.children[0]; // "Inactive" option

        if (status === 'Disable') {
            dropdownButton.textContent = 'Disable';
            dropdownButton.classList.remove('btn-success');
            dropdownButton.classList.add('btn-secondary');
            activeOption.style.display = 'block';
            inactiveOption.style.display = 'none';
        } else {
            dropdownButton.textContent = 'Enable';
            dropdownButton.classList.remove('btn-secondary');
            dropdownButton.classList.add('btn-success');
            activeOption.style.display = 'none';
            inactiveOption.style.display = 'block';
        }
    }
</script>
</html>
