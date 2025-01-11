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

// Fetch medicine order requests
$sql = "SELECT * FROM medicine_order_requests";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Order Requests</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Medicine Order Requests</h1>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Medicine Name</th>
                <th>Quantity</th>
                <th>Requested By</th>
                <th>Date Requested</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['orderID']}</td>
                        <td>{$row['medicineName']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['requestedBy']}</td>
                        <td>{$row['dateRequested']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

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

h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

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

/* Status styling */
td.status {
    font-weight: bold;
    color: #007bff;
}

td.status.Pending {
    color: orange;
}

td.status.Approved {
    color: green;
}

td.status.Rejected {
    color: red;
}

/* Responsive styles */
@media (max-width: 768px) {
    table, th, td {
        font-size: 12px;
    }

    h1 {
        font-size: 18px;
    }
}

</style>