<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prices = $_POST['prices'];
    
    foreach ($prices as $medicineID => $price) {
        $stmt = $conn->prepare("UPDATE prescription_medicines SET price = ? WHERE id = ?");
        $stmt->bind_param("di", $price, $medicineID);
        $stmt->execute();
    }
    
    $_SESSION['success'] = "Prices submitted successfully!";
    header("Location: prescriptions.php");
    exit;
}
?>