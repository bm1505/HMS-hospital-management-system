<?php
// Start session
session_start();

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

// Fetch all medicines sold and calculate profit
$sql = "SELECT 
            medicine_id, 
            medicine_name, 
            SUM(quantity_sold) AS total_quantity_sold, 
            selling_price, 
            cost_price 
        FROM medicines_sold 
        GROUP BY medicine_id";
$result = $conn->query($sql);

$total_sales = 0;
$total_profit = 0;

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #e9f7ef;
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

        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profit Report</h1>

        <!-- Medicines Sold Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Medicine ID</th>
                        <th>Medicine Name</th>
                        <th>Quantity Sold</th>
                        <th>Selling Price (per unit)</th>
                        <th>Cost Price (per unit)</th>
                        <th>Total Sales</th>
                        <th>Total Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $total_sales_per_medicine = $row['total_quantity_sold'] * $row['selling_price'];
                            $total_cost_per_medicine = $row['total_quantity_sold'] * $row['cost_price'];
                            $profit_per_medicine = $total_sales_per_medicine - $total_cost_per_medicine;

                            $total_sales += $total_sales_per_medicine;
                            $total_profit += $profit_per_medicine;

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['medicine_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['medicine_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['total_quantity_sold']) . "</td>";
                            echo "<td>" . number_format($row['selling_price'], 2) . "</td>";
                            echo "<td>" . number_format($row['cost_price'], 2) . "</td>";
                            echo "<td>" . number_format($total_sales_per_medicine, 2) . "</td>";
                            echo "<td>" . number_format($profit_per_medicine, 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No medicines sold.</td></tr>";
                    }
                    ?>
                    <!-- Total Row -->
                    <tr class="total-row">
                        <td colspan="5"><strong>Total</strong></td>
                        <td><strong><?= number_format($total_sales, 2) ?></strong></td>
                        <td><strong><?= number_format($total_profit, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>