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
$popupOpen = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendNotification'])) {
    // Get and sanitize input
    $doctorID = mysqli_real_escape_string($conn, $_POST['doctor']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sentBy = "Lab Department"; // This can be dynamic based on your context
    $dateSent = date('Y-m-d H:i:s');
    $status = "Unread"; // Default status

    $sql = "INSERT INTO doctor_notifications (doctorID, message, sentBy, dateSent, status) 
            VALUES ('$doctorID', '$message', '$sentBy', '$dateSent', '$status')";
    mysqli_query($conn, $sql);
    // Do not ask for confirmation; simply set flag to open modal popup
    $popupOpen = true;
}

// Fetch doctors list for the select box
$doctorQuery = "SELECT doctorID, firstName, middleName, surname FROM doctors";
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
 <style>
    /* Import Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

/* Global Reset and Body Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f1f4f8;
    color: #2c3e50;
    line-height: 1.6;
    padding: 20px;
}

/* Heading Styles */
h1, h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 700;
    color: #34495e;
}

/* Notification Form Styles */
.notification-form {
    width: 60%;
    max-width: 600px;
    margin: 30px auto;
    padding: 30px;
    background: linear-gradient(145deg, #ffffff, #e3eaf3);
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border: 1px solid #d1d9e6;
}
.notification-form h2 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    color: #34495e;
}
.notification-form form {
    display: flex;
    flex-direction: column;
}
.notification-form label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #34495e;
}
.notification-form select,
.notification-form textarea {
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    color: #2c3e50;
    background-color: #fff;
    transition: border-color 0.3s ease;
}
.notification-form select:focus,
.notification-form textarea:focus {
    outline: none;
    border-color: #2980b9;
    box-shadow: 0 0 8px rgba(41, 128, 185, 0.2);
}
.notification-form button {
    padding: 12px 20px;
    background-color: #2980b9;
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.notification-form button:hover {
    background-color: #1f6391;
}

/* Modal Popup Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 25px;
    border-radius: 10px;
    width: 80%;
    max-width: 800px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    position: relative;
}
.back-button {
    display: inline-block;
    margin-bottom: 15px;
    padding: 10px 20px;
    background-color: #2980b9;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
    transition: background-color 0.3s ease;
}
.back-button:hover {
    background-color: #1f6391;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #2980b9;
    color: #fff;
    text-transform: uppercase;
    font-size: 0.9rem;
    font-weight: 700;
}
td {
    font-size: 0.9rem;
    color: #2c3e50;
}
tr:hover {
    background-color: #f2f8fc;
}
td.status {
    font-weight: bold;
}
td.status.Unread {
    color: #e67e22;
}
td.status.Read {
    color: #27ae60;
}

 </style>
</head>
<body>
  <h1>Doctor Notifications</h1>
  <!-- Notification form -->
  <div class="notification-form"><a href="laboratory.php" class="back-button">Back</a>
    <h2>Send Notification to Doctors</h2>
    <form method="POST" action="doctor_notification.php">
      <label for="doctor">Select Doctor:</label>
      <select name="doctor" id="doctor" required>
        <?php
        if ($doctors) {
            foreach ($doctors as $doctor) {
                echo "<option value='{$doctor['doctorID']}'>{$doctor['firstName']} {$doctor['middleName']} {$doctor['surname']}</option>";
            }
        } else {
            echo "<option>No doctors available</option>";
        }
        ?>
      </select>
      <label for="message">Notification Message:</label>
      <textarea id="message" name="message" rows="4" required></textarea>
      <button type="submit" name="sendNotification">Send Notification</button>
    </form>
  </div>
  
  <!-- Modal Popup for Notifications -->
  <div id="notificationModal" class="modal">
    <div class="modal-content">
      <a href="laboratory.php" class="back-button">Back</a>
      <h2>Notification List</h2>
      <div id="notificationsTable">
        <!-- Notifications table will be loaded here via AJAX -->
      </div>
    </div>
  </div>
  
  <script>
    // Function to load notifications via AJAX
    function loadNotifications() {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'fetch_notifications.php', true);
      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
          document.getElementById('notificationsTable').innerHTML = xhr.responseText;
        }
      };
      xhr.send();
    }
    
    // Open the modal popup if a notification was sent
    <?php if ($popupOpen) { ?>
      document.addEventListener("DOMContentLoaded", function() {
        var modal = document.getElementById('notificationModal');
        modal.style.display = "block";
        loadNotifications(); // Load notifications immediately
      });
    <?php } ?>
    
    // Refresh notifications every 5 seconds when the modal is open
    setInterval(function() {
      var modal = document.getElementById('notificationModal');
      if (modal.style.display == "block") {
        loadNotifications();
      }
    }, 5000);
  </script>
</body>
</html>
<?php mysqli_close($conn); ?>
