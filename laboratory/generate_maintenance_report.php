<?php
// report.php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch equipment maintenance records
$sql = "SELECT * FROM equipment_maintenance";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Equipment Maintenance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-button {
            display: block;
            width: 150px;
            margin: 0 auto 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #cccccc;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #dddddd;
        }
        /* For printing the report nicely */
        @media print {
            body {
                margin: 0;
            }
            .print-button {
                display: none;
            }
            table {
                page-break-after: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            td {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <h1>Equipment Maintenance Report</h1>
    <button class="print-button" onclick="window.print()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Record ID</th>
                <th>Equipment Name</th>
                <th>Maintenance Type</th>
                <th>Service Date</th>
                <th>Next Calibration Date</th>
                <th>Technician</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['recordID']); ?></td>
                    <td><?php echo htmlspecialchars($row['equipmentName']); ?></td>
                    <td><?php echo htmlspecialchars($row['maintenanceType']); ?></td>
                    <td><?php echo htmlspecialchars($row['serviceDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['nextCalibrationDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['technician']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>
