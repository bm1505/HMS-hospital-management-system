<?php
// update_status.php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if recordID is provided
if (isset($_GET['recordID'])) {
    // Ensure the recordID is treated as an integer to prevent SQL injection
    $recordID = intval($_GET['recordID']);
    
    // Update the status to 'Completed'
    $sql = "UPDATE equipment_maintenance SET status='Completed' WHERE recordID=$recordID";
    
    if (mysqli_query($conn, $sql)) {
        // Redirect back to the maintenance records page after update
        header("Location: equipment_maintenance.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    echo "No record ID specified.";
}

mysqli_close($conn);
?>
