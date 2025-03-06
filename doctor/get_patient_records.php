<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "st_norbert_hospital");
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get patient ID from the request
$patientId = $_GET['patientID'] ?? 0;

// Validate and escape the patient ID
if (empty($patientId)) {
    die(json_encode(['error' => 'Patient ID is required']));
}
$patientId = $conn->real_escape_string($patientID);

// Fetch medical history and doctor's notes
$query = "SELECT test_result, doctor_notes FROM laboratory_results WHERE patientID = '$patientID'";
$result = $conn->query($query);

if (!$result) {
    // Handle query errors
    die(json_encode(['error' => 'Query error: ' . $conn->error]));
}

if ($result->num_rows > 0) {
    // Fetch the record
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    // No records found
    echo json_encode(['error' => 'No records found']);
}

$conn->close();
?>