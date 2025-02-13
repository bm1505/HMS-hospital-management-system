<?php
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch results from laboratory_results table
$query = "SELECT * FROM laboratory_results ORDER BY test_date DESC";
$result = mysqli_query($conn, $query);

$results = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
    }
}

echo json_encode($results);

mysqli_close($conn);
?>
