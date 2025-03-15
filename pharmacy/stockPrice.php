<?php
// Start session
session_start();

// Check if the user is logged in (assuming pharmacist or admin access)
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
}

// Database credentials
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all medicines from the medicine_stock table
$sql = "SELECT medicine_name, quantity_sold, selling_price, cost_price 
        FROM medicines_sold 
        ORDER BY medicine_name ASC";
$result = $conn->query($sql);

// Check if the query executed successfully
if (!$result) {
    die("Error executing query: " . $conn->error);
}

$totalStockValue = 0; // Variable to store the total value of all medicines
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Price Report</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .table {
            margin-top: 20px;
        }

        .table thead {
            background-color: #3498db;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .total-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stock Price Report</h1>

        <!-- Medicines Stock Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Stock Quantity</th>
                        <th>Selling Price (per unit)</th>
                        <th>Cost Price (per unit)</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $totalValue = $row['quantity_sold'] * $row['cost_price']; // Calculate total value
                            $totalStockValue += $totalValue; // Add to total stock value

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['medicine_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity_sold']) . "</td>";
                            echo "<td>" . number_format($row['selling_price'], 2) . " Tsh</td>";
                            echo "<td>" . number_format($row['cost_price'], 2) . " Tsh</td>";
                            echo "<td>" . number_format($totalValue, 2) . " Tsh</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No medicines found in stock.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Total Stock Value -->
        <div class="total-value">
            Total Stock Value: <?= number_format($totalStockValue, 2) ?> Tsh
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>