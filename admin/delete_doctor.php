<?php
// Start session for managing session data
session_start();

// Database connection variables
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle doctor deletion if delete is requested
if (isset($_GET['id'])) {
    $doctor_id = $_GET['id'];
    
    // Delete doctor from database
    $delete_sql = "DELETE FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $doctor_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Doctor deleted successfully'); window.location.href='view_doctors.php';</script>";
    } else {
        echo "<script>alert('Error deleting doctor'); window.location.href='view_doctors.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('No doctor specified for deletion'); window.location.href='view_doctors.php';</script>";
}

$conn->close();
?>
