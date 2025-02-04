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
$lab_tests = isset($_GET['lab_tests']) ? urldecode($_GET['lab_tests']) : ''; // Initialized lab_tests variable
$test_details = ''; // Initialize test_details variable

if (isset($_POST['send_test_request'])) {
    $test_details = isset($_POST['test_details']) ? $_POST['test_details'] : ''; // Ensure test details are set
    error_log("Test Details: " . $test_details);
    error_log("Lab Tests: " . $lab_tests);
}

$success_message = "";
$error_message = "";

// Insert test request when "Send Test Request" is clicked
if (isset($_POST['send_test_request'])) {
    $test_request_status = 'Pending';
    $test_date = date("Y-m-d H:i:s"); // Set the current date and time for the test request

    // Prepare the query to insert test details
    $test_request_stmt = $conn->prepare("INSERT INTO lab_requests (patientID, doctorID, lab_tests, testDate, test_details, test_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($test_request_stmt === false) {
        error_log("Error preparing query: " . $conn->error);
        die("Error preparing query: " . $conn->error);
    }

    // Bind the parameters (patientID, doctorID, lab_tests, testDate, test_details, status, created_at)
    $created_at = date("Y-m-d H:i:s");
    $test_request_stmt->bind_param("iisssss", $patientID, $_SESSION['doctorID'], $lab_tests, $test_date, $test_details, $test_request_status, $created_at);

    if ($test_request_stmt->execute()) {
        $success_message = "Test request successfully submitted!";
    } else {
        $error_message = "Error submitting test request: " . $test_request_stmt->error;
    }

    $test_request_stmt->close();
}

// Fetch patient details
$patient_query = $conn->prepare("SELECT first_name, last_name FROM patients WHERE patientID = ?");
$patient_query->bind_param("i", $patientID);
$patient_query->execute();
$patient_result = $patient_query->get_result();
$patient = $patient_result->fetch_assoc();
$patient_query->close();

// Fetch diagnosis details
$diagnosis_query = $conn->prepare("SELECT diagnosis, lab_tests, other_notes FROM diagnoses WHERE patientID = ?");
$diagnosis_query->bind_param("i", $patientID);
$diagnosis_query->execute();
$diagnosis_result = $diagnosis_query->get_result();
$diagnosis = $diagnosis_result->fetch_assoc();
$diagnosis_query->close();

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Test Request - St. Norbert Hospital</title>
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
        <h2 class="header-title">Send Test Request</h2>
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

        <!-- Table to display after form submission -->
        <div id="patientTable">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Diagnosis</th>
                        <th>Lab Test</th>
                        <th>Other Notes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?></td>
                        <td><?= htmlspecialchars($diagnosis['diagnosis']) ?></td>
                        <td><?= htmlspecialchars($diagnosis['lab_tests']) ?></td>
                        <td><?= htmlspecialchars($diagnosis['other_notes']) ?></td>
                        <td id="testStatus">Pending</td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="test_details" value="<?= htmlspecialchars($diagnosis['lab_tests']) ?>">
                                <button type="submit" name="send_test_request" class="btn btn-custom">Send Test Request</button>
                            </form>
                            <button id="removeRequestBtn" class="btn btn-danger hidden" onclick="removeTestRequest()">Remove Request</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function removeTestRequest() {
        alert('Test request removed!');
        document.getElementById('removeRequestBtn').classList.add('hidden');
    }
</script>
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
        .hidden {
            display: none;
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