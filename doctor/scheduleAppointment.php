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

// Initialize variables for success and error messages
$success_message = "";
$error_message = "";

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $patient_name = $_POST['patient_name'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Insert the appointment into the appointments table
    $insert_sql = "INSERT INTO appointments (patient_name, doctorID, appointment_date, appointment_time) 
                   VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $patient_name, $doctorID, $appointment_date, $appointment_time);
    
    if ($insert_stmt->execute()) {
        $success_message = "Appointment booked successfully!";
    } else {
        $error_message = "Error booking appointment. Please try again.";
    }
    $insert_stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Scheduling</title>
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
        <h3>Book an Appointment</h3>

        <form method="POST" action="appointmentPage.php">
            <div class="form-group">
                <label for="patientName">Patient Name</label>
                <input type="text" name="patient_name" id="patientName" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="doctor">Select Doctor</label>
                <select name="doctorID" id="doctor" class="form-control" required>
                    <option value="">Choose a doctor</option>
                    <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                        <option value="<?php echo $doctor['doctorID']; ?>"><?php echo $doctor['firstName'] . " " . $doctor['surname'] . " (" . $doctor['specialization'] . ")"; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="appointmentDate">Appointment Date</label>
                <input type="date" name="appointment_date" id="appointmentDate" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="appointmentTime">Appointment Time</label>
                <input type="time" name="appointment_time" id="appointmentTime" class="form-control" required>
            </div>

            <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
        </form>

        <!-- Display success or error message -->
        <?php if ($success_message): ?>
            <div class="alert alert-success mt-3"><?php echo $success_message; ?></div>
            <!-- Button to view appointment -->
            <a href="appointmentpage.php" class="btn btn-secondary mt-2">View Appointment</a>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger mt-3"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
