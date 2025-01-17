<?php
session_start(); // Start the session

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle patient status update to 'done'
if (isset($_GET['remove_patientID'])) {
    $patientID = $_GET['remove_patientID'];
    $update_query = "UPDATE patient_treatment SET status = 'done' WHERE patientID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $patientID);

    if ($stmt->execute()) {
        $message = "Patient status updated to 'done'!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch patients sent to the nurse
$patients_query = "
    SELECT p.patientID, p.first_name, p.last_name, p.dateofBirth, p.gender, p.phone, 
           p.email, p.address, p.insurance_number, p.emergency_contact, p.relationship, 
           p.doctor_type
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
        }
        h1, h4 {
            color: #343a40;
            text-align: center;
        }
        .table {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .btn-action {
            background-color: #28a745;
            color: white;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .btn-action:hover {
            background-color: #218838;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .alert {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Nurse Dashboard</h1>
    <hr>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    
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
