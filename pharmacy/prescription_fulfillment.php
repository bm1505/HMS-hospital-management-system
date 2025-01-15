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

// Handle form submission to create a prescription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_prescription'])) {
    // Get form values
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $medicine_name = $_POST['medicine_name'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];

    // Prepare the SQL statement
    $sql = "INSERT INTO prescriptions (patientID, doctorID, medicationName, dosage, instructions) 
            VALUES (?, ?, ?, ?, ?)";
    
    // Prepare the statement and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $medicine_name, $dosage, $instructions);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Prescription has been successfully sent to the pharmacy!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Fetch patients for the dropdown
$patients_sql = "SELECT patientID, CONCAT(first_name, ' ', last_name) AS fullName FROM patients";
$patients_result = $conn->query($patients_sql);

// Fetch doctors for the dropdown
$doctors_sql = "SELECT doctorID, CONCAT(firstName, ' ', middleName, ' ', surname) AS fullName FROM doctors";
$doctors_result = $conn->query($doctors_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 30px;
            max-width: 800px;
        }
        .card {
            border-radius: 15px;
            padding: 20px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .form-control {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 15px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow">
        <h2 class="text-center mb-4">Create Prescription</h2>
        <form method="POST" action="prescription.php">
    <div class="form-group">
        <label for="patient_id">Patient Name:</label>
        <select class="form-control" id="patient_id" name="patient_id" required>
            <option value="" disabled selected>Select Patient</option>
            <?php
            // Loop through patients and populate the dropdown
            while ($patient = $patients_result->fetch_assoc()) {
                echo "<option value='" . $patient['patientID'] . "'>" . $patient['fullName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="doctor_id">Doctor Name:</label>
        <select class="form-control" id="doctor_id" name="doctor_id" required>
            <option value="" disabled selected>Select Doctor</option>
            <?php
            // Loop through doctors and populate the dropdown
            while ($doctor = $doctors_result->fetch_assoc()) {
                echo "<option value='" . $doctor['doctorID'] . "'>" . $doctor['fullName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="medicine_name">Medicine Name:</label>
        <input type="text" class="form-control" id="medicine_name" name="medicine_name" required>
    </div>
    <div class="form-group">
        <label for="dosage">Dosage:</label>
        <input type="text" class="form-control" id="dosage" name="dosage" required>
    </div>
    <div class="form-group">
        <label for="instructions">Instructions:</label>
        <textarea class="form-control" id="instructions" name="instructions" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary btn-block" name="submit_prescription">Send Prescription</button>

    <!-- Button to navigate to the prescription list -->
    <a href="prescriptionlist.php" class="btn btn-secondary btn-block mt-3">View Prescription List</a>
</form>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Close the connection after processing the form
$conn->close();
?>       
