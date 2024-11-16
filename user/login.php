<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include_once("dbconnection/connect.php");
$con = connection();

// Message initialization
$message = "";

if (isset($_POST['login'])) {
    // Retrieve and sanitize input
    $studentID = isset($_POST['id_number']) ? mysqli_real_escape_string($con, trim($_POST['id_number'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Check if ID and password were provided
    if (empty($studentID) || empty($password)) {
        $message = "Please fill in both ID number and password.";
    } else {
        // Prepare the SQL query to fetch user by ID number
        $query = "SELECT * FROM `users` WHERE `id_number` = ?";
        $stmt = $con->prepare($query);

        // Check if the statement preparation failed
        if ($stmt === false) {
            die("Prepare failed: " . $con->error);
        }

        // Bind parameters and execute the statement
        $stmt->bind_param("s", $studentID);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        // Fetch result and check for ID number
        $result = $stmt->get_result();

        // Check if a user was found
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['id_number'] = $user['id_number'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = 'Invalid password. Please try again.';
            }
        } else {
            $message = 'ID number not found. Please check your credentials or register.';
        }

        // Close the statement
        $stmt->close();
    }
}
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header">
        <div class="mainheader">
            <p>Web-based school fees <br>
            <span class="subheading">MANAGEMENT SYSTEM</span></p>
        </div>
    </div>

<div class="wrapper">
    <div class="container">
        <h3>Login User</h3>
        <form action="" method="POST">
            <label>ID NUMBER</label>
            <input type="text" name="id_number" class="id_number" placeholder="ID NUMBER" required>

            <label>PASSWORD</label>
            <div class="password-container">
                <input type="password" name="password" class="password" id="password" placeholder="PASSWORD" required>
                <span class="toggle-password" id="togglePassword">&#128065;</span> <!-- Eye icon -->
            </div>

            <button type="submit" name="login" id="loginBtn">Login</button>
            <a href="register.php">Create Account</a>
        </form>
        
        <!-- Display login message if set -->
        <?php if (!empty($message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById("togglePassword").addEventListener("click", function() {
        const passwordField = document.getElementById("password");
        const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
        passwordField.setAttribute("type", type);
        this.innerHTML = type === "password" ? "👁️" : "👁️";
    });
</script>

</body>
</html>
