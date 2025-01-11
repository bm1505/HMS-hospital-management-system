<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all results from the database
$sql = "SELECT * FROM laboratory_results";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Test Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navbar inclusion -->
 

    <h2>All Test Results</h2>

    <table>
        <thead>
            <tr>
                <th>Result ID</th>
                <th>Patient Name</th>
                <th>Test Name</th>
                <th>Result</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through the results and display each row in the table
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['resultID']}</td>
                        <td>{$row['patientName']}</td>
                        <td>{$row['testName']}</td>
                        <td>{$row['result']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Generate Report Button -->
    <form action="generate_report.php" method="POST">
        <button type="submit" name="generate_report">Generate Report</button>
    </form>

</body>
</html>

<?php
// Close database connection
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

</style>