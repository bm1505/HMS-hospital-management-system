<?php
// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch patient details and medical history
if (isset($_GET['patient_id']) && !empty($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];

    // Fetch patient details
    $patient_result = $conn->query("SELECT * FROM patients WHERE id = $patient_id");
    $patient = $patient_result->fetch_assoc();

    // Fetch medical history
    $medical_history = [];
    $history_result = $conn->query("SELECT * FROM patient_diagnosis WHERE patient_id = $patient_id");
    while ($row = $history_result->fetch_assoc()) {
        $medical_history[] = $row;
    }

    // Display patient details
    echo "<h5>Patient Details</h5>";
    echo "<p><strong>Name:</strong> {$patient['firstName']} {$patient['lastName']}</p>";
    echo "<p><strong>Age:</strong> {$patient['age']}</p>"; // Assuming age field exists
    echo "<p><strong>Gender:</strong> {$patient['gender']}</p>"; // Assuming gender field exists

    // Display medical history
    echo "<h5>Medical History:</h5>";
    if (!empty($medical_history)) {
        echo "<ul>";
        foreach ($medical_history as $history) {
            echo "<li>";
            echo "<strong>Diagnosis:</strong> {$history['diagnosis']}<br>";
            echo "<strong>Medications:</strong> {$history['medications']}<br>";
            echo "<strong>Lab Tests:</strong> {$history['lab_tests']}<br>";
            echo "<strong>Date:</strong> {$history['created_at']}<br>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No medical history found.</p>";
    }
}

$conn->close();
?>
