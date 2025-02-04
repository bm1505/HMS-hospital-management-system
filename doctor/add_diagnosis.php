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

// Handle form submission for diagnosis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the form data is being passed
    $diagnosis = isset($_POST['diagnosis']) ? trim($_POST['diagnosis']) : '';
    $other_notes = isset($_POST['other_notes']) ? trim($_POST['other_notes']) : '';
    $doctorID = $_SESSION['doctorID'];
    // Fetch lab_tests and medications from POST data, use empty string if not set
    $lab_tests = isset($_POST['lab_tests']) ? trim($_POST['lab_tests']) : '';
    $medications = isset($_POST['medications']) ? trim($_POST['medications']) : '';

    if (empty($diagnosis)) {
        $error_message = "Diagnosis field cannot be empty.";
    } else {
        // Insert diagnosis into the database
        $stmt = $conn->prepare("INSERT INTO diagnoses (patientID, doctorID, diagnosis, medications, lab_tests, other_notes, test_request_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Error preparing query: " . $conn->error);
            die("Error preparing query: " . $conn->error);
        }

        $status = 'Pending';  // Default status
        $created_at = date("Y-m-d H:i:s"); // Current date and time
        $stmt->bind_param("iissssss", $patientID, $doctorID, $diagnosis, $medications, $lab_tests, $other_notes, $status, $created_at);

        if ($stmt->execute()) {
            $success_message = "Diagnosis successfully recorded!";
            // Redirect to send_request.php after successful diagnosis
            header("Location: send_request.php?patientID=" . $patientID);
            exit(); // Make sure to stop further execution after redirect
        } else {
            $error_message = "Error saving diagnosis: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch patient details
$patient_query = $conn->prepare("SELECT first_name, last_name FROM patients WHERE patientID = ?");
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
        /* Your styles here */
    </style>
</head>
<body>

<div class="navbar-custom">
    <a href="doctor.php">🏥 Dashboard</a>
    <a href="../logout.php" class="float-end me-4">🚪 Logout</a>
</div>

<div class="container">
    <div class="card p-4">
        <h2 class="header-title">Add Diagnosis</h2>
        <hr>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success_message) ?> </div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error_message) ?> </div>
        <?php endif; ?>

        <div class="patient-info">
            <h4>Patient: <?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?> (ID: <?= $patientID ?>)</h4>
            <a href="treatment.php?patientID=<?= $patientID ?>" class="btn btn-custom">View Treatment History</a>
        </div>

        <!-- Diagnosis Form -->
        <form id="diagnosisForm" method="post">
            <div class="mb-3">
                <label for="diagnosis" class="form-label">Diagnosis <span class="text-danger">*</span></label>
                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
            </div>
           
            <div class="mb-3">
                <label for="lab_tests" class="form-label">Order Laboratory Tests</label>
                <textarea class="form-control" id="lab_tests" name="lab_tests" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label for="other_notes" class="form-label">Other Notes</label>
                <textarea class="form-control" id="other_notes" name="other_notes" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-custom w-100">Save Diagnosis</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
    .btn-custom {
        background: #007bff;
        color: white;
        font-weight: bold;
        border-radius: 8px;
        padding: 10px 20px;
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
    .patient-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .patient-info h4 {
        margin: 0;
    }
</style>
