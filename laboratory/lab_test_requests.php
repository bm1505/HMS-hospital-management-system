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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $labRequestID = $_POST['labRequestID'];
    $status = $_POST['status'];

    $sql = "UPDATE lab_requests SET test_status = ? WHERE labRequestID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $labRequestID);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();
}

// Handle sample addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_sample'])) {
    $labRequestID = $_POST['labRequestID'];

    $sql = "UPDATE lab_requests SET sample_added = 1 WHERE labRequestID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $labRequestID);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // Redirect to sample_management.php
    header("Location: sample_management.php?labRequestID=" . $labRequestID);
    exit();
}

// Fetch lab test requests
$sql = "SELECT * FROM lab_requests";
$result = $conn->query($sql);

// Fetch patient details
if (isset($_GET['patientID'])) {
    $patientID = $_GET['patientID'];
    $sql = "SELECT first_name, last_name FROM patients WHERE patientID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);

    if ($stmt->fetch()) {
        echo $first_name . ' ' . $last_name;
    } else {
        echo "Patient Not Found";
    }
    $stmt->close();
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
</head>
<body>
<div class="navbar-custom">
    <a href="laboratory.php">🏥 Dashboard</a>
    <a href="../logout.php" class="float-end me-4">🚪 Logout</a>
</div>

<div class="container">
    <h2 class="header-title text-center my-4">Lab Test Requests</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Patient ID</th>
                <th>Doctor ID</th>
                <th>Lab Tests</th>
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
                <td><?= htmlspecialchars($row['patientID']) ?></td>
                <td><?= htmlspecialchars($row['doctorID']) ?></td>
                <td><?= htmlspecialchars($row['test_details']) ?></td>
                <td><?= htmlspecialchars($row['testDate']) ?></td>
                <td><?= htmlspecialchars($row['test_status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="labRequestID" value="<?= $row['labRequestID'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="Under Treatment" <?= $row['test_status'] == 'Under Treatment' ? 'selected' : '' ?>>Under Treatment</option>
                            <option value="Done and Completed" <?= $row['test_status'] == 'Done and Completed' ? 'selected' : '' ?>>Done and Completed</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="labRequestID" value="<?= $row['labRequestID'] ?>">
                        <button type="submit" name="add_sample" class="btn btn-success btn-sm">Add Sample</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
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