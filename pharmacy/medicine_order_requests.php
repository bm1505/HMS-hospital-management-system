<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create medicines table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS medicines (
    medicineID INT AUTO_INCREMENT PRIMARY KEY,
    medicineName VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    reorderLevel INT NOT NULL,
    unitPrice DECIMAL(10, 2) NOT NULL,
    supplier VARCHAR(255) NOT NULL
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating medicines table: " . mysqli_error($conn));
}

// Create medicine_order_requests table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS medicine_order_requests (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    medicineID INT NOT NULL,
    medicineName VARCHAR(255) NOT NULL,
    quantityRequested INT NOT NULL,
    requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    FOREIGN KEY (medicineID) REFERENCES medicines(medicineID)
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating medicine_order_requests table: " . mysqli_error($conn));
}

// Function to fetch all medicines
function getMedicines($conn) {
    $sql = "SELECT * FROM medicines";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        die("Error executing query: " . mysqli_error($conn));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to reorder medicines with low stock
function reorderLowStockMedicines($conn) {
    // Fetch all medicines
    $medicines = getMedicines($conn);

    // Loop through each medicine
    foreach ($medicines as $medicine) {
        $medicineID = $medicine['medicineID'];
        $medicineName = $medicine['medicineName'];
        $quantity = $medicine['quantity'];
        $reorderLevel = $medicine['reorderLevel'];

        // Check if the quantity is below the reorder level
        if ($quantity < $reorderLevel) {
            // Calculate the quantity to reorder (e.g., reorder up to 2x the reorder level)
            $quantityRequested = $reorderLevel * 2 - $quantity;

            // Insert reorder request into the medicine_order_requests table
            $sql = "INSERT INTO medicine_order_requests (medicineID, medicineName, quantityRequested) 
                    VALUES ('$medicineID', '$medicineName', '$quantityRequested')";

            if (!mysqli_query($conn, $sql)) {
                echo "<script>alert('Error inserting reorder request for $medicineName: " . mysqli_error($conn) . "');</script>";
            } else {
                echo "<script>alert('Reorder request generated for $medicineName.');</script>";
            }
        }
    }
}

// Check if the reorder button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reorder_stock'])) {
    reorderLowStockMedicines($conn);
}

// Fetch all medicines
$medicines = getMedicines($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Order Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* General styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .table-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th, .table-container td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-container th {
            background-color: #007bff;
            color: #fff;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .table-container td {
            font-size: 14px;
            color: #555;
        }

        .table-container tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn:hover {
            background-color: #218838;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .table-container {
                padding: 15px;
            }

            .table-container th, .table-container td {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Medicine Order Requests</h1>

        <!-- Medicine Stock Table -->
        <div class="table-container">
            <h2>Medicine Stock</h2>
            <table>
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicines as $medicine): ?>
                        <tr>
                            <td><?= $medicine['medicineName'] ?></td>
                            <td><?= $medicine['quantity'] ?></td>
                            <td><?= $medicine['reorderLevel'] ?></td>
                            <td class="<?= $medicine['quantity'] < $medicine['reorderLevel'] ? 'low-stock' : 'in-stock' ?>">
                                <?= $medicine['quantity'] < $medicine['reorderLevel'] ? 'Low Stock' : 'In Stock' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Reorder Button -->
        <form method="POST" style="text-align: center;">
            <button type="submit" name="reorder_stock" class="btn">Reorder Low Stock Medicines</button>
        </form>
    </div>
</body>
</html>