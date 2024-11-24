<?php
// Start the session at the very beginning
session_start();

include_once("dbconnection/connect.php");
$con = connection(); // Establish database connection

// Initialize messages
$errorMessage = "";
$successMessage = "";

// Initialize search and message variables
$searchTerm = "";
$searchResults = [];

// Check if there is a search query
if (isset($_GET['search'])) {
    $searchTerm = $con->real_escape_string($_GET['search']);

    // Prepare a search query with placeholders
    $sql_search = "SELECT * FROM students 
                   WHERE fname LIKE ? OR lname LIKE ? OR studentID LIKE ? 
                   ORDER BY studentID DESC";
    $stmt_search = $con->prepare($sql_search);

    // Add wildcards to search term for partial matches
    $searchWildcard = "%{$searchTerm}%";
    $stmt_search->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);

    // Execute the search query
    $stmt_search->execute();
    $searchResults = $stmt_search->get_result();
    
    // Check if no results found
    if ($searchResults->num_rows == 0) {
        $errorMessage = "No students found matching the search term.";
    }

    // Close the prepared statement
    $stmt_search->close();
}

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
        /* Styles go here */
        .sidebar { position: fixed; z-index: 2; transition: transform 0.3s ease; }
        .main-content { position: relative; z-index: 1; }
        .tagName { background-color: red; color: #fff; padding: 10px; font-size: 18px; font-weight: bold; }
        .container { width: 100%; background-color: rgba(255, 255, 255, 0.8); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border-radius: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 18px; text-align: center; margin: 20px 0; }
        th, td { padding: 5px; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }

       /* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* The Close Button */
.closeX {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 20px;
    cursor: pointer;
}

.closeX:hover,
.closeX:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Style the status buttons */
.status-buttons {
    margin-top: 20px;
}

/* Custom styles for the status buttons */
.status-btn {
    width: 100%;
    margin: 5px 0;
}

/* Text for the current status */
#statusText {
    font-size: 18px;
    font-weight: bold;
    color: #333;
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
        <li><a href="studentInfo.php">Student Info</a></li>
        <li><a href="accounting.php">Accounting</a></li>
        <li><a href="javascript:void(0);" id="logoutLink">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <div class="search-block text-center my-4">
            <h4 class="mb-3">ACCOUNTING DEPARTMENT</h4>
           
        </div>

        <div class="container table-responsive">
            <div class="tagName">Display Information</div>
            <div class="row mb-4">
                <!-- Display search results -->
                <div class="search-results">
                    <?php if ($searchTerm && $searchResults->num_rows > 0): ?>
                        <h3>Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h3>
                        <table>
                            <tr>
                                <th>Student ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Grade</th>
                                <th>School Year</th>
                                <th>Transaction</th>
                               
                            </tr>
                            <?php while ($row = $searchResults->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['studentID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['grade']); ?></td>
                                    <td><?php echo htmlspecialchars($row['school_year']); ?></td>
                                    <td> 
                                    <a href="payment2.php?ID=<?php echo htmlspecialchars($row['accountID']); ?>" class="btn btn-sm btn-primary me-2">UPDATE AMOUNT</a>
                                    <!-- <a id="openModalBtn" class="btn btn-sm btn-primary me-2">Check SOA Status</a> -->
                                    <a href="check_soa_status.php?ID=<?php echo htmlspecialchars($row['accountID']); ?>" class="btn btn-sm btn-primary me-2">Check SOA Status</a>
                                    <a href="javascript-print/invoice.php?ID=<?php echo htmlspecialchars($row['accountID']); ?>" class="btn btn-sm btn-primary me-2">INVOICE</a>
                                    <a href="#" class="btn btn-sm btn-primary me-2">Email Billing Notify</a>

                                  

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php elseif ($searchTerm): ?>
                        <p><?php echo $errorMessage; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer bg-dark text-light text-center py-2 mt-5">
    <p>&copy; Carlgeline Gabilla & Jessabel Canaway Capstone Project 2024. All rights reserved.</p>
</footer>

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to logout?</p>
        <button id="confirmLogout" class="close">Yes, Logout</button>
        <button id="cancelLogout" class="close">Cancel</button>
    </div>
</div>

<!-- Modal Structure -->
<div id="soaModal" class="modal">
    <div class="modal-content">
        <span class="closeX">&times;</span>
        <h2>SOA Status</h2>
        <p id="statusText">Status: Processing</p>
        <div class="status-buttons">
            <button class="status-btn btn btn-outline-primary" data-status="Processing">Processing</button>
            <button class="status-btn btn btn-outline-primary" data-status="Ready to Release">Ready to Release</button>
            <button class="status-btn btn btn-outline-primary" data-status="Claimed">Claimed</button>
        </div>
    </div>
</div>

<script src="javascript/script.js"></script>
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var errorMessage = document.getElementById('errorMessage');
        var successMessage = document.getElementById('successMessage');
        if (errorMessage || successMessage) {
            setTimeout(function() {
                if (errorMessage) errorMessage.style.display = 'none';
                if (successMessage) successMessage.style.display = 'none';
            }, 3000);
        }
    });


 // Wait for the DOM to fully load before adding event listeners
document.addEventListener('DOMContentLoaded', function () {
    // Get the modal, button references, and elements
    var modal = document.getElementById("soaModal");
    var openModalBtn = document.getElementById("openModalBtn");
    var closeBtn = document.getElementsByClassName("closeX")[0]; // Correcting for multiple close buttons
    var statusText = document.getElementById("statusText");
    var statusButtons = document.querySelectorAll(".status-btn");

    // Open the modal when the button is clicked
    openModalBtn.onclick = function() {
        modal.style.display = "block";
    }

    // Close the modal when the "x" button is clicked
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal if the user clicks outside the modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Update the status when a button is clicked
    statusButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            var newStatus = button.getAttribute("data-status");
            statusText.innerText = "Status: " + newStatus;  // Update the status text
        });
    });
});

</script>
</body>
</html>
