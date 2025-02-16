<?php
// remove_expired_assignments.php
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentTime = date('Y-m-d H:i:s');
$query       = "UPDATE patient_vitals SET doctorID = NULL, remove_at = NULL WHERE remove_at <= ?";
$stmt        = $conn->prepare($query);
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

echo "Removed " . $affected . " expired assignments.";
?>
