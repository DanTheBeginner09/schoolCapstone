<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Get accountID from URL
if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Fetch existing data
    $stmt = $con->prepare("SELECT accountID, studentID, fname, lname, gender, age, grade, email, remainingbalance, totalTuition, status, school_year, firstQuarter, secondQuarter, thirdQuarter, fourthQuarter FROM students WHERE accountID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Invalid Account ID.");
}

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Handle form submission to update clearance status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-clearance'])) {
    // Escape the form data to prevent SQL injection
    $quarter1 = mysqli_real_escape_string($con, $_POST['quarter1']);
    $quarter2 = mysqli_real_escape_string($con, $_POST['quarter2']);
    $quarter3 = mysqli_real_escape_string($con, $_POST['quarter3']);
    $quarter4 = mysqli_real_escape_string($con, $_POST['quarter4']);

    // Update the clearance status in the database
    $updateQuery = "UPDATE students SET 
                    firstQuarter = ?, 
                    secondQuarter = ?, 
                    thirdQuarter = ?, 
                    fourthQuarter = ? 
                    WHERE accountID = ?";

    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("ssssi", $quarter1, $quarter2, $quarter3, $quarter4, $accountID);

    if ($stmt->execute()) {
        $successMessage = "Clearance status updated successfully.";
    } else {
        $errorMessage = "Error updating clearance status: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>

            .tagName {
        
            background-color: #3f51b5;
            color: #fff;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
        }

        .border{
            width: 100%;
            margin-top: 70px;
        }
   
        .container {
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
 
        

    
        

<div class="col-md-6">
   
    <h4 class="mt-3 personalinfo" style="text-transform: uppercase; font-size: 1.5rem;">Student Clearance</h4>

    form action="view.php?ID=<?php echo $accountID; ?>" method="POST">
    <div class="form-group">
        <label for="quarter1">First Quarter Clearance Status:</label>
        <input type="text" name="quarter1" id="quarter1" class="form-control" value="<?php echo isset($student['firstQuarter']) ? htmlspecialchars($student['firstQuarter']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="quarter2">Second Quarter Clearance Status:</label>
        <input type="text" name="quarter2" id="quarter2" class="form-control" value="<?php echo isset($student['secondQuarter']) ? htmlspecialchars($student['secondQuarter']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="quarter3">Third Quarter Clearance Status:</label>
        <input type="text" name="quarter3" id="quarter3" class="form-control" value="<?php echo isset($student['thirdQuarter']) ? htmlspecialchars($student['thirdQuarter']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="quarter4">Fourth Quarter Clearance Status:</label>
        <input type="text" name="quarter4" id="quarter4" class="form-control" value="<?php echo isset($student['fourthQuarter']) ? htmlspecialchars($student['fourthQuarter']) : ''; ?>" required>
    </div>

    <button type="submit" name="update-clearance" class="btn btn-primary">Update Clearance</button>
</form>
            <br>

            <a href="studentInfo.php" class="btn btn-light btn-sm">
    <i class="fas fa-arrow-left"></i> Back
</a>

</div>




        </div>
            



    </div>
</div>



        </div>



</div>

    

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
