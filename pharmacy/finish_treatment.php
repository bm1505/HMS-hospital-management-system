<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the data is received via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prescriptionID = $_POST['prescriptionID'];
    $patientName = $_POST['patientName'];
    $medicationName = $_POST['medicationName'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];

    // Insert data into finished_prescriptions table
    $sql = "INSERT INTO finished_prescriptions (patientName, medicationName, dosage, instructions) 
            VALUES ('$patientName', '$medicationName', '$dosage', '$instructions')";

    if ($conn->query($sql) === TRUE) {
        // If successful, update the status of the prescription to "Finished"
        $update_status_sql = "UPDATE prescriptions SET status = 'Finished' WHERE prescriptionID = '$prescriptionID'";

        if ($conn->query($update_status_sql) === TRUE) {
            echo "Success";
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Error inserting into finished_prescriptions: " . $conn->error;
    }
}

$conn->close();
?>
