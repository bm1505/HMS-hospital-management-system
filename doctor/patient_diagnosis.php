<?php
session_start(); // Start the session to access the logged-in doctorID

// Check if the doctorID exists in the session
if (!isset($_SESSION['doctorID'])) {
    die("Error: You must be logged in to view this page.");
}

// Assuming the doctor’s ID is stored in the session
$doctorID = $_SESSION['doctorID'];

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a connection to the database
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch patient details assigned to the logged-in doctor
$patient_query = "SELECT a.assignmentID, p.patientID, p.first_name, p.last_name, p.dateOfBirth, p.gender, p.phone, p.email, 
                         p.insurance_number, a.date_assigned, p.emergency_contact, p.weight, p.blood_pressure, p.temperature, 
                         p.height, p.doctor_type, p.other_notes
                  FROM doctor_assignments a
                  JOIN patients p ON a.patientID = p.patientID
                  WHERE a.doctorID = ?";
$stmt = $conn->prepare($patient_query);

if ($stmt === false) {
    die("Error in SQL query preparation: " . $conn->error);
}

$stmt->bind_param("i", $doctorID);
$stmt->execute();
$patient_result = $stmt->get_result();

$patients = [];
if ($patient_result && $patient_result->num_rows > 0) {
    while ($row = $patient_result->fetch_assoc()) {
        $patients[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assigned Patients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f5f7;
        }
        h1 {
            font-weight: 600;
            color: #007bff;
        }
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mt-4 mb-4">Assigned Patients</h1>
    <hr>

    <div class="card p-4 mt-4">
        <h4 class="mb-3">Patient List</h4>
        <?php if (empty($patients)): ?>
            <p>No patients assigned to you.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Date of Birth</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Insurance Number</th>
                        <th>Date Assigned</th>
                        <th>Emergency Contact</th>
                        <th>Weight</th>
                        <th>Blood Pressure</th>
                        <th>Temperature</th>
                        <th>Height</th>
                        <th>Doctor Type</th>
                        <th>Other Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                            <td><?= htmlspecialchars($patient['dateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($patient['phone']) ?></td>
                            <td><?= htmlspecialchars($patient['email']) ?></td>
                            <td><?= htmlspecialchars($patient['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($patient['date_assigned']) ?></td>
                            <td><?= htmlspecialchars($patient['emergency_contact']) ?></td>
                            <td><?= htmlspecialchars($patient['weight']) ?></td>
                            <td><?= htmlspecialchars($patient['blood_pressure']) ?></td>
                            <td><?= htmlspecialchars($patient['temperature']) ?></td>
                            <td><?= htmlspecialchars($patient['height']) ?></td>
                            <td><?= htmlspecialchars($patient['doctor_type']) ?></td>
                            <td><?= htmlspecialchars($patient['other_notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
