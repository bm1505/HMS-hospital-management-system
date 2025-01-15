<?php
// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctors
$sql = "SELECT * FROM doctors";
$doctors_result = $conn->query($sql);

// Initialize variables
$success_message = "";
$error_message = "";
$availability_message = "";
$appointments_result = null; // Initialize appointments_result

// Handle doctor selection for viewing appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id']) && !empty($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];

    // Fetch appointments for the selected doctor
    $appointments_sql = "SELECT * FROM appointments WHERE doctor_id = ?";
    $appointments_stmt = $conn->prepare($appointments_sql);
    $appointments_stmt->bind_param("i", $doctor_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Appointments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-primary {
            width: 100%;
        }
        .alert {
            margin-top: 20px;
        }
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Hospital Management</a>
    </nav>

    <div class="container mt-5">
        <h3>Doctor's Appointments</h3>
        <p>Select a doctor to view and book appointments.</p>

        <!-- Doctor selection form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="doctor_id">Select Doctor:</label>
                <select class="form-control" id="doctor_id" name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                        <option value="<?php echo $doctor['doctorID']; ?>">
                            <?php echo $doctor['firstName'] . " " . $doctor['middleName'] . " " . $doctor['surname']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">View Appointments</button>
        </form>

        <!-- Display appointments if doctor is selected -->
        <?php if ($appointments_result !== null): ?>
            <div class="appointment-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Appointment Date</th>
                            <th>Appointment Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments_result->num_rows > 0): ?>
                            <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $appointment['patient_name']; ?></td>
                                    <td><?php echo $appointment['appointment_date']; ?></td>
                                    <td><?php echo $appointment['appointment_time']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
