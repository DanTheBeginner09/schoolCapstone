<?php 
// Include your database connection
include_once("dbconnection/connect.php");
$con = connection();

// Check if the account ID is provided
if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Fetch SOA details from the database
    $sql = "SELECT * FROM students WHERE accountID = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $con->error);
    }
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_assoc();

    // Close the statement
    $stmt->close();
} else {
    $students = null; // No data found
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOA Status</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* General Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            margin-bottom: 25px;
            font-size: 28px;
            color: #333;
            font-weight: 600;
        }

        p {
            margin-bottom: 12px;
            font-size: 16px;
            color: #555;
        }

        .soa-details {
            text-align: left;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f4f4f4;
            border-radius: 8px;
        }

        .soa-details p {
            font-size: 16px;
            margin-bottom: 12px;
        }

        /* Dropdown Styling */
        .status-select {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fafafa;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            margin-top: 15px;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        /* Link Button Styles */
        a.btn {
            padding: 12px 20px;
            display: inline-block;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            text-align: center;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        a.btn-back {
            background-color: #6c757d;
        }

        a.btn-back:hover {
            background-color: #5a6268;
        }

        /* Form Element Spacing */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                max-width: 90%;
            }

            h1 {
                font-size: 24px;
            }

            .btn {
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SOA Status</h1>
        <?php if ($students): ?>
            <div class="soa-details">
                <p><strong>Account ID:</strong> <?php echo htmlspecialchars($students['accountID']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($students['fname']); ?>  <?php echo htmlspecialchars($students['lname']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($students['status']); ?></p>
                <p><strong>Remaining Balance:</strong> <?php echo htmlspecialchars($students['remainingbalance']); ?></p>
                <p><strong>Request for SOA:</strong> <?php echo htmlspecialchars($students['soa']); ?></p>
               
                <p><strong>Statement of Account Status:</strong> <?php echo htmlspecialchars($students['soa']); ?></p>
            </div>
            <!-- Status Update Form -->
            <form method="post" action="update_status.php?ID=<?php echo $students['accountID']; ?>">
                <input type="hidden" name="accountID" value="<?php echo htmlspecialchars($students['accountID']); ?>">
                <div class="form-group">
                    <label for="soa">Update SOA Status:</label>
                    <select name="soa" id="soa" class="status-select">
    <option value="" <?php echo ($students['soa'] == '') ? 'selected' : ''; ?>> </option>
    <option value="processing" <?php echo ($students['soa'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
    <option value="ready_to_release" <?php echo ($students['soa'] == 'ready_to_release') ? 'selected' : ''; ?>>Ready to Release</option>
    <option value="claimed" <?php echo ($students['soa'] == 'claimed') ? 'selected' : ''; ?>>Claimed</option>
</select>

                </div>
                <button type="submit" class="btn">Update SOA Status</button>
            </form>
        <?php else: ?>
            <p>No SOA found for the provided Account ID.</p>
        <?php endif; ?>
        <a href="javascript:history.back()" class="btn btn-back">Return</a>
        <a href="dashboard.php" class="btn btn-back">Back to Dashboard</a>
    </div>
</body>
</html>
