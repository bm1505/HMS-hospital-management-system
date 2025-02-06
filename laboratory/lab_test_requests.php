<?php
session_start();


$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $labRequestID = $_POST['labRequestID'];
    $status = $_POST['status'];

    $sql = "UPDATE lab_requests SET test_status = ? WHERE labRequestID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $labRequestID);
    $stmt->execute();
    $stmt->close();
}

// Handle sample addition
// Handle sample addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_sample'])) {
    $labRequestID = $_POST['labRequestID'];

    // Update sample status
    $sql = "UPDATE lab_requests SET sample_added = 1 WHERE labRequestID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $labRequestID);
    $stmt->execute();
    $stmt->close();

    // Get patient ID and name
    $sql = "SELECT p.patientID, p.first_name, p.last_name 
            FROM lab_requests lr
            JOIN patients p ON lr.patientID = p.patientID
            WHERE lr.labRequestID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $labRequestID);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $stmt->close();

    $patientID = $patient['patientID'];
    $patientName = urlencode($patient['first_name'] . ' ' . $patient['last_name']);

    // Redirect with patientID included
    header("Location: sample_management.php?labRequestID=$labRequestID&patientID=$patientID&patientName=$patientName");
    exit();
}

// Fetch lab test requests with patient and doctor names
$sql = "SELECT lr.*, 
        p.first_name AS patient_first, p.last_name AS patient_last,
        d.firstName AS doctor_first, d.middleName AS doctor_last 
        FROM lab_requests lr
        LEFT JOIN patients p ON lr.patientID = p.patientID
        LEFT JOIN doctors d ON lr.doctorID = d.doctorID";

$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Test Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Biome+W01+Regular&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-custom bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="laboratory.php">🔬 Lab Dashboard</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-light btn-sm">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4 lab-title">Laboratory Test Requests</h2>
    
    <div class="table-responsive lab-table">
        <table class="table table-hover table-bordered">
        <thead class="bg-lab-primary text-white">
    <tr>
        <th>Request ID</th>
        <th>Patient ID</th> <!-- Added Patient ID column -->
        <th>Patient Name</th>
        <th>Doctor Name</th>
        <th>Tests Requested</th>
        <th>Test Date</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>
</thead>

            <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['labRequestID']) ?></td>
        <td><?= htmlspecialchars($row['patientID']) ?></td> <!-- Displaying Patient ID -->
        <td class="patient-name">
            <?= htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']) ?>
        </td>
        <td class="doctor-name">
            <?= htmlspecialchars($row['doctor_first'] . ' ' . $row['doctor_last']) ?>
        </td>
        <td><?= htmlspecialchars($row['test_details']) ?></td>
        <td><?= htmlspecialchars($row['testDate']) ?></td>
        <td>
            <form method="POST" class="d-inline">
                <input type="hidden" name="labRequestID" value="<?= $row['labRequestID'] ?>">
                <select name="status" class="form-select status-select" onchange="this.form.submit()">
                    <option value="Under Treatment" <?= $row['test_status'] == 'Under Treatment' ? 'selected' : '' ?>>Under Treatment</option>
                    <option value="Done and Completed" <?= $row['test_status'] == 'Done and Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
                <input type="hidden" name="update_status" value="1">
            </form>
        </td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
            <form method="POST" class="d-inline">
                <input type="hidden" name="labRequestID" value="<?= $row['labRequestID'] ?>">
                <button type="submit" name="add_sample" class="btn btn-lab-action btn-sm">
                    <i class="bi bi-vial"></i> Add Sample
                </button>
            </form>
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
    :root {
        --lab-primary: #2A5C82;
        --lab-secondary: #5DA9E9;
        --lab-accent: #FF6B6B;
    }

    body {
        background:rgb(157, 199, 241);
        font-family: 'Roboto Condensed', sans-serif;
    }

    .navbar-custom {
        padding: 0.8rem 1rem;
    }

    .lab-title {
        color: var(--lab-primary);
        font-family: 'Biome W01 Regular', 'Roboto Condensed', sans-serif;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .lab-table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    .bg-lab-primary {
        background-color: var(--lab-primary) !important;
    }

    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }

    .status-select {
        width: 150px;
        border: 2px solid var(--lab-secondary);
        border-radius: 5px;
        font-weight: 500;
    }

    .btn-lab-action {
        background: var(--lab-accent);
        color: white;
        border-radius: 8px;
        padding: 5px 15px;
        transition: all 0.3s ease;
    }

    .btn-lab-action:hover {
        background: #ff5252;
        transform: translateY(-1px);
    }

    .patient-name, .doctor-name {
        font-weight: 500;
        color: var(--lab-primary);
    }

    @media (max-width: 768px) {
        .lab-title {
            font-size: 1.5rem;
        }
        
        .table-responsive {
            border: none;
        }
    }
</style>