<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("dbconnection/connect.php");
$con = connection();

$message = "";



if (isset($_POST['login'])) {
    // Sanitize and trim input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Query to check for user credentials
    $query = "SELECT * FROM `admin_user` WHERE `id_number` = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['password'];

        // Verify the password with stored hash
        if (password_verify($password, $stored_hash)) {
            $_SESSION['id_number'] = $user['id_number'];  // Start user session
            header("Location: dashboard.php");  // Redirect to dashboard
            exit();
        } else {
            $message = 'Invalid password. Please try again.';
        }
    } else {
        $message = 'Username not found. Please check your credentials or register.';
    }

    $stmt->close();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            <h3>Login Admistrator</h3>
            <form action="" method="POST">
                <label>USER NAME</label>
                <input type="text" name="username" class="username" placeholder="USER NAME" required>

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
</body>

<script>
    document.getElementById("togglePassword").addEventListener("click", function() {
        const passwordField = document.getElementById("password");
        const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
        passwordField.setAttribute("type", type);
        this.innerHTML = type === "password" ? "&#128065;" : "👁️";
    });
</script>
</html>
