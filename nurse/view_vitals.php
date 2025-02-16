<?php
session_start();

// Database connection details
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Assign Doctor
if (isset($_POST['assign_doctor'])) {
    $doctorID  = intval($_POST['doctorID']);
    $patientID = intval($_POST['patientID']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if patient already has a doctor
        $check_query = "SELECT doctorID FROM patient_vitals WHERE patientID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $result  = $stmt->get_result();
        $patient = $result->fetch_assoc();

        if ($patient && $patient['doctorID']) {
            echo "<script>alert('Doctor already assigned to this patient.');</script>";
        } else {
            // Set removal time to 2 minutes ahead
            $removeTime = date('Y-m-d H:i:s', strtotime('+2 minutes'));
            $assign_query = "UPDATE patient_vitals SET doctorID = ?, remove_at = ? WHERE patientID = ?";
            $stmt = $conn->prepare($assign_query);
            $stmt->bind_param("isi", $doctorID, $removeTime, $patientID);

            if ($stmt->execute()) {
                $conn->commit(); // Commit transaction
                echo "<script>alert('Doctor assigned successfully.');</script>";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                throw new Exception("Failed to assign doctor.");
            }
        }
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on failure
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    } finally {
        $stmt->close();
    }
}

// Automatically remove expired doctor assignments (assignments older than 2 minutes)
$currentTime = date('Y-m-d H:i:s');
$remove_query = "UPDATE patient_vitals SET doctorID = NULL, remove_at = NULL WHERE remove_at <= ?";
$stmt = $conn->prepare($remove_query);
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// Fetch doctors for the assignment dropdown
$doctor_query  = "SELECT doctorID, CONCAT(firstName, ' ', middleName, ' ', surname) AS full_name, specialization FROM doctors";
$doctor_result = $conn->query($doctor_query);
$doctors       = $doctor_result->fetch_all(MYSQLI_ASSOC);

// Fetch doctor's statuses for the sidebar (initial load)
$doctors_status_query = "SELECT firstName, surname, status FROM doctors";
$doctors_status       = $conn->query($doctors_status_query);

// Fetch patient vitals for main table
$vitals_query  = "SELECT pv.*, p.first_name, p.last_name, p.dateOfBirth, p.gender, p.phone, 
                         p.insurance_number, p.emergency_contact, p.doctor_type 
                  FROM patient_vitals pv 
                  JOIN patients p ON pv.patientID = p.patientID 
                  ORDER BY pv.created_at DESC";
$vitals_result = $conn->query($vitals_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Doctor to Patients</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: rgb(98, 114, 124);
      --secondary-color: rgb(9, 143, 153);
      --accent-color: #f39c12;
      --bg-color: rgb(4, 127, 136);
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-color);
      display: flex;
      flex-direction: row;
    }
    h1 {
      font-weight: 600;
      color: var(--accent-color);
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 10px 20px rgb(240, 42, 42);
    }
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgb(9, 255, 214);
    }
    .badge {
      font-size: 14px;
      padding: 8px 12px;
    }
    .status-in {
      color: rgb(251, 255, 0);
      font-weight: bold;
    }
    .status-out {
      color: rgb(255, 25, 0);
      font-weight: bold;
    }
    /* Sidebar Styling */
    .sidebar {
      background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
      color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      margin-bottom: 20px;
      width: 300px; /* Fixed width for sidebar */
      height: 100vh; /* Full height */
      overflow-y: auto; /* Enable scrolling if content overflows */
      position: fixed; /* Fixed position */
      left: 0;
      top: 0;
    }
    .sidebar h4 {
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(255,255,255,0.3);
      padding-bottom: 10px;
    }
    .sidebar table {
      color: #fff;
    }
    .sidebar table th {
      background-color: rgba(0, 0, 0, 0.2);
      border-color: rgba(255, 255, 255, 0.3);
    }
    .sidebar table td, .sidebar table th {
      vertical-align: middle;
    }
    .sidebar table tr:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    /* Main Content Adjustments */
    .main-content {
      margin-left: 320px; /* Add margin to avoid overlap with sidebar */
      padding: 20px;
      width: calc(100% - 320px); /* Adjust width */
    }
    .main-table th, .main-table td {
      vertical-align: middle;
    }
    .main-table .form-select {
      max-width: 250px;
    }
    /* Back Button Styling */
    .back-btn {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <!-- Sidebar for Doctors Status -->
  <div class="sidebar"><a href="nurse_dashboard.php" class="btn btn-secondary back-btn">Back</a>
    <h4>Doctors Status</h4> 
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Doctor Name</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="doctors-status-body">
        <?php while ($row = $doctors_status->fetch_assoc()): ?>
          <tr>
            <td><?= $row['firstName'] . ' ' . $row['surname']; ?></td>
            <td>
              <span class="<?= $row['status'] === 'In' ? 'status-in' : 'status-out'; ?>">
                <?= $row['status']; ?>
              </span>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Main Content for Patient Vitals & Doctor Assignment -->
  <div class="main-content">
    <!-- Back Button -->
   
    
    <h1 class="text-center mb-4">Patient Vitals & Doctor Assignment</h1>
    <div class="card p-4 mt-3">
      <h4>Recorded Vitals</h4>
      <table class="table table-striped table-bordered main-table">
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

  <!-- JavaScript to auto-refresh only the doctors status table every 1 second -->
  <script>
    function refreshDoctorsStatus() {
      fetch('fetch_doctors_status.php')
        .then(response => response.text())
        .then(data => {
          document.getElementById('doctors-status-body').innerHTML = data;
        })
        .catch(error => console.error('Error fetching doctor statuses:', error));
    }
    // Refresh every 1 second (1000 milliseconds)
    setInterval(refreshDoctorsStatus, 1000);
    // Initial call to populate immediately
    refreshDoctorsStatus();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
