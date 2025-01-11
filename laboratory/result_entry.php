<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Insert result entry
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patientName = $_POST['patientName'];
    $testName = $_POST['testName'];
    $result = $_POST['result'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    $sql = "INSERT INTO laboratory_results (patientName, testName, result, date, status) 
            VALUES ('$patientName', '$testName', '$result', '$date', '$status')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Result added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetching results
$sql = "SELECT * FROM laboratory_results";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Result Entry</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navbar inclusion -->
   

    <h1>Enter Laboratory Test Results</h1>

    <!-- Form to Enter Test Result -->
    <form method="POST" action="result_entry.php">
        <label for="patientName">Patient Name:</label>
        <input type="text" id="patientName" name="patientName" required><br><br>

        <label for="testName">Test Name:</label>
        <input type="text" id="testName" name="testName" required><br><br>

        <label for="result">Test Result:</label>
        <textarea id="result" name="result" required></textarea><br><br>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
            <option value="Reviewed">Reviewed</option>
        </select><br><br>

        <button type="submit">Submit Result</button>
        <button type="button" onclick="window.location.href='all_results.php'">See all results</button>

    </form>

</body>
</html>

<?php
mysqli_close($conn);
?>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

h1, h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

form {
    width: 80%;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input, textarea, select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

button {
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #0056b3;
}

table {
    width: 90%;
    margin: 30px auto;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: #fff;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
}

td {
    font-size: 14px;
    color: #555;
}

tr:hover {
    background-color: #f1f1f1;
}

</style>