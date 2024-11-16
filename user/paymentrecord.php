<?php
// Start the session at the very beginning
session_start();

// Include database connection file
include_once("dbconnection/connect.php");

// Establish database connection
$con = connection();

// Check if the user is logged in
if (!isset($_SESSION['id_number'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch the student information based on the logged-in user
$user_id = $_SESSION['id_number'];
$query = "SELECT * FROM students WHERE studentID = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any student data was found
if ($result->num_rows > 0) {
    // Fetch the student data
    $student = $result->fetch_assoc();
    ?>

   

    <?php
} else {
    echo "<p>No student found.</p>";
}

// Close the statement and connection
$stmt->close();
$con->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/style.css">

    <style>


    .paymentDashboard p:hover {
        cursor: pointer;
      
    }
  
 
    .studentdashboard {
    transition: margin-left 250ms ease-in-out, width 250ms ease-in-out;
    margin-top: 50px; /* Maintain your original margin */
    width: 100%; /* Ensure it takes the full width of the container */
    padding: 15px; /* Optional: Add padding for better spacing */
    box-sizing: border-box; /* Include padding in the width calculation */
            }

/* Style for paragraphs */
.studentdashboard p {
    
    margin: -2px -30px; /* Add vertical margin for paragraphs */
    font-size: 1.1rem; /* Slightly increase font size for readability */
}


.paymentdashboard p{
    font-size: 1.5rem;
}

table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}



/* Optional media query for smaller screens */
@media (max-width: 576px) {
    .studentdashboard {
        margin-top: 30px; /* Adjust margin for smaller screens */
    }
    
    .studentdashboard p {
        font-size: 1rem; /* Reduce font size for better fit on small screens */
    }
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
            <a href="dashboard.php"><h3>USER DASHBOARD</h3></a>
        </div>
        <ul class="menu">
            <li>
                
                <a href="paymentrecord.php"><img src="img/transaction.png" alt="transaction"><br> PAYMENT RECORDS</a>
            </li>
            <li><a href="requestsoa.php"><img class="img2" src="img/file 1.png" alt="transaction"> <br>REQUEST OF STATEMENT OF ACCOUNT</a></li>
           
                <li><a href="javascript:void(0);" id="logoutLink">Logout</a></li>
        </ul>
    </div>
    </div>

        
<!-- Main Content -->
<div class="main-content">
    <div class="studentdashboard">
        <p>Student: <strong><?php echo htmlspecialchars($student['fname']); ?> <?php echo htmlspecialchars($student['lname']); ?></strong></p>
        <p>ID Number: <strong><?php echo htmlspecialchars($student['studentID']); ?></strong></p>
    </div>

    <div class="paymentdashboard">
    
    <p>PAYMENT RECORDS</p>
    </div>

    <div class="transaction">
        

<table>
  <tr>
    <th>GRADING</th>
    <th>AMOUNT PER EXAM</th>
    <th>REMARKS</th>
  </tr>
  <tr>
    <td>1st GRADING</td>
    <td>php 2,500.00</td>
    <td>cleared</td>
  </tr>
  <tr>
    <td>2nd GRADING</td>
    <td>php 2,500.00</td>
    <td>cleared</td>
  </tr>
  <tr>
    <td>3rd GRADING</td>
    <td>php 1,500.00</td>
    <td>partial</td>
  </tr>
  <tr>
    <td>4th GRADING</td>
    <td>php 1,500.00</td>
    <td>partial</td>
  </tr>
</table>
</div>


</div>




 <!-- Footer -->
 <footer class="footer bg-dark text-light text-center py-2 mt-5">
        <p>&copy; Daniel C. Villanueva 2024 Capstone. All rights reserved.</p>
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
</html>
