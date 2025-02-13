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

// Create bills table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS bills (
    billID INT AUTO_INCREMENT PRIMARY KEY,
    patientID INT NOT NULL,
    patientName VARCHAR(255) NOT NULL,
    medicationName VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    pricePerUnit DECIMAL(10, 2) NOT NULL,
    totalAmount DECIMAL(10, 2) NOT NULL,
    paymentStatus ENUM('Pending', 'Paid') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Create discharge table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS discharge (
    dischargeID INT AUTO_INCREMENT PRIMARY KEY,
    patientID INT NOT NULL,
    patientName VARCHAR(255) NOT NULL,
    dischargeDate DATE NOT NULL,
    totalCost DECIMAL(10, 2) NOT NULL,
    paymentStatus ENUM('Pending', 'Paid') DEFAULT 'Pending'
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Create complete_treatment table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS complete_treatment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patientName VARCHAR(255) NOT NULL,
    paymentStatus ENUM('Pending', 'Paid') DEFAULT 'Pending',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Function to fetch all discharge records
function getDischarges($conn) {
    $sql = "SELECT * FROM discharge";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        die("Error executing query: " . mysqli_error($conn));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Mark treatment as complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_complete'])) {
    $patientName = $_POST['patientName'];
    $paymentStatus = $_POST['paymentStatus'];

    // Check if payment status is "Paid"
    if ($paymentStatus === 'Paid') {
        $sql = "INSERT INTO complete_treatment (patientName, paymentStatus) 
                VALUES ('$patientName', '$paymentStatus')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Treatment marked as complete!');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Cannot mark as done. Payment status is Pending.');</script>";
    }
}

// Fetch discharge records
$discharges = getDischarges($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing and Payment Management</title>
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

        .payment-status.paid {
            color: #28a745;
        }

        .payment-status.pending {
            color: #dc3545;
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

        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn:hover:not(:disabled) {
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
        <!-- Discharge Table -->
        <div class="table-container">
            <h2>Bills Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>                      
                        <th>Total Cost</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discharges as $discharge): ?>
                        <tr>                           
                            <td><?= $discharge['patientName'] ?></td>
                            <td><?= number_format($discharge['totalCost'], 2) ?></td>
                            <td class="payment-status <?= strtolower($discharge['paymentStatus']) ?>">
                                <?= $discharge['paymentStatus'] ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="patientName" value="<?= $discharge['patientName'] ?>">
                                    <input type="hidden" name="paymentStatus" value="<?= $discharge['paymentStatus'] ?>">
                                    <button type="submit" name="mark_complete" class="btn" <?= $discharge['paymentStatus'] === 'Pending' ? 'disabled' : '' ?>>
                                        Done
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>