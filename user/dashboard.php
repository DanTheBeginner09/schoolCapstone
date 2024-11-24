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


.periodRecord p {
    font-size: 1.5rem;  /* Adjust the font size here (increase or decrease as needed) */
    margin-top: 10px;   /* Adjust the space between paragraphs */
}

/* Optional: Styling for links (if needed) */
.periodRecord a {
    text-decoration: none;  /* Remove underline */
    color: inherit;         /* Ensure the color is consistent */
}

.main-content {
    flex-grow: 1; /* Allow content to grow and fill space */
    padding: 20px;
}

.footer {
    position: absolute;
    bottom: 0;
    width: 100%;
}

.footer p {
    font-size: 1rem; /* Adjust the font size as needed */
    margin: 0;       /* Remove margin */
}


/* Optional media query for smaller screens */
@media (max-width: 576px) {
    .studentdashboard {
        margin-top: 30px; /* Adjust margin for smaller screens */
    }
    
    .studentdashboard p {
        font-size: 1rem; /* Reduce font size for better fit on small screens */
    }

    .periodRecord p {
        font-size: 1.2rem;  /* Smaller font size for small screens */
        margin-top: 8px;    /* Slightly smaller margin */
    }
}


/* Media query for medium screens (tablets, small laptops) */
@media (max-width: 768px) {
    .periodRecord p {
        font-size: 1.3rem;  /* Slightly smaller font size for medium screens */
        margin-top: 9px;    /* Slightly smaller margin */
    }
}

/* Media query for large screens (larger laptops and desktops) */
@media (min-width: 992px) {
    .periodRecord p {
        font-size: 1.5rem;  /* Keep the default size for large screens */
        margin-top: 10px;   /* Default margin */
    }
}

/* Media query for large screens (larger laptops and desktops) */
@media (min-width: 400px) {
    .periodRecord p {
        font-size: 1.5rem;  /* Keep the default size for large screens */
        margin-top: 10px;   /* Default margin */
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
    
    <h3>REMAINING BALANCE/S</h3>
    </div>

    <div class="periods">
    <h4>Periods</h4>
</div>

<div class="periodRecord">
   
    <a href="history_4quarter.php"><p>2024-2024 Q4 - All Cleared</p></a>
    <a href="history_3quarter.php"><p>2024-2024 Q3 - All Cleared</p></a>
   <a href="history_2quarter.php"><p>2024-2024 Q2 - All Cleared</p></a> 
   <a href="history_1quarter.php"><p>2024-2024 Q1 - All Cleared</p></a> 
</div>



</div>




<!-- Footer -->
<footer class="footer bg-dark text-light text-center py-2 mt-5">
    <p>&copy; Carlgeline Gabila & Jessabel Canaway 2024 Capstone. All rights reserved.</p>
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
