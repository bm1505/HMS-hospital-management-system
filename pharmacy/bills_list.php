<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital"; // Update to your actual database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to fetch all bills with patient names
// Function to fetch all bills
function getBills($conn) {
    $sql = "SELECT * FROM bills";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


// Update payment status
if (isset($_GET['pay'])) {
    $billID = $_GET['pay'];
    $sql = "UPDATE bills SET paymentStatus = 'Paid' WHERE billID = '$billID'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Payment status updated successfully!'); window.location='bills_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

$bills = getBills($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bills List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
        }
        td {
            color: #555;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Bills List</h2>
    <table>
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Medication Name</th>
                <th>Quantity</th>
                <th>Price Per Unit</th>
                <th>Total Amount</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bills)): ?>
                <?php foreach ($bills as $bill): ?>
                    <tr>
                        <td><?= htmlspecialchars($bill['patientName']); ?></td>
                        <td><?= htmlspecialchars($bill['medicationName']); ?></td>
                        <td><?= htmlspecialchars($bill['quantity']); ?></td>
                        <td><?= htmlspecialchars($bill['pricePerUnit']); ?></td>
                        <td><?= htmlspecialchars($bill['totalAmount']); ?></td>
                        <td><?= htmlspecialchars($bill['paymentStatus']); ?></td>
                        <td>
                            <?php if ($bill['paymentStatus'] == "Pending"): ?>
                                <a href="?pay=<?= $bill['billID']; ?>" 
                                   onclick="return confirm('Are you sure you want to mark this bill as paid?');">Mark as Paid</a>
                            <?php else: ?>
                                Paid
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No bills found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
