<?php
// fetch_doctors_status.php
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query  = "SELECT firstName, surname,  status FROM doctors";
$result = $conn->query($query);

$output = "";
while ($row = $result->fetch_assoc()) {
    $output .= "<tr>";
    $output .= "<td>" . $row['firstName'] . " " . $row['surname'] . "</td>";
   
    $statusClass = ($row['status'] === 'In') ? 'status-in' : 'status-out';
    $output .= "<td><span class='$statusClass'>" . $row['status'] . "</span></td>";
    $output .= "</tr>";
}
echo $output;
$conn->close();
?>
