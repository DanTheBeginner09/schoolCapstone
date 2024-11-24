<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include_once("dbconnection/connect.php");
$con = connection();

$message = "";
$messageType = ""; // Success or error indicator for modal

if (isset($_POST['register'])) {
    // Retrieve and sanitize input
    $fname = isset($_POST['fname']) ? trim($_POST['fname']) : ''; // Full name
    $username = isset($_POST['username']) ? trim($_POST['username']) : ''; // Username
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into admin_user table
        $insertQuery = "INSERT INTO `admin_user` (`fname`, `id_number`, `password`) VALUES (?, ?, ?)";
        $insertStmt = $con->prepare($insertQuery);

        // Ensure the statement preparation succeeded
        if ($insertStmt === false) {
            $message = "Database preparation failed. Please try again.";
            $messageType = "error";
        } else {
            // Bind full name, username, and hashed password
            $insertStmt->bind_param("sss", $fname, $username, $hashedPassword);

            // Execute the statement and check for success
            if ($insertStmt->execute()) {
                $message = "Registration successful! You can now log in.";
                $messageType = "success";
            } else {
                $message = "Registration failed. Please try again. Error: " . $insertStmt->error;
                $messageType = "error";
            }

            $insertStmt->close();
        }
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
    <link rel="stylesheet" href="css/style1.css">
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
            <h3>Create Account</h3>
            <form action="" method="POST">
                <label>FULL NAME</label>
                <input type="text" name="fname" class="username" placeholder="FULL NAME" required>

                <label>USER NAME</label>
                <input type="text" name="username" class="username" placeholder="USER NAME" required>

                <label>PASSWORD</label>
                <div class="password-container">
                    <input type="password" name="password" class="password" id="password" placeholder="PASSWORD" required>
                    <span class="toggle-password" id="togglePassword">&#128065;</span>
                </div>

                <label>CONFIRM PASSWORD</label>
                <div class="password-container">
                    <input type="password" name="confirm_password" class="password" id="confirmPassword" placeholder="CONFIRM PASSWORD" required>
                    <span class="toggle-password" id="toggleConfirmPassword">&#128065;</span>
                </div>

                <button type="submit" name="register" id="registerBtn">Register</button>
                <a href="login.php">Already have an account? Login</a>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal" style="<?php echo $message ? 'display: block;' : 'display: none;'; ?>">
        <div class="modal-content">
            <p id="modalMessage" class="<?php echo ($messageType === 'success') ? 'modal-success' : 'modal-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <button class="close" id="closeModal">Okay</button>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const passwordInput = document.querySelector('#password');
        const confirmPasswordInput = document.querySelector('#confirmPassword');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '&#128065;' : '&#128066;';
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '&#128065;' : '&#128066;';
        });

        const modal = document.getElementById('myModal');
        const closeModal = document.getElementById('closeModal');

        closeModal.onclick = function() {
            modal.style.display = 'none';
            if ('<?php echo $messageType; ?>' === 'success') {
                window.location.href = 'login.php';
            }
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    });
    </script>
</body>
</html>
