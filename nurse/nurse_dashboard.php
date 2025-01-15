<?php
session_start();  // Ensure the session is started

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

// Handle remove patient (update status to 'done')
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

// Fetch patients sent to the nurse (status: 'under treatment')
$patients_query = "
    SELECT p.patientID, p.first_name, p.last_name, p.dateofBirth, p.gender, p.phone, p.email, p.address, p.insurance_number, p.emergency_contact, p.relationship, p.doctor_type
    FROM patients p
    JOIN patient_treatment pt ON p.patientID = pt.patientID
    WHERE pt.status = 'under treatment' 
    ORDER BY pt.sent_to_nurse DESC
";
$patients_result = $conn->query($patients_query);

// Check if the query was successful
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

    $stmt = $conn->prepare("
        INSERT INTO patient_vitals (patientID, weight, blood_pressure, temperature, height, other_notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iddsds", $patientID, $weight, $blood_pressure, $temperature, $height, $other_notes);

    if ($stmt->execute()) {
        // Mark patient as 'done' after adding vitals
        $update_query = "UPDATE patient_treatment SET status = 'done' WHERE patientID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $patientID);
        $update_stmt->execute();
        $update_stmt->close();

        // Prepare the data to send
        $patient_query = "SELECT * FROM patients WHERE patientID = ?";
        $patient_stmt = $conn->prepare($patient_query);
        $patient_stmt->bind_param("i", $patientID);
        $patient_stmt->execute();
        $patient_result = $patient_stmt->get_result();
        $patient_data = $patient_result->fetch_assoc();

        // Send the patient information to vital.hph
        $api_url = "http://vital.hph/api/receive_data"; // URL to which you want to send the data
        $data = [
            'patientID' => $patient_data['patientID'],
            'first_name' => $patient_data['first_name'],
            'last_name' => $patient_data['last_name'],
            'gender' => $patient_data['gender'],
            'phone' => $patient_data['phone'],
            'email' => $patient_data['email'],
            'address' => $patient_data['address'],
            'insurance_number' => $patient_data['insurance_number'],
            'emergency_contact' => $patient_data['emergency_contact'],
            'relationship' => $patient_data['relationship'],
            'doctor_type' => $patient_data['doctor_type'],
            'weight' => $weight,
            'blood_pressure' => $blood_pressure,
            'temperature' => $temperature,
            'height' => $height,
            'other_notes' => $other_notes
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($api_url, false, $context);

        if ($result === FALSE) {
            $message = "Error sending data to vital.hph";
        } else {
            $message = "Patient vitals recorded successfully and data sent to vital.hph.";
        }

    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch patient vitals
$vitals_query = "
    SELECT 
        pv.*, 
        p.first_name, 
        p.last_name 
    FROM 
        patient_vitals pv
    JOIN 
        patients p 
    ON 
        pv.patientID = p.patientID 
    ORDER BY 
        pv.created_at DESC
";
$vitals_result = $conn->query($vitals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fc;
        }
        .container {
            max-width: 1200px;
        }
        h1, h4 {
            color: #343a40;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .alert {
            font-size: 0.9rem;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-action {
            background-color: #28a745;
            color: white;
            font-size: 14px;
        }
        .btn-action:hover {
            background-color: #218838;
        }
        .btn-done {
            background-color: #6c757d;
            color: white;
            font-size: 14px;
        }
        .btn-done:hover {
            background-color: #5a6268;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            font-size: 14px;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .action-btn-container {
            text-align: center;
        }
        .left-column {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .right-column {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Nurse Dashboard</h1>
    <hr>

    <!-- Display message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Display list of patients sent to nurse -->
    <div class="mb-4">
        <h4>Patients Sent to Nurse</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Email Address</th>
                    <th>Address</th>
                    <th>Insurance Number</th>
                    <th>Emergency Contact</th>
                    <th>Relationship with Emergency Contact</th>
                    <th>Doctor Type</th>
                    <th>Action</th>
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
                            <td><?= htmlspecialchars($patient['address']) ?></td>
                            <td><?= htmlspecialchars($patient['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($patient['emergency_contact']) ?></td>
                            <td><?= htmlspecialchars($patient['relationship']) ?></td>
                            <td><?= htmlspecialchars($patient['doctor_type']) ?></td>
                            <td class="action-btn-container">
                                <a href="?remove_patientID=<?= $patient['patientID'] ?>" class="btn btn-remove">Remove</a>
                                <a href="vital.php?patientID=<?= $patient['patientID'] ?>&first_name=<?= urlencode($patient['first_name']) ?>&last_name=<?= urlencode($patient['last_name']) ?>&doctor_type=<?= urlencode($patient['doctor_type']) ?>&insurance_number=<?= urlencode($patient['insurance_number']) ?>&gender=<?= urlencode($patient['gender']) ?>" class="btn btn-action">Add Vital</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">No patients found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
