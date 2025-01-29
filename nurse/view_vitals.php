<?php 
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Assign Doctor
if (isset($_POST['assign_doctor']) && !empty($_POST['doctorID']) && !empty($_POST['patientID'])) {
    $doctorID = intval($_POST['doctorID']);
    $patientID = intval($_POST['patientID']);

    $conn->begin_transaction(); // Start Transaction

    // Check if patient already has a doctor
    $check_query = "SELECT doctorID FROM patient_vitals WHERE patientID = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();

    if ($patient && $patient['doctorID']) {
        echo "<script>alert('Doctor already assigned to this patient.');</script>";
    } else {
        // Assign doctor
        $assign_query = "UPDATE patient_vitals SET doctorID = ? WHERE patientID = ?";
        $stmt = $conn->prepare($assign_query);
        $stmt->bind_param("ii", $doctorID, $patientID);

        if ($stmt->execute()) {
            $conn->commit(); // Commit transaction
            echo "<script>alert('Doctor assigned successfully.');</script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $conn->rollback(); // Rollback on failure
            echo "<script>alert('Failed to assign doctor.');</script>";
        }
    }
    $stmt->close();
}

// Fetch doctors for dropdown
$doctor_query = "SELECT doctorID, CONCAT(firstName, ' ', middleName, ' ', surname) AS full_name, specialization FROM doctors";
$doctor_result = $conn->query($doctor_query);
$doctors = $doctor_result->fetch_all(MYSQLI_ASSOC);

// Fetch patient vitals
$vitals_query = "SELECT pv.*, p.first_name, p.last_name, p.dateOfBirth, p.gender, p.phone, p.insurance_number, p.emergency_contact, p.doctor_type 
                 FROM patient_vitals pv 
                 JOIN patients p ON pv.patientID = p.patientID 
                 ORDER BY pv.created_at DESC";
$vitals_result = $conn->query($vitals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Doctor to Patients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mb-4">Patient Vitals & Doctor Assignment</h1>
    <div class="card p-4 mt-3">
        <h4>Recorded Vitals</h4>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Insurance</th>
                    <th>Emergency Contact</th>
                    <th>Weight (kg)</th>
                    <th>BP</th>
                    <th>Temp (°C)</th>
                    <th>Height (cm)</th>
                    <th>Doctor Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vital = $vitals_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($vital['patientID']) ?></td>
                        <td><?= htmlspecialchars($vital['first_name'] . ' ' . $vital['last_name']) ?></td>
                        <td><?= htmlspecialchars($vital['dateOfBirth']) ?></td>
                        <td><?= htmlspecialchars($vital['gender']) ?></td>
                        <td><?= htmlspecialchars($vital['phone']) ?></td>
                        <td><?= htmlspecialchars($vital['insurance_number']) ?></td>
                        <td><?= htmlspecialchars($vital['emergency_contact']) ?></td>
                        <td><?= htmlspecialchars($vital['weight']) ?></td>
                        <td><?= htmlspecialchars($vital['blood_pressure']) ?></td>
                        <td><?= htmlspecialchars($vital['temperature']) ?></td>
                        <td><?= htmlspecialchars($vital['height']) ?></td>
                        <td><?= htmlspecialchars($vital['doctor_type']) ?></td>
                        <td>
                            <?php if (empty($vital['doctorID'])): ?>
                                <form method="POST" class="d-flex">
                                    <select name="doctorID" class="form-select" required>
                                        <option value="" disabled selected>Select Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['doctorID'] ?>">
                                                <?= htmlspecialchars($doctor['full_name']) ?> (<?= htmlspecialchars($doctor['specialization']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="patientID" value="<?= htmlspecialchars($vital['patientID']) ?>">
                                    <button type="submit" name="assign_doctor" class="btn btn-primary ms-2">Assign</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Doctor Assigned</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }
</style>
