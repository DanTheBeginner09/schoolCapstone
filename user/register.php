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
    $studentID = isset($_POST['idnumber']) ? trim($_POST['idnumber']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        // Check if ID exists in students table
        $studentCheckQuery = "SELECT * FROM `students` WHERE `studentID` = ?";
        $stmt = $con->prepare($studentCheckQuery);
        $stmt->bind_param("s", $studentID);
        $stmt->execute();
        $studentResult = $stmt->get_result();

        if ($studentResult->num_rows === 0) {
            $message = "Student ID does not exist in records. Please contact the administration.";
            $messageType = "error";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into users table
            $insertQuery = "INSERT INTO `users` (`id_number`, `password`) VALUES (?, ?)";
            $insertStmt = $con->prepare($insertQuery);
            $insertStmt->bind_param("ss", $studentID, $hashedPassword);

            if ($insertStmt->execute()) {
                $message = "Registration successful! You can now log in.";
                $messageType = "success";
            } else {
                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }

            $insertStmt->close();
        }

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
            <h3>Create Account</h3>
            <form action="" method="POST">
                <label>ID NUMBER</label>
                <input type="text" name="idnumber" class="username" placeholder="ID NUMBER" required>

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
                <?php echo $message; ?>
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
