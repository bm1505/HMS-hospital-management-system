<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$patientID = $_GET['patientID'];

$sql = "SELECT first_name, last_name FROM patients WHERE patientID = '$patientID'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo $row['first_name'] . " " . $row['last_name'];
} else {
    echo '';
}

mysqli_close($conn);
?>