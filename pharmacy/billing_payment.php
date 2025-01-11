<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital"; // Update to your actual database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to fetch all bills
function getBills($conn) {
    $sql = "SELECT * FROM bills";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Add a new bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addBill'])) {
    $patientID = $_POST['patientID'];
    $medicationName = $_POST['medicationName'];
    $quantity = $_POST['quantity'];
    $pricePerUnit = $_POST['pricePerUnit'];
    $totalAmount = $quantity * $pricePerUnit;
    $paymentStatus = "Pending";

    $sql = "INSERT INTO bills (patientID, medicationName, quantity, pricePerUnit, totalAmount, paymentStatus) 
            VALUES ('$patientID', '$medicationName', '$quantity', '$pricePerUnit', '$totalAmount', '$paymentStatus')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Bill added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Update payment status
if (isset($_GET['pay'])) {
    $billID = $_GET['pay'];
    $sql = "UPDATE bills SET paymentStatus = 'Paid' WHERE billID = '$billID'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Payment status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch bills list
$bills = getBills($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing and Payment Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        form {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Billing and Payment Management</h1>

    <h2>Add New Bill</h2>
    <form method="POST" action="">
        <label for="patientID">Patient ID:</label>
        <input type="text" name="patientID" id="patientID" required><br>
        <label for="medicationName">Medication Name:</label>
        <input type="text" name="medicationName" id="medicationName" required><br>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" required><br>
        <label for="pricePerUnit">Price Per Unit:</label>
        <input type="number" step="0.01" name="pricePerUnit" id="pricePerUnit" required><br>
        <button type="submit" name="addBill">Add Bill</button>
    </form>

    <h2>Bills List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient ID</th>
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
                        <td><?= $bill['billID']; ?></td>
                        <td><?= $bill['patientID']; ?></td>
                        <td><?= $bill['medicationName']; ?></td>
                        <td><?= $bill['quantity']; ?></td>
                        <td><?= $bill['pricePerUnit']; ?></td>
                        <td><?= $bill['totalAmount']; ?></td>
                        <td><?= $bill['paymentStatus']; ?></td>
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
                    <td colspan="8">No bills found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

h1, h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

form {
    max-width: 600px;
    margin: 0 auto 40px;
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

form input {
    width: calc(100% - 20px);
    padding: 8px 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

form button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

form button:hover {
    background-color: #218838;
}

/* Table styles */
table {
    width: 90%;
    margin: 0 auto 40px;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
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

a {
    text-decoration: none;
    color: #007bff;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

td:last-child a {
    margin-right: 10px;
}

/* Alert styles */
.alert {
    width: 90%;
    margin: 20px auto;
    padding: 15px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    font-size: 14px;
    text-align: center;
}

/* Responsive styles */
@media (max-width: 768px) {
    table, th, td {
        font-size: 12px;
    }

    form input, form button {
        font-size: 14px;
    }
}

</style>
</html>
