<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

$student = null;

// Fetch student details if search query exists
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $query = "SELECT * FROM students WHERE studentID = '$search' OR fname LIKE '%$search%' OR lname LIKE '%$search%' LIMIT 1";
    $result = mysqli_query($con, $query);
    $student = mysqli_fetch_assoc($result);
}

// Handle form submission to update clearance status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($student['accountID'])) {
    $accountID = $student['accountID'];
    $quarter1 = mysqli_real_escape_string($con, $_POST['quarter1']);
    $quarter2 = mysqli_real_escape_string($con, $_POST['quarter2']);
    $quarter3 = mysqli_real_escape_string($con, $_POST['quarter3']);
    $quarter4 = mysqli_real_escape_string($con, $_POST['quarter4']);

    $updateQuery = "UPDATE students SET 
                    firstQuarter = '$quarter1', 
                    secondQuarter = '$quarter2', 
                    thirdQuarter = '$quarter3', 
                    fourthQuarter = '$quarter4'
                    WHERE accountID = '$accountID'";
    if (mysqli_query($con, $updateQuery)) {
        echo "<script>alert('Clearance status updated successfully.');</script>";
    } else {
        echo "<script>alert('Update failed: " . mysqli_error($con) . "');</script>";
    }
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

<div class="border">
      
      
        

    
        <div class="container">
        <div class="container table-responsive">

        <div class="tagName">Display Information</div>
                      

        <div class="row mb-4">
        <div class="col-md-6">
            
    <h4 class="mt-3" style="text-transform: uppercase; font-size: 1.5rem;">Student Details</h4>

    <div class="personal-details bg-light p-3 rounded">
        <div class="row">
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">ID Number:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['studentID']) ? htmlspecialchars($student['studentID']) : 'Not available'; ?></span>
            </div>
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">SY:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['school_year']) ? htmlspecialchars($student['school_year']) : 'Not available'; ?></span>
            </div>
           
        </div>
        <div class="row">
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">First Name:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['fname']) ? htmlspecialchars($student['fname']) : 'Not available'; ?></span>
            </div>
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Last Name:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['lname']) ? htmlspecialchars($student['lname']) : 'Not available'; ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Gender:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['gender']) ? htmlspecialchars($student['gender']) : 'Not available'; ?></span>
            </div>
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Age:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['age']) ? htmlspecialchars($student['age']) : 'Not available'; ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Grade:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['grade']) ? htmlspecialchars($student['grade']) : 'Not available'; ?></span>
            </div>
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Email:</strong> 
                <span style="font-size: 1rem;"><?php echo isset($student['email']) ? htmlspecialchars($student['email']) : 'Not available'; ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Status:</strong> 
                <span class="status-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['status']) ? htmlspecialchars($student['status']) : 'Not available'; ?></span>
            </div>
            <div class="col">
                <strong style="text-transform: uppercase; font-size: 1rem;">Remaining Balance:</strong> 
                <span class="quater-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['remainingbalance']) : 'Not available'; ?></span>
            </div>
        </div>

  
    </div>
        
    

</div>


            <div class="col-md-6">
    <h4 class="mt-3 personalinfo" style="text-transform: uppercase; font-size: 1.5rem;">Student Clearance</h4>

    <div class="row mt-3">
        <div class="col">
            <strong style="text-transform: uppercase; font-size: 1rem;">1st Quarter:</strong> 
            <span class="quater-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['firstQuarter']) : 'Not available'; ?></span>
        </div>
        <div class="col">
            <strong style="text-transform: uppercase; font-size: 1rem;">2nd Quarter:</strong> 
            <span class="quater-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['secondQuarter']) : 'Not available'; ?></span>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <strong style="text-transform: uppercase; font-size: 1rem;">3rd Quarter:</strong> 
            <span class="quater-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['thirdQuarter']) : 'Not available'; ?></span>
        </div>
        <div class="col">
            <strong style="text-transform: uppercase; font-size: 1rem;">4th Quarter:</strong> 
            <span class="quater-button badge bg-success" style="font-size: 1rem;"><?php echo isset($student['remainingbalance']) ? htmlspecialchars($student['fourthQuarter']) : 'Not available'; ?></span>
        </div>
    </div>

    

</div>

<div class="text-center">
    <a href="studentInfo.php" class="btn btn-light btn-sm mx-3 my-2 px-4 py-2">
        <i class="fas fa-arrow-left"></i> Back
    </a>
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
