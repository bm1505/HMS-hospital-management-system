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

// Handle new notification submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendNotification'])) {
    $message = $_POST['message'];
    $sentBy = "Lab Department"; // Can be dynamic based on the user logged in or context
    $dateSent = date('Y-m-d H:i:s');
    $status = "Unread"; // Default status

    $sql = "INSERT INTO doctor_notifications (message, sentBy, dateSent, status) 
            VALUES ('$message', '$sentBy', '$dateSent', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Notification sent successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch doctors list
$doctorQuery = "SELECT doctorID, firstName, lastName FROM doctors";
$doctorResult = mysqli_query($conn, $doctorQuery);
$doctors = mysqli_fetch_all($doctorResult, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Notifications</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Doctor Notifications</h1>

    <!-- Notification form -->
    <div class="notification-form">
        <h2>Send Notification to Doctors</h2>
        <label for="doctor">Select Doctor:</label>
            <select name="doctor" id="doctor" required>
                <?php
                // Check if doctors exist and generate options
                if ($doctors) {
                    foreach ($doctors as $doctor) {
                        echo "<option value='{$doctor['doctorID']}'>{$doctor['firstName']} {$doctor['lastName']}</option>";
                    }
                } else {
                    echo "<option>No doctors available</option>";
                }
                ?>
            </select>

        <form method="POST" action="doctor_notification.php">
            <label for="message">Notification Message:</label>
            <textarea id="message" name="message" rows="4" required></textarea>
            
            <!-- Select doctors to notify -->
           
            <button type="submit" name="sendNotification">Send Notification</button>
        </form>
    </div>

    <!-- Display notifications -->
    <h2>Notification List</h2>
    <table>
        <thead>
            <tr>
                <th>Notification ID</th>
                <th>Message</th>
                <th>Sent By</th>
                <th>Date Sent</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch notifications list
            $sql = "SELECT * FROM doctor_notifications";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['notificationID']}</td>
                        <td>{$row['message']}</td>
                        <td>{$row['sentBy']}</td>
                        <td>{$row['dateSent']}</td>
                        <td class='status {$row['status']}'>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
mysqli_close($conn);
?>

<style>
    /* General styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
    }

    h1, h2 {
        text-align: center;
        color: #333;
    }

    .notification-form {
        width: 60%;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .notification-form textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
    }

    .notification-form select {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .notification-form button {
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    .notification-form button:hover {
        background-color: #0056b3;
    }

    /* Table styles */
    table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #007bff;
        color: #fff;
        text-transform: uppercase;
        font-size: 14px;
        font-weight: bold;
    }

    td {
        font-size: 14px;
        color: #555;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    /* Status styling */
    td.status {
        font-weight: bold;
        color: #007bff;
    }

    td.status.Unread {
        color: orange;
    }

    td.status.Read {
        color: green;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        table, th, td {
            font-size: 12px;
        }

        h1 {
            font-size: 18px;
        }
    }
</style>
