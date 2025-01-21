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

// Fetch logged-in user's ID (assuming you have a user ID stored in the session)
$logged_in_userID = $_SESSION['doctorID']; // The logged-in user's ID (this needs to be set at login)

if (isset($_POST['assign_doctor']) && isset($_POST['doctorID']) && isset($_POST['patientID'])) {
    $doctorID = $_POST['doctorID'];
    $patientID = $_POST['patientID'];

    // Fetch patient details
    $patient_query = "SELECT * FROM patients WHERE patientID = ?";
    $stmt = $conn->prepare($patient_query);
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $patient_result = $stmt->get_result();
    $patient = $patient_result->fetch_assoc();

    // Prepare the query for inserting doctor assignment
    $insert_assignment_query = "INSERT INTO doctor_assignments 
     (patientID, doctorID, firstName, lastName, dateOfBirth, gender, phone, email, insuranceNumber, date_assigned, emergency_contact, weight, blood_pressure, temperature, height, doctor_type, other_notes, diagnosis)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_assignment_query);
    $insert_stmt->bind_param(
        "iissssssssssssss", // 17 parameters
        $patientID,
        $doctorID,
        $patient['first_name'],
        $patient['last_name'],
        $patient['dateOfBirth'],
        $patient['gender'],
        $patient['phone'],
        $patient['email'],
        $patient['insurance_number'],
        $patient['emergency_contact'],
        $patient['weight'],
        $patient['blood_pressure'],
        $patient['temperature'],
        $patient['height'],
        $patient['doctor_type'],
        $patient['other_notes']
    );
    $insert_stmt->execute();

    // Update the patient's assigned doctor
    $assign_query = "UPDATE patient_vitals SET doctorID = ? WHERE patientID = ?";
    $stmt = $conn->prepare($assign_query);
    $stmt->bind_param("ii", $doctorID, $patientID);
    if ($stmt->execute()) {
        // Successfully assigned doctor, reload the page to update the button visibility
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Failed to assign doctor');</script>";
    }
    $stmt->close();
}

// Fetch doctor details for dropdown
$doctor_query = "SELECT doctorID, firstName, middleName, surname, specialization FROM doctors";
$doctor_result = $conn->query($doctor_query);
$doctors = [];
if ($doctor_result && $doctor_result->num_rows > 0) {
    while ($row = $doctor_result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Fetch doctor assignments and diagnoses for the logged-in doctor
$diagnosis_query = "
    SELECT 
        da.patientID, 
        da.doctorID, 
        da.diagnosis, 
        p.first_name, 
        p.last_name, 
        p.dateOfBirth, 
        p.gender, 
        p.phone, 
        p.insurance_number, 
        p.emergency_contact,
        p.weight, 
        p.blood_pressure, 
        p.temperature, 
        p.height,
        p.other_notes
    FROM 
        doctor_assignments da
    JOIN 
        patients p 
    ON 
        da.patientID = p.patientID
    WHERE
        da.doctorID = ?  -- Filter by the logged-in doctor ID
    LIMIT 0, 25;
";

// Prepare the statement
$stmt = $conn->prepare($diagnosis_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $logged_in_userID); // Use the logged-in doctor's ID
$stmt->execute();
$diagnosis_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Diagnosis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between">
        <a href="doctor.php" class="btn btn-outline-primary">Back</a>
        <a href="../logout.php" class="btn btn-outline-danger">Logout</a>
    </div>

    <h1 class="text-center mt-4 mb-4">Patient Diagnoses</h1>
    <hr>

    <div class="card p-4 mt-4">
        <h4 class="mb-3">Doctor Assignments and Diagnoses</h4>
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
                    <th>Doctor Diagnosis</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($diagnosis_result && $diagnosis_result->num_rows > 0): ?>
                    <?php while ($diagnosis = $diagnosis_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($diagnosis['patientID']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['first_name'] . ' ' . $diagnosis['last_name']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['dateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['gender']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['phone']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['emergency_contact']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['weight']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['blood_pressure']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['temperature']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['height']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['other_notes']) ?></td>
                            <td><?= htmlspecialchars($diagnosis['diagnosis']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center">No diagnoses assigned yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
