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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipmentName = $_POST['equipmentName'];
    $maintenanceType = $_POST['maintenanceType'];
    $serviceDate = $_POST['serviceDate'];
    $nextCalibrationDate = $_POST['nextCalibrationDate'];
    $technician = $_POST['technician'];
    $status = $_POST['status'];

    // Insert query
    $sql = "INSERT INTO equipment_maintenance (equipmentName, maintenanceType, serviceDate, nextCalibrationDate, technician, status)
            VALUES ('$equipmentName', '$maintenanceType', '$serviceDate', '$nextCalibrationDate', '$technician', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Record added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment Maintenance Record</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Add New Equipment Maintenance Record</h1>

    <form method="POST" action="equipment_maintenance.php">
        <label for="equipmentName">Equipment Name:</label>
        <input type="text" id="equipmentName" name="equipmentName" required><br><br>

        <label for="maintenanceType">Maintenance Type:</label>
        <select id="maintenanceType" name="maintenanceType" required>
            <option value="Routine">Routine</option>
            <option value="Calibration">Calibration</option>
            <option value="Repair">Repair</option>
        </select><br><br>

        <label for="serviceDate">Service Date:</label>
        <input type="date" id="serviceDate" name="serviceDate" required><br><br>

        <label for="nextCalibrationDate">Next Calibration Date:</label>
        <input type="date" id="nextCalibrationDate" name="nextCalibrationDate" required><br><br>

        <label for="technician">Technician Name:</label>
        <input type="text" id="technician" name="technician" required><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Scheduled">Scheduled</option>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
        </select><br>
        <button type="submit">Add Record</button> <button type="submit" onclick="window.location.href='view_record.php'; return false;">See all maintenance record</button>
        <style>
            /* General styling for buttons */
button {
    padding: 12px 25px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    margin-top: 20px;
    font-weight: bold;
}

/* Style for submit button (Add Record) */
button[type="submit"]:first-of-type {
    background-color: #28a745; /* Green background */
    color: white;
}

button[type="submit"]:first-of-type:hover {
    background-color: #218838; /* Darker green on hover */
}

/* Style for the "See all results" button */
button[type="submit"]:last-of-type {
    background-color: #007bff; /* Blue background */
    color: white;
    border: 2px solid #007bff; /* Blue border */
}

button[type="submit"]:last-of-type:hover {
    background-color: #0056b3; /* Darker blue on hover */
    border-color: #0056b3; /* Darker blue border */
}

button:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); /* Blue glow effect on focus */
}

/* Responsive styling for small screens */
@media (max-width: 768px) {
    button {
        font-size: 14px;
        padding: 10px 20px;
    }
}

        </style>
    </form>

</body>
</html>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

form, table {
    width: 80%;
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

/* Table styling */
table {
    border-collapse: collapse;
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

/* Status styling */
td.status {
    font-weight: bold;
}

td.status.Scheduled {
    color: orange;
}

td.status.Completed {
    color: green;
}

td.status.Pending {
    color: red;
}

/* Responsive styling */
@media (max-width: 768px) {
    table, th, td {
        font-size: 12px;
    }

    h1 {
        font-size: 18px;
    }
}

/* Form styling */
form input, form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

form input[type="submit"] {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}

form input[type="submit"]:hover {
    background-color: #0056b3;
}

</style>