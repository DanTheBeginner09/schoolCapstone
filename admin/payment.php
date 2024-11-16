<?php
session_start();
include_once("dbconnection/connect.php");
$con = connection();

// Fetch all student IDs and names for the dropdown
$studentResult = mysqli_query($con, "SELECT studentID, fname, lname FROM students");
if (!$studentResult) {
    die("Error fetching students: " . mysqli_error($con));
}

// Adding a new payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_payment'])) {
    $studentID = $_POST['studentID'];
    $amount = $_POST['amount'];
    $paymentDate = $_POST['paymentDate'];
    $paymentMethod = $_POST['paymentMethod'];
    $referenceNumber = $_POST['referenceNumber'];
    $description = $_POST['description'];

    // Retrieve accountID based on studentID
    $studentQuery = "SELECT accountID FROM students WHERE studentID = ?";
    $stmt = mysqli_prepare($con, $studentQuery);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $studentID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $accountID);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($accountID) {
            // SQL query to insert a new payment
            $query = "INSERT INTO payment (accountID, amount, paymentDate, paymentMethod, referenceNumber, description) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "idssss", $accountID, $amount, $paymentDate, $paymentMethod, $referenceNumber, $description);

                if (mysqli_stmt_execute($stmt)) {
                    echo "Payment added successfully.";
                } else {
                    echo "Error: " . mysqli_error($con);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Error preparing statement: " . mysqli_error($con);
            }
        } else {
            echo "Error: Student ID not found.";
        }
    } else {
        echo "Error preparing statement: " . mysqli_error($con);
    }
}

// Function to retrieve all payments
function getAllPayments($con) {
    $query = "SELECT * FROM payment";
    $result = mysqli_query($con, $query);
    if (!$result) {
        die("Error fetching payments: " . mysqli_error($con));
    }
    return $result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }

        /* Form styles */
        form {
            max-width: 600px;
            width: 100%;
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        select, input[type="text"], input[type="number"], input[type="date"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            margin-top: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f1f1;
        }

        /* Action form styles */
        .action-form {
            display: inline-block;
            min-width: 200px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            form {
                width: 90%;
            }
            table {
                font-size: 14px;
            }
            input[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h2>Add Payment</h2>
    <form method="POST" action="">
        <label for="studentID">Student ID:</label>
        <select name="studentID" required>
            <option value="">Select Student</option>
            <?php while ($student = mysqli_fetch_assoc($studentResult)) { ?>
                <option value="<?php echo $student['studentID']; ?>">
                    <?php echo $student['studentID'] . " - " . $student['fname'] . " " . $student['lname']; ?>
                </option>
            <?php } ?>
        </select>
        
        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        
        <label for="paymentDate">Payment Date:</label>
        <input type="date" name="paymentDate" required>
        
        <label for="paymentMethod">Payment Method:</label>
        <select name="paymentMethod">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="online">Online</option>
        </select>
        
        <label for="referenceNumber">Reference Number:</label>
        <input type="text" name="referenceNumber">
        
        <label for="description">Description:</label>
        <textarea name="description"></textarea>
        
        <input type="submit" name="add_payment" value="Add Payment">
    </form>

    <h2>Payment Records</h2>
    <table>
        <tr>
            <th>Payment ID</th>
            <th>Student ID</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Method</th>
            <th>Status</th>
            <th>Reference Number</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php
        $payments = getAllPayments($con);
        while ($payment = mysqli_fetch_assoc($payments)) {
            echo "<tr>
                    <td>{$payment['paymentID']}</td>
                    <td>{$payment['accountID']}</td>
                    <td>{$payment['amount']}</td>
                    <td>{$payment['paymentDate']}</td>
                    <td>{$payment['paymentMethod']}</td>
                    <td>{$payment['status']}</td>
                    <td>{$payment['referenceNumber']}</td>
                    <td>{$payment['description']}</td>
                    <td>
                        <form method='POST' action='' class='action-form'>
                            <input type='hidden' name='paymentID' value='{$payment['paymentID']}'>
                            <select name='status'>
                                <option value='completed' " . ($payment['status'] == 'completed' ? "selected" : "") . ">Completed</option>
                                <option value='pending' " . ($payment['status'] == 'pending' ? "selected" : "") . ">Pending</option>
                                <option value='failed' " . ($payment['status'] == 'failed' ? "selected" : "") . ">Failed</option>
                            </select>
                            <input type='submit' name='update_status' value='Update Status'>
                        </form>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
