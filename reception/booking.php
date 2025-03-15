<?php
// scheduleAppointment.php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$success_message = "";
$error_message = "";

// Function to automatically remove past appointments
function removePastAppointments($conn) {
    $currentDateTime = date('Y-m-d H:i:s');
    $sql = "DELETE FROM appointments WHERE CONCAT(appointment_date, ' ', appointment_time) < '$currentDateTime'";
    if ($conn->query($sql) === TRUE) {
        // Optional: Log or handle the deletion
    } else {
        // Optional: Log or handle the error
    }
}

// Call the function to remove past appointments
removePastAppointments($conn);

// Process Add Appointment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_appointment'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $conn->real_escape_string($_POST['reason']);

    $sql = "INSERT INTO appointments (doctor_id, patient_name, appointment_date, appointment_time, reason)
            VALUES ($doctor_id, '$patient_name', '$appointment_date', '$appointment_time', '$reason')";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Appointment added successfully.";
    } else {
        $error_message = "Error adding appointment: " . $conn->error;
    }
}

// Process Remove Appointment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['removeAppointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $sql = "DELETE FROM appointments WHERE id = $appointment_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Appointment removed successfully.";
    } else {
        $error_message = "Error removing appointment: " . $conn->error;
    }
}

// Process Diagnosis form submission from the modal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveDiagnosis'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $lab_tests = $conn->real_escape_string($_POST['lab_tests']);
    $other_notes = $conn->real_escape_string($_POST['other_notes']);

    $sql = "UPDATE appointments SET diagnosis='$diagnosis', lab_tests='$lab_tests', other_notes='$other_notes'
            WHERE id = $appointment_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Diagnosis updated successfully.";
    } else {
        $error_message = "Error updating diagnosis: " . $conn->error;
    }
}

// Determine the selected doctor for displaying appointments.
$selected_doctor_id = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['doctor_id'])) {
    $selected_doctor_id = intval($_POST['doctor_id']);
} else {
    $doctor_sql = "SELECT doctorID FROM doctors ORDER BY doctorID LIMIT 1";
    $doctor_result = $conn->query($doctor_sql);
    if ($doctor_result && $doctor_result->num_rows > 0) {
        $doctor_row = $doctor_result->fetch_assoc();
        $selected_doctor_id = intval($doctor_row['doctorID']);
    }
}

// Retrieve list of doctors for the dropdown
$doctors = [];
$doctor_sql = "SELECT doctorID, firstName, middleName, surname FROM doctors";
$doctor_result = $conn->query($doctor_sql);
if ($doctor_result) {
    while ($row = $doctor_result->fetch_assoc()) {
        $fullName = $row['firstName'] . " ";
        if (!empty($row['middleName'])) {
            $fullName .= $row['middleName'] . " ";
        }
        $fullName .= $row['surname'];
        $row['fullName'] = trim($fullName);
        $doctors[] = $row;
    }
}

// Retrieve all appointments for the selected doctor
$appointments = [];
if ($selected_doctor_id > 0) {
    $sql = "SELECT a.id, a.patient_name, a.appointment_date, a.appointment_time, a.reason, 
                   a.diagnosis, a.lab_tests, a.other_notes,
                   CONCAT(d.firstName, ' ', IFNULL(d.middleName, ''), ' ', d.surname) as doctor_name
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.doctorID
            WHERE a.doctor_id = $selected_doctor_id
            ORDER BY a.appointment_date, a.appointment_time";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #eef2f7;
            margin-top: 20px;
        }
        .container {
            margin-top: 20px;
        }
        .form-section, .table-section {
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #d1dce5;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .form-section h2, .table-section h2 {
            color: #2a4d69;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #4a90e2;
            border-color: #4a90e2;
        }
        .btn-primary:hover {
            background-color: #357ab8;
            border-color: #2d6591;
        }
        .btn-danger {
            background-color: #e94b35;
            border-color: #e94b35;
        }
        .btn-danger:hover {
            background-color: #cc3f2f;
            border-color: #b33328;
        }
        .btn-info {
            background-color: #50e3c2;
            border-color: #50e3c2;
            color: #fff;
        }
        .btn-info:hover {
            background-color: #3ac3a1;
            border-color: #36ad8e;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        .modal-header {
            background-color: #4a90e2;
            color: #fff;
        }
        .modal-title {
            color: #fff;
        }
        .alert {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" id="successMessage"><?php echo htmlspecialchars($success_message); ?></div>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert alert-danger" id="errorMessage"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Side: Appointment Form -->
        <div class="col-md-6 form-section">
            <h2>Add Appointment</h2>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="doctor_id" class="form-label">Select Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="form-select" required>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['doctorID']; ?>" <?php if ($doctor['doctorID'] == $selected_doctor_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($doctor['fullName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="patient_name" class="form-label">Patient Name</label>
                    <input type="text" class="form-control" id="patient_name" name="patient_name" placeholder="Enter patient name" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_date" class="form-label">Appointment Date</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_time" class="form-label">Appointment Time</label>
                    <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="reason" name="reason" rows="2" placeholder="Enter reason for appointment" required></textarea>
                </div>
                <button type="submit" name="submit_appointment" class="btn btn-primary">Add Appointment</button>
            </form>
        </div>

        <!-- Right Side: Appointments Table -->
        <div class="col-md-6 table-section">
            <h2>Appointments for Selected Doctor</h2>
            <?php if (!empty($appointments)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient Name</th>
                        <th>Doctor Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo $appointment['id']; ?></td>
                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($appointment['reason'])); ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                <button type="submit" name="removeAppointment" class="btn btn-danger btn-sm action-btn" onclick="return confirm('Are you sure you want to remove this appointment?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No appointments found for the selected doctor.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Automatically hide success/error messages after 1 minute
setTimeout(function() {
    var successMessage = document.getElementById('successMessage');
    var errorMessage = document.getElementById('errorMessage');
    if (successMessage) successMessage.style.display = 'none';
    if (errorMessage) errorMessage.style.display = 'none';
}, 60000); // 60,000 milliseconds = 1 minute
</script>
</body>
</html>