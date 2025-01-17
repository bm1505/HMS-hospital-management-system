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

// Insert into doctor_assignments table
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
    
    // Insert into doctor_assignments table
 // Assign values to variables first
 $first_name = $patient['first_name'] ?? '';
 $last_name = $patient['last_name'] ?? '';
 $dateOfBirth = $patient['dateOfBirth'] ?? '';
 $gender = $patient['gender'] ?? '';
 $phone = $patient['phone'] ?? '';
 $email = $patient['email'] ?? '';
 $insurance_number = $patient['insurance_number'] ?? '';
 $doctor_type = $patient['doctor_type'] ?? '';
 $emergency_contact = $patient['emergency_contact'] ?? '';
 $weight = $patient['weight'] ?? '';
 $blood_pressure = $patient['blood_pressure'] ?? '';
 $temperature = $patient['temperature'] ?? '';
 $height = $patient['height'] ?? '';
 $other_notes = $patient['other_notes'] ?? '';

 // Prepare the query for inserting doctor assignment
 $insert_assignment_query = "INSERT INTO doctor_assignments 
     (patientID, doctorID, firstName, lastName, dateOfBirth, gender, phone, email, insuranceNumber, date_assigned, emergency_contact, weight, blood_pressure, temperature, height, doctor_type, other_notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

 $insert_stmt = $conn->prepare($insert_assignment_query);

 // Bind the parameters
 $insert_stmt->bind_param(
     "iissssssssssssss",  // 17 parameters
     $patientID,
     $doctorID,
     $first_name,
     $last_name,
     $dateOfBirth,
     $gender,
     $phone,
     $email,
     $insurance_number,
     $emergency_contact,
     $weight,
     $blood_pressure,
     $temperature,
     $height,
     $doctor_type,
     $other_notes
 );

 // Execute the query
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

// Handle Remove Patient
if (isset($_POST['remove_patient'])) {
    $patientID = $_POST['patientID'];

    // Delete the patient from the patient_vitals table
    $remove_query = "DELETE FROM patient_vitals WHERE patientID = ?";
    $stmt = $conn->prepare($remove_query);
    $stmt->bind_param("i", $patientID);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Failed to remove patient');</script>";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Vitals</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script>
        function removeRow(button, patientID) {
            var row = button.closest('tr');
            row.style.display = 'none';

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            var patientIDField = document.createElement('input');
            patientIDField.type = 'hidden';
            patientIDField.name = 'patientID';
            patientIDField.value = patientID;
            form.appendChild(patientIDField);

            var removePatientField = document.createElement('input');
            removePatientField.type = 'hidden';
            removePatientField.name = 'remove_patient';
            removePatientField.value = '1';
            form.appendChild(removePatientField);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between">
        <a href="nurse_dashboard.php" class="btn btn-outline-primary">Back</a>
        <a href="index.php" class="btn btn-outline-danger">Logout</a>
    </div>

    <h1 class="text-center mt-4 mb-4">Patient Vitals</h1>
    <hr>

    <div class="card p-4 mt-4">
        <h4 class="mb-3">Recorded Vitals</h4>
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
                    <th>Doctor Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $vitals_query = "
                    SELECT 
                        pv.*, 
                        p.first_name, 
                        p.last_name, 
                        p.dateOfBirth, 
                        p.gender, 
                        p.phone, 
                        p.insurance_number, 
                        p.emergency_contact,
                        p.doctor_type,
                        pv.doctorID
                    FROM 
                        patient_vitals pv
                    JOIN 
                        patients p 
                    ON 
                        pv.patientID = p.patientID 
                    ORDER BY 
                        pv.created_at DESC";
                $vitals_result = $conn->query($vitals_query);

                if ($vitals_result && $vitals_result->num_rows > 0):
                    while ($vital = $vitals_result->fetch_assoc()):
                ?>
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
                        <td><?= htmlspecialchars($vital['other_notes']) ?></td>
                        <td><?= htmlspecialchars($vital['doctor_type']) ?></td>
                        <td>
                            <?php if (empty($vital['doctorID'])): ?>
                                <div class="card p-4">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="doctorID" class="form-label">Select Doctor</label>
                                            <select name="doctorID" class="form-select" id="doctorID">
                                                <option value="" disabled selected>Select Doctor</option>
                                                <?php foreach ($doctors as $doctor): ?>
                                                    <option value="<?= htmlspecialchars($doctor['doctorID']) ?>">
                                                        <?= htmlspecialchars($doctor['firstName'] . ' ' . $doctor['middleName'] . ' ' . $doctor['surname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="patientID" value="<?= htmlspecialchars($vital['patientID']) ?>">
                                        </div>
                                        <button type="submit" name="assign_doctor" class="btn btn-primary">Assign Doctor</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <p>Doctor Assigned</p>
                            <?php endif; ?>

                            <button type="button" class="btn btn-danger mt-2" onclick="removeRow(this, <?= htmlspecialchars($vital['patientID']) ?>)">Remove</button>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="14" class="text-center">No vitals recorded yet.</td>
                    </tr>
                <?php
                endif;
                ?>
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
        border: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }
    .btn-outline-primary {
        border-color: #007bff;
        color: #007bff;
    }
    .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
    }
</style>
