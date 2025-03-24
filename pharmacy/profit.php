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

// Store all rows for display
$medicines = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['total_sales'] = $row['total_quantity_sold'] * $row['selling_price'];
        $row['total_cost'] = $row['total_quantity_sold'] * $row['cost_price'];
        $row['profit'] = $row['total_sales'] - $row['total_cost'];

        $total_sales += $row['total_sales'];
        $total_profit += $row['profit'];

        $medicines[] = $row;
    }
}

// Close the database connection (now correctly placed after processing)
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
        .profit-positive {
            color: #28a745; /* Green for positive profit */
        }
        .profit-negative {
            color: #dc3545; /* Red for negative profit */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-chart-line"></i> Profit Report</h1>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Sales</h5>
                        <p class="card-text h4"><?= number_format($total_sales, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white <?= ($total_profit >= 0) ? 'bg-success' : 'bg-danger' ?> mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Profit</h5>
                        <p class="card-text h4"><?= number_format($total_profit, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medicines Sold Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Medicine ID</th>
                        <th>Medicine Name</th>
                        <th>Quantity Sold</th>
                        <th>Selling Price</th>
                        <th>Cost Price</th>
                        <th>Total Sales</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($medicines)): ?>
                        <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?= htmlspecialchars($medicine['medicine_id']) ?></td>
                                <td><?= htmlspecialchars($medicine['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($medicine['total_quantity_sold']) ?></td>
                                <td><?= number_format($medicine['selling_price'], 2) ?></td>
                                <td><?= number_format($medicine['cost_price'], 2) ?></td>
                                <td><?= number_format($medicine['total_sales'], 2) ?></td>
                               
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No medicines sold.</td></tr>
                    <?php endif; ?>
                    
                    <!-- Total Row -->
                    <tr class="total-row">
                        <td colspan="5"><strong>Grand Total</strong></td>
                        <td><strong><?= number_format($total_sales, 2) ?></strong></td>
                        
                            <strong><?= number_format($total_profit, 2) ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>