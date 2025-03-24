<?php
// equipment_maintenance.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch equipment maintenance records
$sql = "SELECT * FROM equipment_maintenance";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Maintenance Records</title>
    <link rel="stylesheet" href="styles.css">
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
        table {
            width: 80%;
            margin: 0 auto 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
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
        /* Action Button */
        .action-button {
            display: inline-block;
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .action-button:hover {
            background-color: #218838;
        }
        /* Button container styling */
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        button {
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            margin: 10px;
            font-weight: bold;
        }
        button:hover {
            opacity: 0.9;
        }
        button[type="button"]:nth-of-type(1) {
            background-color: #28a745;
            color: white;
        }
        button[type="button"]:nth-of-type(2) {
            background-color: #007bff;
            color: white;
        }
        button[type="button"]:nth-of-type(3) {
            background-color: #ffc107;
            color: white;
        }
        @media (max-width: 768px) {
            table, th, td {
                font-size: 12px;
            }
            h1 {
                font-size: 18px;
            }
            button {
                font-size: 14px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>

    <h1>Equipment Maintenance Records</h1>

    <table>
        <thead>
            <tr>
                <th>Record ID</th>
                <th>Equipment Name</th>
                <th>Maintenance Type</th>
                <th>Date of Service</th>
                <th>Next Calibration Date</th>
                <th>Technician</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['recordID']}</td>
                        <td>{$row['equipmentName']}</td>
                        <td>{$row['maintenanceType']}</td>
                        <td>{$row['serviceDate']}</td>
                        <td>{$row['nextCalibrationDate']}</td>
                        <td>{$row['technician']}</td>
                        <td class='status {$row['status']}'>{$row['status']}</td>";
                // Only show the action button if status is not Completed
                if ($row['status'] !== 'Completed') {
                    echo "<td><a href='update_status.php?recordID={$row['recordID']}' class='action-button'>Mark Completed</a></td>";
                } else {
                    echo "<td>--</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="button-container">
        <!-- Add Record button -->
        <button type="button" onclick="window.location.href='equipment_maintenance.php'">Add Record</button>
        
        <!-- View all records button -->
        <button type="button" onclick="window.location.href='view_record.php'">See All Maintenance Records</button>
        
        <!-- Generate Report button -->
        <button type="button" onclick="window.location.href='generate_maintenance_report.php'">Generate Report</button>
    </div>

</body>
</html>

<?php
mysqli_close($conn);
?>
