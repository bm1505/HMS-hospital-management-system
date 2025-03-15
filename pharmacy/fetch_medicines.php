<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Fetch medicine details for a specific prescription
if (isset($_GET['prescriptionID'])) {
    $prescriptionID = intval($_GET['prescriptionID']);
    $query = "SELECT medicationName, quantities, dosages, instructions 
              FROM prescription_medicines 
              WHERE prescriptionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $prescriptionID);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicines = [];
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }
    $stmt->close();
    echo json_encode($medicines);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
$conn->close();
?>