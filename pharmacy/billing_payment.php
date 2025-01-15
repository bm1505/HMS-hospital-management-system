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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
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

        form {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        form input:focus {
            border-color: #007bff;
            outline: none;
        }

        form button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #218838;
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Table styles */
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: bold;
        }

        td {
            font-size: 14px;
            color: #555;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td:last-child a {
            margin-right: 10px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            form {
                padding: 15px;
            }

            table, th, td {
                font-size: 12px;
            }

            form input, form button {
                font-size: 14px;
            }
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
        <a href="bills_list.php">View Bills</a>
    </form>

</body>
</html>
