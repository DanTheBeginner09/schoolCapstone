

<!DOCTYPE html>
<html lang="en">
<head><?php
include_once("dbconnection/connect.php");
$con = connection();

if (isset($_GET['ID']) && is_numeric($_GET['ID'])) {
    $accountID = $_GET['ID'];

    // Retrieve student SOA status based on accountID
    $sql = "SELECT * FROM students WHERE accountID = ?";
    $stmt = $con->prepare($sql);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("SQL prepare error: " . $con->error);
    }

    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Process the fetched data
      //  print_r($row);
    } else {
        echo "No data found for account ID $accountID.";
    }

    $stmt->close();
} else {
    echo "No valid account ID provided.";
}

$con->close();
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
<h1>On going feature...</h1>
</body>
</html>
