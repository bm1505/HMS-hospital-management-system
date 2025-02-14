<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode([]);
    exit;
}

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([]));
}

// Get the search term and escape it for safety
$searchTerm = "";
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
}

// Query the medicine_stock table for matching records
$sql = "SELECT * FROM medicine_stock WHERE medicineName LIKE '%$searchTerm%'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode([]);
    exit;
}

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);
echo json_encode($data);
$conn->close();
?>
