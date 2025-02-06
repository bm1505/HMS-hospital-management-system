<?php
// Database connection details
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

// Create database connection
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to fetch all available lab tests from the catalog
$sql = "SELECT test_name, cost, preparation_instructions, turnaround_time FROM lab_test_catalog";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Test Catalog</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 2rem;
            background-color: #f5f5f5;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }

        /* Catalog Container */
        .catalog-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            overflow-x: auto;
        }

        /* Table Styles */
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .test-table th,
        .test-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .test-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .test-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .test-table tr:hover {
            background-color: #f1f8ff;
        }

        .cost {
            font-weight: bold;
            color: #27ae60;
        }

        .turnaround-time {
            color: #e67e22;
        }

        .preparation-instructions {
            max-width: 300px;
            white-space: normal;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .catalog-container {
                padding: 1rem;
            }

            .test-table th,
            .test-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="catalog-container">
        <h1>Lab Test Catalog</h1>
        <table class="test-table">
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th>Cost</th>
                    <th>Preparation Instructions</th>
                    <th>Turnaround Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['test_name']) . "</td>
                                <td class='cost'>Tsh" . number_format($row['cost'], 2) . "</td>
                                <td class='preparation-instructions'>" . htmlspecialchars($row['preparation_instructions']) . "</td>
                                <td class='turnaround-time'>" . htmlspecialchars($row['turnaround_time']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>No tests found in the catalog.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
// Close the database connection
mysqli_close($conn);
?>