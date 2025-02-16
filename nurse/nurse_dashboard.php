<?php
session_start();

// For demonstration, assume a logged‐in nurse ID. In production, this would be set at login.
if (!isset($_SESSION['nurse_id'])) {
    $_SESSION['nurse_id'] = 1;
}
$nurse_id = $_SESSION['nurse_id'];

// Database connection details
$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "st_norbert_hospital";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------------- Nurse Status Code ----------------
// Fetch nurse status from the "nurses" table
$stmt = $conn->prepare("SELECT status FROM nurses WHERE id = ?");
$stmt->bind_param("i", $nurse_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $nurse_status = $row['status'];
} else {
    // If no record exists, insert a default status 'In'
    $nurse_status = 'In';
    $insert = $conn->prepare("INSERT INTO nurses (id, status) VALUES (?, ?)");
    $insert->bind_param("is", $nurse_id, $nurse_status);
    $insert->execute();
    $insert->close();
}
$stmt->close();

// Handle the toggle status button
if (isset($_POST['toggle_status'])) {
    $new_status = ($nurse_status === 'In') ? 'Out' : 'In';
    $update_stmt = $conn->prepare("UPDATE nurses SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $nurse_id);
    $update_stmt->execute();
    $update_stmt->close();
    $nurse_status = $new_status;
    $message_status = "Nurse status updated to " . $new_status . "!";
}
// ---------------- End Nurse Status Code ----------------

// Handle patient status update to 'done'
if (isset($_GET['remove_patientID'])) {
    $patientID = $_GET['remove_patientID'];
    $update_query = "UPDATE patient_treatment SET status = 'done' WHERE patientID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $patientID);
    if ($stmt->execute()) {
        $message = "";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch patients sent to the nurse
$patients_query = "
    SELECT p.patientID, p.first_name, p.last_name, p.dateofBirth, p.gender, p.phone, 
           p.email, p.insurance_number, p.doctor_type
    FROM patients p
    JOIN patient_treatment pt ON p.patientID = pt.patientID
    WHERE pt.status = 'under treatment'
    ORDER BY pt.sent_to_nurse DESC
";
$patients_result = $conn->query($patients_query);
if ($patients_result === false) {
    die("Error fetching patients: " . $conn->error);
}

// Handle form submission for recording patient vitals
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_vitals'])) {
    $patientID = $_POST['patientID'];
    $weight = $_POST['weight'];
    $blood_pressure = $_POST['blood_pressure'];
    $temperature = $_POST['temperature'];
    $height = $_POST['height'];
    $other_notes = $_POST['other_notes'];

    $stmt = $conn->prepare("INSERT INTO patient_vitals (patientID, weight, blood_pressure, temperature, height, other_notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddsds", $patientID, $weight, $blood_pressure, $temperature, $height, $other_notes);
    if ($stmt->execute()) {
        // Mark patient as 'done'
        $update_query = "UPDATE patient_treatment SET status = 'done' WHERE patientID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $patientID);
        $update_stmt->execute();
        $update_stmt->close();
        $message = "Patient vitals recorded successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <!-- Refresh the page every 5 seconds -->
    <meta http-equiv="refresh" content="5">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
       :root {
          --primary-color: rgb(98, 114, 124);
          --secondary-color: rgb(9, 143, 153);
          --accent-color: #f39c12;
          --bg-color: rgb(64, 228, 240);
       }
       body {
          font-family: 'Poppins', sans-serif;
          background-color: var(--bg-color);
          display: flex;
          justify-content: center;
          padding: 20px;
       }
       .container {
           width: 100%;
           max-width: 1200px;
           background-color: rgba(1, 143, 124, 0.49);
           box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
           border-radius: 10px;
           padding: 20px;
           margin-bottom: 20px;
       }
       h1 {
           color:rgb(0, 43, 43);
           text-align: center;
           margin-bottom: 20px;
       }
       .nav-bar {
           display: flex;
           justify-content: space-between;
           align-items: center;
           background-color:rgb(142, 250, 250);
           padding: 10px 20px;
           color: #fff;
           border-radius: 10px 10px 0 0;
           margin-bottom: 20px;
       }
       .nav-bar a {
           color: #fff;
           text-decoration: none;
           font-size: 14px;
           margin: 0 10px;
           padding: 5px 10px;
           background-color: #0069d9;
           border-radius: 5px;
       }
       .nav-bar a:hover {
           background-color: #0056b3;
       }
       table {
           width: 100%;
           border-collapse: collapse;
           margin-bottom: 20px;
           box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
       }
       table thead {
           background-color: #0056b3;
           color: #fff;
           text-transform: uppercase;
           font-size: 14px;
       }
       table th, table td {
           padding: 12px;
           text-align: center;
           border: 1px solid #ddd;
       }
       table tbody tr:nth-child(even) {
           background-color: #f9f9f9;
       }
       table tbody tr:hover {
           background-color: #f1f1f1;
       }
       .btn-action {
           display: inline-block;
           padding: 5px 10px;
           color: #fff;
           background-color:rgb(0, 255, 60);
           border-radius: 4px;
           text-decoration: none;
           font-size: 13px;
       }
       .btn-action:hover {
           background-color: #218838;
       }
       .btn-remove {
           display: inline-block;
           padding: 5px 10px;
           color: #fff;
           background-color: #dc3545;
           border-radius: 4px;
           text-decoration: none;
           font-size: 13px;
       }
       .btn-remove:hover {
           background-color: #c82333;
       }
       .alert {
           background-color: #e7f5ff;
           border-left: 5px solid #007bff;
           padding: 10px;
           color: #0056b3;
           margin-bottom: 20px;
           font-size: 14px;
           border-radius: 5px;
       }
       /* Nurse Status Section */
       .nurse-status {
           display: flex;
           align-items: center;
           justify-content: center;
           margin-bottom: 20px;
           font-size: 16px;
       }
       .nurse-status .status-label {
           margin-right: 10px;
           font-weight: bold;
       }
       .status-in {
           color: rgb(229, 255, 0);
           font-weight: bold;
       }
       .status-out {
           color: rgb(255, 25, 0);
           font-weight: bold;
       }
       .nurse-status button {
           margin-left: 20px;
       }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-bar">
        <a href="view_vitals.php">Vitals</a>
        <a href="../logout.php">Logout</a>
    </div>
    <h1>Nurse Dashboard</h1>
    
    <?php if (!empty($message_status)): ?>
        <div class="alert text-center"><?= htmlspecialchars($message_status) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Nurse Status Section -->
    <div class="nurse-status">
        <span class="status-label"><h2>Patient present:</span>
        <span class="<?php echo $nurse_status === 'In' ? 'status-in' : 'status-out'; ?>">
            <?php echo $nurse_status; ?>
        </span>
        <form method="POST" style="display:inline;">
            <button type="submit" name="toggle_status" class="btn btn-sm btn-primary">🟢</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Phone</th>     
                    <th>Email</th>
                    <th>Insurance Number</th>
                    <th>Doctor Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($patients_result && $patients_result->num_rows > 0): ?>
                    <?php while ($patient = $patients_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($patient['patientID']) ?></td>
                            <td><?= htmlspecialchars($patient['first_name']) ?></td>
                            <td><?= htmlspecialchars($patient['last_name']) ?></td>
                            <td><?= htmlspecialchars($patient['dateofBirth']) ?></td>
                            <td><?= htmlspecialchars($patient['gender']) ?></td>
                            <td><?= htmlspecialchars($patient['phone']) ?></td>
                            <td><?= htmlspecialchars($patient['email']) ?></td>
                            <td><?= htmlspecialchars($patient['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($patient['doctor_type']) ?></td>
                            <td>
                                <a href="?remove_patientID=<?= $patient['patientID'] ?>" class="btn btn-remove">Mark Done</a>
                                <a href="vital.php?patientID=<?= $patient['patientID'] ?>" class="btn btn-action">Add Vitals</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No patients found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
