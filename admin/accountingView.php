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
                                    <a href="payment2.php?ID=<?php echo htmlspecialchars($row['accountID']); ?>" class="btn btn-sm btn-primary me-2">SOA</a>
                                    <a href="payment2.php?ID=<?php echo htmlspecialchars($row['accountID']); ?>" class="btn btn-sm btn-primary me-2">INVOICE</a>



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
    <p>&copy; Carlgeline Gabilla & Jessa Mae Canaway Capstone Project 2024. All rights reserved.</p>
</footer>

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to logout?</p>
        <button id="confirmLogout" class="close">Yes, Logout</button>
        <button id="cancelLogout" class="close">Cancel</button>
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
</script>
</body>
</html>
