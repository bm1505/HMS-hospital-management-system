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

// Fetch notifications list (assumes a column named "doctorReply" exists)
$sql = "SELECT * FROM doctor_notifications ORDER BY dateSent DESC";
$result = mysqli_query($conn, $sql);

$output = '<table>
            <thead>
              <tr>
                <th>Message</th>
                <th>Sent By</th>
                <th>Date Sent</th>
                <th>Status</th>
                <th>Doctor Reply</th>
              </tr>
            </thead>
            <tbody>';
while ($row = mysqli_fetch_assoc($result)) {
    $doctorReply = isset($row['doctorReply']) && !empty($row['doctorReply']) ? $row['doctorReply'] : 'No reply';
    $output .= "<tr>
                  <td>{$row['message']}</td>
                  <td>{$row['sentBy']}</td>
                  <td>{$row['dateSent']}</td>
                  <td class='status {$row['status']}'>{$row['status']}</td>
                  <td>{$doctorReply}</td>
                </tr>";
}
$output .= '</tbody></table>';

echo $output;
mysqli_close($conn);
?>
