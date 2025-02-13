<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prescriptionID'])) {
    $prescriptionID = intval($_POST['prescriptionID']);

    // Database connection
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "st_norbert_hospital";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update status to Done (matches ENUM values: Pending, Done)
    $updateQuery = "UPDATE prescriptions SET status='Done' WHERE prescriptionID=?";
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $prescriptionID);

    if ($stmt->execute()) {
        // Redirect back to the fulfillment page after successful update
        header("Location: prescription_fulfillment.php");
        exit;
    } else {
        die("Error updating status: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: prescription_fulfillment.php");
    exit;
}
?>
