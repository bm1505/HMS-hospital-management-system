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

// Fetch patient ID from GET request
if (!isset($_GET['patientID']) || empty($_GET['patientID'])) {
    die("Invalid patient ID.");
}

$patientID = intval($_GET['patientID']);
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diagnosis = trim($_POST['diagnosis']);
    $other_notes = trim($_POST['other_notes']);
    $doctorID = $_SESSION['doctorID'];

    if (empty($diagnosis)) {
        $error_message = "Diagnosis field cannot be empty.";
    } else {
        // Insert diagnosis into the database
        $stmt = $conn->prepare("UPDATE patient_vitals SET diagnosis = ?, other_notes = ? WHERE patientID = ?");
        
        if ($stmt === false) {
            $error_message = "Error preparing the SQL query: " . $conn->error;
        } else {
            $stmt->bind_param("ssi", $diagnosis, $other_notes, $patientID);

            if ($stmt->execute()) {
                $success_message = "Diagnosis successfully added!";
            } else {
                $error_message = "Error executing the query: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch patient details
$patient_query = $conn->prepare("SELECT first_name, last_name FROM patients WHERE patientID = ?");
if ($patient_query === false) {
    die("Error preparing SQL query for patient details: " . $conn->error);
}

$patient_query->bind_param("i", $patientID);
$patient_query->execute();
$patient_result = $patient_query->get_result();
$patient = $patient_result->fetch_assoc();
$patient_query->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Diagnosis - St. Norbert Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
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
        .btn-custom {
            background: #007bff;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px 20px;
            transition: 0.3s ease;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
        .header-title {
            color: #007bff;
            font-weight: bold;
            text-align: center;
            font-size: 28px;
        }
        .alert-custom {
            border-radius: 8px;
            font-weight: bold;
        }
        .navbar-custom {
            background-color: #007bff;
            padding: 15px;
            border-radius: 0px 0px 12px 12px;
        }
        .navbar-custom a {
            color: white;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<div class="navbar-custom text-center">
    <a href="doctor.php">🏥 Dashboard</a>
    <a href="../logout.php" class="float-end me-4">🚪 Logout</a>
</div>

<!-- Main Container -->
<div class="container">
    <div class="card p-4">
        <h2 class="header-title">Add Diagnosis</h2>
        <hr>

        <!-- Success & Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-custom"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Patient Details -->
        <h4 class="mb-3">Patient: <?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?> (ID: <?= $patientID ?>)</h4>

        <!-- Diagnosis Form -->
        <form method="post">
            <div class="mb-3">
                <label for="diagnosis" class="form-label">Diagnosis <span class="text-danger">*</span></label>
                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required placeholder="Enter patient diagnosis..."></textarea>
            </div>
            <div class="mb-3">
                <label for="other_notes" class="form-label">Other Notes</label>
                <textarea class="form-control" id="other_notes" name="other_notes" rows="2" placeholder="Additional notes..."></textarea>
            </div>
            <button type="submit" class="btn btn-custom w-100">Save Diagnosis</button>
        </form>

        <div class="text-center mt-4">
            <a href="view_patients.php" class="btn btn-outline-primary">← Back to Patients</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
