<?php
session_start();

// Database connection details
$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$patientID = $_GET['patientID'] ?? '';

if (empty($patientID)) {
    die(json_encode(['error' => 'Patient ID is required']));
}

// Query to fetch patient details
$query = "
    SELECT 
        p.patientID, 
        p.dateofBirth, 
        p.gender, 
        p.contactInfo, 
        p.first_name, 
        p.last_name,
        lr.test_result AS test_result,
        mp.medicationName,
        mp.dosages
    FROM 
        patients p
    LEFT JOIN 
        laboratory_results lr ON p.patientID = lr.patientID
    LEFT JOIN 
        prescription_medicines mp ON p.patientID = mp.patientID
    WHERE 
        p.patientID = ?
    LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $patientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Patient not found']);
}

$stmt->close();
$conn->close();
?>