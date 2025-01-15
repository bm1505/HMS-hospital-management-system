<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if prescriptionID is passed
if (isset($_POST['prescriptionID'])) {
    $prescriptionID = $_POST['prescriptionID'];

    // Update the prescription status to 'Finished'
    $sql = "UPDATE prescriptions SET status = 'Finished' WHERE prescriptionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescriptionID);
    $stmt->execute();

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
