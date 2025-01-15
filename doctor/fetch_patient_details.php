<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['patient_id'])) {
    $patient_id = (int)$_GET['patient_id'];
    $query = "SELECT first_name, last_name, age, gender, contact, address FROM patients WHERE patientID = $patient_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        $response = [
            'full_name' => $patient['first_name'] . ' ' . $patient['last_name'],
            'age' => $patient['age'],
            'gender' => $patient['gender'],
            'contact' => $patient['contact'],
            'address' => $patient['address'],
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'No patient found.']);
    }
}

$conn->close();
?>
