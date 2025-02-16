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

// Handle Check-In and Check-Out actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_in'])) {
        $appointment_id = $_POST['appointment_id'];
        $update_sql = "UPDATE appointments SET status = 'Checked In' WHERE id = $appointment_id";
        if (!$conn->query($update_sql)) {
            die("Error updating record: " . $conn->error);
        }
    } elseif (isset($_POST['check_out'])) {
        $appointment_id = $_POST['appointment_id'];
        $update_sql = "UPDATE appointments SET status = 'Checked Out' WHERE id = $appointment_id";
        if (!$conn->query($update_sql)) {
            die("Error updating record: " . $conn->error);
        }
    }
}

// Fetch doctors and their status
$sql_doctors = "SELECT doctorID, firstName, surname, specialization, contactNumber, email, qualification, address, status 
                FROM doctors";
$doctors_status = $conn->query($sql_doctors);
if (!$doctors_status) {
    die("Error fetching doctors: " . $conn->error);
}

// Fetch nurses and their status
$sql_nurses = "SELECT id, full_name, age, gender, email, address, specialization, qualification, marital_status, status 
               FROM nurses";
$nurses_status = $conn->query($sql_nurses);
if (!$nurses_status) {
    die("Error fetching nurses: " . $conn->error);
}

// Fetch all appointments
$sql_appointments = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, 
                    p.first_name AS patient_first_name, p.last_name AS patient_last_name, 
                    d.firstName AS doctor_first_name, d.surname AS doctor_last_name
                    FROM appointments a
                    JOIN patients p ON a.patientID = p.patientID
                    JOIN doctors d ON a.doctorID = d.doctorID";
$appointments = $conn->query($sql_appointments);
if (!$appointments) {
    die("Error fetching appointments: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Check-In & Check-Out</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #e9f2f9;
            font-family: 'Roboto', sans-serif;
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
        h4 {
            color: #34495e;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .table th {
            background-color: #34495e;
            color: #fff;
            text-transform: uppercase;
            font-weight: 500;
        }
        .table td {
            vertical-align: middle;
        }
        .btn {
            font-weight: bold;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 14px;
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
        .status-in {
            color: #27ae60;
            font-weight: bold;
        }
        .status-out {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Patient Check-In & Check-Out</h2>

    <!-- Doctors Table -->
    <h4>Doctors Status</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Doctor Name</th>
                <th>Specialization</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $doctors_status->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['firstName'] . ' ' . $row['surname']; ?></td>
                    <td><?php echo $row['specialization']; ?></td>
                    <td><?php echo $row['contactNumber']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <span class="<?php echo $row['status'] === 'In' ? 'status-in' : 'status-out'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Nurses Table -->
    <h4>Nurses Status</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nurse Name</th>
                <th>Specialization</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $nurses_status->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['specialization']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <span class="<?php echo $row['status'] === 'In' ? 'status-in' : 'status-out'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
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
                    <td>
                        <span class="<?php echo $row['status'] === 'Checked In' ? 'status-in' : 'status-out'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="check_in" class="btn btn-check-in">Check-In</button>
                        </form>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="check_out" class="btn btn-check-out">Check-Out</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Auto-refresh every 5 seconds -->
<script>
    setTimeout(function() {
        location.reload();
    }, 5000);
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>