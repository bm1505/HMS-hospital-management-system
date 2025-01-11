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

// Fetch sample details
$sql = "SELECT * FROM laboratory_samples";
$result = mysqli_query($conn, $sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Samples</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Laboratory Sample Details</h1>

    <!-- Sample List Table -->
    <table>
        <thead>
            <tr>
                <th>Sample ID</th>
                <th>Patient ID</th>
                <th>Sample Type</th>
                <th>Date Received</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through each row and display it
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['sampleID']}</td>
                        <td>{$row['patientID']}</td>
                        <td>{$row['sampleType']}</td>
                        <td>{$row['dateReceived']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

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
