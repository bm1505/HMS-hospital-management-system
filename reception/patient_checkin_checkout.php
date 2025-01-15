<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctors currently with patients
$sql_doctors = "SELECT d.firstName AS doctor_first_name, d.surname AS doctor_last_name, COUNT(a.id) AS patients_count
FROM doctors d
LEFT JOIN appointments a ON d.doctorID = a.doctorID AND a.status = 'Checked In'
GROUP BY d.doctorID";
$doctors_status = $conn->query($sql_doctors);

// Fetch nurses currently with patients
$sql_nurses = "SELECT n.firstName AS nurse_first_name, n.surname AS nurse_last_name, COUNT(a.id) AS patients_count
FROM nurses n
LEFT JOIN appointments a ON n.nurseID = a.nurseID AND a.status = 'Under Nurse Care'
GROUP BY n.nurseID";
$nurses_status = $conn->query($sql_nurses);

// Fetch all appointments
$sql_appointments = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, 
    p.first_name AS patient_first_name, p.last_name AS patient_last_name, 
    d.firstName AS doctor_first_name, d.surname AS doctor_last_name
    FROM appointments a
    JOIN patients p ON a.patientID = p.patientID
    JOIN doctors d ON a.doctorID = d.doctorID";
$appointments = $conn->query($sql_appointments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Check-In & Check-Out</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 30px;
        }
        h2 {
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #34495e;
            color: #fff;
            text-transform: uppercase;
        }
        .btn {
            font-weight: bold;
            border-radius: 20px;
        }
        .btn-check-in {
            background-color: #27ae60;
            color: white;
        }
        .btn-check-in:hover {
            background-color: #1e8449;
        }
        .btn-check-out {
            background-color: #2980b9;
            color: white;
        }
        .btn-check-out:hover {
            background-color: #21618c;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Patient Check-In & Check-Out</h2>

    <!-- Doctors Table -->
    <h4>Doctors Currently Attending to Patients</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Doctor Name</th>
                <th>Patients Count</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $doctors_status->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['doctor_first_name'] . ' ' . $row['doctor_last_name']; ?></td>
                    <td><?php echo $row['patients_count']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Nurses Table -->
    <h4>Nurses Currently Attending to Patients</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nurse Name</th>
                <th>Patients Count</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $nurses_status->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nurse_first_name'] . ' ' . $row['nurse_last_name']; ?></td>
                    <td><?php echo $row['patients_count']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Appointments Table -->
    <h4>All Appointments</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Doctor Name</th>
                <th>Appointment Date</th>
                <th>Appointment Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['patient_first_name'] . ' ' . $row['patient_last_name']; ?></td>
                    <td><?php echo $row['doctor_first_name'] . ' ' . $row['doctor_last_name']; ?></td>
                    <td><?php echo $row['appointment_date']; ?></td>
                    <td><?php echo $row['appointment_time']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="patient_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="check_in" class="btn btn-check-in">Check-In</button>
                        </form>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="patient_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="check_out" class="btn btn-check-out">Check-Out</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
