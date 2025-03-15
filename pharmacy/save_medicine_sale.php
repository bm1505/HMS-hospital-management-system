<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Unauthorized access");
}

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process medicine sales data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medicines'])) {
    $medicines = json_decode($_POST['medicines'], true);
    
    if (!is_array($medicines) || empty($medicines)) {
        die("Invalid data received.");
    }

    $stmt = $conn->prepare("INSERT INTO medicines_sold (medicine_name, quantity_sold, selling_price, cost_price, sale_date) VALUES (?, ?, ?, ?, ?)");

    foreach ($medicines as $medicine) {
        $stmt->bind_param("sidds", $medicine['medicine_name'], $medicine['quantity_sold'], $medicine['selling_price'], $medicine['cost_price'], $medicine['sale_date']);
        if (!$stmt->execute()) {
            die("Error inserting data: " . $stmt->error);
        }
    }

    echo "Medicine sales recorded successfully.";
    $stmt->close();
}

$conn->close();
?>
