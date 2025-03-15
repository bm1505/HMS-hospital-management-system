<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
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

// Update prescription status to "Fulfilled"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prescriptionID'])) {
    $prescriptionID = intval($_POST['prescriptionID']);
    $stmt = $conn->prepare("UPDATE prescriptions SET status = 'Fulfilled' WHERE prescriptionID = ?");
    $stmt->bind_param("i", $prescriptionID);
    if ($stmt->execute()) {
        echo "<script>alert('Prescription marked as fulfilled!'); window.location.href = 'pharmacy.php';</script>";
    } else {
        echo "<script>alert('Error updating prescription status: " . $stmt->error . "'); window.location.href = 'pharmacy.php';</script>";
    }
    $stmt->close();
}
$conn->close();
?>