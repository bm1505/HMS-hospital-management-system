<?php
session_start(); // Start session to access session variables

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in user's ID
$logged_in_userID = $_SESSION['doctorID']; // Ensure doctorID is stored in the session

// Fetch patient vitals including diagnosis and other notes
$diagnosis_query = "
    SELECT 
        pv.patientID, 
        pv.weight, 
        pv.blood_pressure, 
        pv.temperature, 
        pv.height, 
        pv.other_notes, 
       
        p.first_name, 
        p.last_name, 
        p.dateOfBirth, 
        p.gender, 
        p.phone, 
        p.insurance_number, 
        p.emergency_contact 
    FROM 
        patient_vitals pv
    JOIN 
        patients p 
    ON 
        pv.patientID = p.patientID
    WHERE
        pv.doctorID = ?
    LIMIT 25;
";

// Prepare the statement
$stmt = $conn->prepare($diagnosis_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $logged_in_userID);
$stmt->execute();
$diagnosis_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Vitals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between">
        <a href="doctor.php" class="btn btn-outline-primary">Back</a>
        <a href="../logout.php" class="btn btn-outline-danger">Logout</a>
    </div>

    <h1 class="text-center mt-4 mb-4">Patient Vitals</h1>
    <hr>

    <div class="card p-4 mt-4">
        <h4 class="mb-3">Vitals Assigned to You</h4>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Insurance Number</th>
                    <th>Emergency Contact</th>
                    <th>Weight (kg)</th>
                    <th>Blood Pressure</th>
                    <th>Temperature (°C)</th>
                    <th>Height (cm)</th>
                    <th>Other Notes</th>
                    
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($diagnosis_result && $diagnosis_result->num_rows > 0): ?>
                    <?php while ($vitals = $diagnosis_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($vitals['patientID']) ?></td>
                            <td><?= htmlspecialchars($vitals['first_name'] . ' ' . $vitals['last_name']) ?></td>
                            <td><?= htmlspecialchars($vitals['dateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($vitals['gender']) ?></td>
                            <td><?= htmlspecialchars($vitals['phone']) ?></td>
                            <td><?= htmlspecialchars($vitals['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($vitals['emergency_contact']) ?></td>
                            <td><?= htmlspecialchars($vitals['weight']) ?></td>
                            <td><?= htmlspecialchars($vitals['blood_pressure']) ?></td>
                            <td><?= htmlspecialchars($vitals['temperature']) ?></td>
                            <td><?= htmlspecialchars($vitals['height']) ?></td>
                            <td><?= htmlspecialchars($vitals['other_notes'] ?? 'N/A') ?></td>
                            
                            <td>
                                <a href="add_diagnosis.php?patientID=<?= $vitals['patientID'] ?>" class="btn btn-success">Diagnosis</a>
                                <a href="remove_patient.php?patientID=<?= $vitals['patientID'] ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center">No vitals assigned yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
