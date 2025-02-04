<?php
session_start();

if (!isset($_SESSION['doctorID'])) {
    die("Error: Unauthorized access.");
}

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

// Fetch patient ID
if (!isset($_GET['patientID']) || empty($_GET['patientID'])) {
    die("Invalid patient ID.");
}
$patientID = intval($_GET['patientID']);

// Fetch patient details
$patient_query = $conn->prepare("SELECT first_name, last_name FROM patients WHERE patientID = ?");
$patient_query->bind_param("i", $patientID);
$patient_query->execute();
$patient_result = $patient_query->get_result();
$patient = $patient_result->fetch_assoc();
$patient_query->close();

// Fetch treatment history
$history_query = $conn->prepare("SELECT diagnosis, medications, lab_tests, other_notes, created_at FROM diagnoses WHERE patientID = ? ORDER BY created_at DESC");
$history_query->bind_param("i", $patientID);
$history_query->execute();
$history_result = $history_query->get_result();
$treatment_history = $history_result->fetch_all(MYSQLI_ASSOC);
$history_query->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment History - St. Norbert Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd, #ffffff);
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header-title {
            color: #007bff;
            font-weight: bold;
            text-align: center;
            font-size: 28px;
        }
        .navbar-custom {
            background-color: #007bff;
            padding: 15px;
            text-align: center;
        }
        .navbar-custom a {
            color: white;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }
        .table-custom {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table-custom th {
            background-color: #007bff;
            color: white;
        }
        .table-custom tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="navbar-custom">
    <a href="doctor.php">🏥 Dashboard</a>
    <a href="add_diagnosis.php?patientID=<?= $patientID ?>" class="float-start ms-4">🔙 Back</a>
    <a href="../logout.php" class="float-end me-4">🚪 Logout</a>
</div>

<div class="container">
    <div class="card p-4">
        <h2 class="header-title">Treatment History</h2>
        <hr>
        <h4>Patient: <?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?> (ID: <?= $patientID ?>)</h4>

        <?php if (!empty($treatment_history)): ?>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Diagnosis</th>
                        <th>Medications</th>
                        <th>Lab Tests</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($treatment_history as $treatment): ?>
                        <tr>
                            <td><?= htmlspecialchars($treatment['created_at']) ?></td>
                            <td><?= htmlspecialchars($treatment['diagnosis']) ?></td>
                            <td><?= htmlspecialchars($treatment['medications']) ?></td>
                            <td><?= htmlspecialchars($treatment['lab_tests']) ?></td>
                            <td><?= htmlspecialchars($treatment['other_notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No treatment history available for this patient.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
