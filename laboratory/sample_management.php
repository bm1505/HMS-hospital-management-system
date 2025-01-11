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

// Insert a new sample if the form is submitted
if (isset($_POST['submit'])) {
    $sampleID = $_POST['sampleID'];
    $patientID = $_POST['patientID'];
    $sampleType = $_POST['sampleType'];
    $dateReceived = $_POST['dateReceived'];
    $status = $_POST['status'];

    // Check if the patientID exists in the patient table
    $checkPatientSql = "SELECT * FROM patient WHERE patientID = '$patientID'";
    $checkResult = mysqli_query($conn, $checkPatientSql);

    if (mysqli_num_rows($checkResult) > 0) {
        // Patient exists, proceed to insert the sample
        $insertSql = "INSERT INTO laboratory_samples (sampleID, patientID, sampleType, dateReceived, status)
                      VALUES ('$sampleID', '$patientID', '$sampleType', '$dateReceived', '$status')";

        if (mysqli_query($conn, $insertSql)) {
            echo "<script>alert('Sample added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        // Patient does not exist
        echo "<script>alert('Error: Patient ID does not exist.');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Laboratory Sample Management</h1>

    <!-- Add Sample Form -->
    <form action="sample_management.php" method="POST">
    <label for="sampleID">Sample ID:</label>
    <input type="text" id="sampleID" name="sampleID" required>

    <label for="patientID">Patient ID:</label>
    <input type="text" id="patientID" name="patientID" required>

    <label for="sampleType">Sample Type:</label>
    <input type="text" id="sampleType" name="sampleType" required>

    <label for="dateReceived">Date Received:</label>
    <input type="date" id="dateReceived" name="dateReceived" required>

    <label for="status">Status:</label>
    <select id="status" name="status" required>
        <option value="Pending">Pending</option>
        <option value="Analyzed">Analyzed</option>
        <option value="Completed">Completed</option>
    </select>

    <button type="submit" name="submit">Add Sample</button>

    <!-- View Samples Button -->
    <button type="button" onclick="window.location.href='view_samples.php';">View Samples</button>
</form>

    

</body>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-top: 30px;
    color: #333;
}

form {
    width: 50%;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

label {
    font-size: 14px;
    color: #555;
    display: block;
    margin-bottom: 8px;
}

input, select, button {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

button {
    background-color: #28a745;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background-color: #218838;
}

/* Table Styles */
table {
    width: 90%;
    margin: 0 auto;
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

/* Responsive Design */
@media (max-width: 768px) {
    form {
        width: 80%;
    }

    table {
        width: 100%;
    }

    h1 {
        font-size: 18px;
    }
}

</style>
</html>
