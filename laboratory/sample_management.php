<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve patientID from session or GET request
$patientID = $_SESSION['patientID'] ?? $_GET['patientID'] ?? '';

$patientName = "";
if (!empty($patientID)) {
    $sql = "SELECT first_name, last_name FROM patients WHERE patientID = '$patientID'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $patientName = $row['first_name'] . " " . $row['last_name'];
    } else {
        $patientID = ''; // Reset if invalid
    }
}

// Handle sample submission
if (isset($_POST['submit'])) {
    $sampleID = $_POST['sampleID'];
    $sampleType = $_POST['sampleType'];
    $dateReceived = $_POST['dateReceived'];
    $status = $_POST['status'];
    $collectedBy = $_POST['collectedBy'];
    $dateCollected = $_POST['dateCollected'];
    $labeledBy = $_POST['labeledBy'];
    $trackingNumber = $_POST['trackingNumber'];

    // Ensure patient exists
    if (!empty($patientID)) {
        $insertSql = "INSERT INTO laboratory_samples (sampleID, patientID, sampleType, dateReceived, status, collectedBy, dateCollected, labeledBy, trackingNumber)
                      VALUES ('$sampleID', '$patientID', '$sampleType', '$dateReceived', '$status', '$collectedBy', '$dateCollected', '$labeledBy', '$trackingNumber')";

        if (mysqli_query($conn, $insertSql)) {
            echo "<script>alert('Sample added successfully!'); window.location='sample_management.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Error: Invalid Patient ID.');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Sample Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Laboratory Sample Management</h1>
    
    <!-- Add Sample Form -->
    <form action="sample_management.php" method="POST">
        <label for="sampleID">Sample ID:</label>
        <input type="text" id="sampleID" name="sampleID" required>

        <label for="patientID">Patient ID:</label>
        <input type="text" id="patientID" name="patientID" value="<?= $patientID ?>" readonly>

        <label for="patientName">Patient Name:</label>
        <input type="text" id="patientName" name="patientName" value="<?= $patientName ?>" readonly>

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

        <label for="collectedBy">Collected By:</label>
        <input type="text" id="collectedBy" name="collectedBy">

        <label for="dateCollected">Date Collected:</label>
        <input type="date" id="dateCollected" name="dateCollected">

        <label for="labeledBy">Labeled By:</label>
        <input type="text" id="labeledBy" name="labeledBy">

        <label for="trackingNumber">Tracking Number:</label>
        <input type="text" id="trackingNumber" name="trackingNumber" required>

        <button type="submit" name="submit">Add Sample</button>
    </form>
</body>
</html>

<style>
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
</style>
