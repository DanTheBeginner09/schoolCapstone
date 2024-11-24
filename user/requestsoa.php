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

.paymentdashboard P{
    font-size: 1.5rem;
}


.close-btn {
    position: absolute;
    top: 30px;
    right: 30px;
    font-size: 20px;
    font-weight: bold; /* Makes the text bold */
    background-color: transparent;
    border: none;
    color: #808080; /* Gray color for the text */
    cursor: pointer;
    transition: all 0.3s ease; /* Smooth transition effect */
}

/* Optional: Change appearance on hover */
.close-btn:hover {
    color: #ff0000;  /* Change color to red when hovered */
    transform: scale(1.1);  /* Slightly enlarge the button */
}


 /* Container for the table */
 .transaction {
            position: relative;
          
        }


/* Style for success message */
.success-message {
    color: green;  /* Makes the text green */
    font-weight: bold; /* Optional: makes the text bold */
    background-color: #d4edda;  /* Optional: light green background for success message */
    padding: 10px;  /* Optional: add padding around the message */
    border: 1px solid #c3e6cb;  /* Optional: a border with a light green color */
    border-radius: 5px;  /* Optional: rounded corners */
    margin: 10px 0;  /* Optional: add margin for spacing */
}

/* Style for success message */
.error-message {
    color: red;  /* Makes the text green */
    font-weight: bold; /* Optional: makes the text bold */
    background-color: #d4edda;  /* Optional: light green background for success message */
    padding: 10px;  /* Optional: add padding around the message */
    border: 1px solid #c3e6cb;  /* Optional: a border with a light green color */
    border-radius: 5px;  /* Optional: rounded corners */
    margin: 10px 0;  /* Optional: add margin for spacing */
}



/* Optional media query for smaller screens */
@media (max-width: 576px) {
    .studentdashboard {
        margin-top: 30px; /* Adjust margin for smaller screens */
    }
    
    .studentdashboard p {
        font-size: 1rem; /* Reduce font size for better fit on small screens */
    }

    .transaction{
        height: 70%;
    }
}

.success-message, .error-message {
    transition: opacity 1s ease-out;
}

.success-message[style="display: none;"], .error-message[style="display: none;"] {
    opacity: 0;
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
    
    <p>TRANSACTION </p>
    </div>



<div class="transaction">
    

<div class="transaction-item">
        <!-- Close Button -->
        <button class="close-btn" onclick="closeTable()">X</button>

    <img src="img/reminders.png" alt="notify">
    
    <!-- Form to handle the request for Statement of Account -->
    <form action="soa_clicked.php" method="POST">
        <p>Click to Request for Statement of Account <br>
        It will notify the school admin to provide the statement of account</p>
        
        <!-- Button to trigger the request -->
        <button type="submit" name="request_soa" value="yes">Request</button>

        <!-- Hidden field to pass the studentID -->
        <input type="hidden" name="studentID" value="<?php echo $student['studentID']; ?>">
    </form>
</div>

<?php if (isset($_SESSION['successMessage'])): ?>
    <div class="success-message" id="successMessage">
        <?php echo $_SESSION['successMessage']; ?>
        <?php unset($_SESSION['successMessage']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['errorMessage'])): ?>
    <div class="error-message" id="errorMessage">
        <?php echo $_SESSION['errorMessage']; ?>
        <?php unset($_SESSION['errorMessage']); ?>
    </div>
<?php endif; ?>

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
<script>
// JavaScript to hide the message after 5 seconds
document.addEventListener("DOMContentLoaded", function() {
    // Check if success message exists and set the timeout to hide it
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.display = 'none';  // Hide the success message
        }, 5000);  // 5000ms = 5 seconds
    }

    // Check if error message exists and set the timeout to hide it
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        setTimeout(function() {
            errorMessage.style.display = 'none';  // Hide the error message
        }, 5000);  // 5000ms = 5 seconds
    }
});


    function closeTable() {
    // Hide the transaction table
    document.querySelector('.transaction').style.display = 'none';
    
    // Redirect to the dashboard page
    window.location.href = 'dashboard.php';
}
    

</script>
</html>
