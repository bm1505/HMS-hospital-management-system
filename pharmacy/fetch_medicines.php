<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Ensure prescriptionID is provided
if (!isset($_GET['prescriptionID'])) {
    echo json_encode(["error" => "Missing prescriptionID"]);
    exit;
}

$prescriptionID = intval($_GET['prescriptionID']);

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch medicines from prescription_medicines table for the given prescriptionID
$query = "SELECT medicineID, prescriptionID, medicationName, quantities, dosages, instructions 
          FROM prescription_medicines 
          WHERE prescriptionID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $prescriptionID);
$stmt->execute();
$result = $stmt->get_result();

$medicines = [];
while($row = $result->fetch_assoc()){
    $medicines[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($medicines);
?>
