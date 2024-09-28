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
.table{
    margin-top:65px;
  
}

.table-content{
    margin-top: -50px;
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


<div class="table">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
      
        <div class="box-content top-box">
        <h3 class="personalinfo">Personal Details</h3>
                <p><strong>Name:</strong> John Doe</p>
                <p><strong>Date of Birth:</strong> January 1, 2000</p>
                <p><strong>Gender:</strong> Male</p>
                <p><strong>Address:</strong> 123 Main St, City, Country</p>
                <p><strong>Contact:</strong> (123) 456-7890</p>
            </div>

            <div class="box-content bottom-box">
                
            </div>
        </div>

        <!-- Optional Side Boxes -->
        <div class="side">
            <!-- Additional content can go here -->
        </div>

        <div class="side">
            <!-- Additional content can go here -->
        </div>

    </div>

   
   
</div>

<div class="main-content">
        <!-- Your main content goes here -->
       

    </div>


    <footer>
        <p>&copy; Gabila & Canaway 2024 Capstone. All rights reserved.</p>
    </footer>
</body>

</html>
