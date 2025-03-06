<?php
// doctorReport.php
session_start();

// Check if doctor is logged in
if (!isset($_SESSION['doctorID'])) {
    header("Location: index.php");
    exit();
}
$doctor_id = intval($_SESSION['doctorID']);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctor basic info
$doctor_sql = "SELECT * FROM doctors WHERE doctorID = ?";
$stmt = $conn->prepare($doctor_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
if ($doctor_result->num_rows === 0) {
    die("Doctor not found.");
}
$doctor = $doctor_result->fetch_assoc();
$doctor_fullName = $doctor['firstName'] .
    (!empty($doctor['middleName']) ? " " . $doctor['middleName'] : "") .
    " " . $doctor['surname'];
$specialization = isset($doctor['specialization']) ? $doctor['specialization'] : "N/A";

// Fetch all appointments for this doctor
$appointments = [];
$app_sql = "SELECT id, doctor_id, patient_name, appointment_date, appointment_time, reason 
            FROM appointments 
            WHERE doctor_id = ? 
            ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $conn->prepare($app_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$app_result = $stmt->get_result();
while ($row = $app_result->fetch_assoc()) {
    $appointments[] = $row;
}

// Fetch all diagnoses for this doctor
$diagnoses = [];
$diag_sql = "SELECT diagnosisID, patientID, diagnosis 
             FROM diagnoses 
             WHERE doctorID = ? 
             ORDER BY diagnosisID DESC";
$stmt = $conn->prepare($diag_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$diag_result = $stmt->get_result();
while ($row = $diag_result->fetch_assoc()) {
    $diagnoses[] = $row;
}

// Fetch all prescriptions for this doctor
$prescriptions = [];
$presc_sql = "SELECT prescriptionID, patientID, created_at, dateIssued 
              FROM prescriptions 
              WHERE doctorID = ? 
              ORDER BY created_at DESC";
$stmt = $conn->prepare($presc_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$presc_result = $stmt->get_result();
while ($row = $presc_result->fetch_assoc()) {
    $prescriptions[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Comprehensive Report</title>
  <!-- Include Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <!-- Custom styles for doctor module -->
  <style>
    body {
      font-family: 'Montserrat', 'Roboto', Arial, sans-serif;
      background-color: #eef2f7;
      margin-top: 20px;
    }
    .container {
      margin-top: 20px;
    }
    .header, .section {
      background-color: #ffffff;
      border: 1px solid #d1dce5;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .header h1 {
      color: #2a4d69;
      margin-bottom: 10px;
    }
    .header p {
      font-size: 14px;
      color: #666;
    }
    .section h2 {
      color: #2a4d69;
      border-bottom: 1px solid #ecf0f1;
      padding-bottom: 8px;
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #f8f9fa;
    }
    .back-btn {
      margin-bottom: 20px;
    }
    .no-print {
      text-align: center;
      font-size: 12px;
      color: #666;
      margin-top: 20px;
    }
    @media print {
      body {
        margin: 1cm;
      }
      .no-print, .back-btn, .print-btn, .modal {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="doctor.php" class="btn btn-secondary back-btn">&laquo; Back to Doctor Dashboard</a>
      <!-- Print Report Button triggers the modal -->
      <button type="button" class="btn btn-primary print-btn" data-bs-toggle="modal" data-bs-target="#printModal">
        Print Report
      </button>
    </div>
    
    <div class="header">
      <h1>ST. NORBERT HOSPITAL</h1>
      <p>Comprehensive Medical Report for Dr. <?= htmlspecialchars($doctor_fullName); ?> (<?= htmlspecialchars($specialization); ?>)</p>
      <p>Report generated on <?= date('F j, Y, g:i a'); ?></p>
    </div>

    <!-- Doctor Information Section -->
    <div class="section" id="doctorInfo">
      <h2>Doctor Information</h2>
      <table>
        <tr>
          <th>Doctor Name</th>
          <td><?= htmlspecialchars($doctor_fullName); ?></td>
          <th>Specialization</th>
          <td><?= htmlspecialchars($specialization); ?></td>
        </tr>
        <tr>
          <th>Doctor ID</th>
          <td><?= $doctor_id; ?></td>
          <th>Total Appointments</th>
          <td><?= count($appointments); ?></td>
        </tr>
      </table>
    </div>

    <!-- Appointment History Section -->
    <div class="section" id="appointmentHistory">
      <h2>Appointment History</h2>
      <?php if (!empty($appointments)): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Patient Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Reason</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appointments as $appointment): ?>
          <tr>
            <td><?= $appointment['id']; ?></td>
            <td><?= htmlspecialchars($appointment['patient_name']); ?></td>
            <td><?= htmlspecialchars($appointment['appointment_date']); ?></td>
            <td><?= htmlspecialchars($appointment['appointment_time']); ?></td>
            <td><?= nl2br(htmlspecialchars($appointment['reason'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No appointments found for this doctor.</p>
      <?php endif; ?>
    </div>

    <!-- Diagnosis Section -->
    <div class="section" id="diagnosisRecords">
      <h2>Diagnosis Records</h2>
      <?php if (!empty($diagnoses)): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Diagnosis ID</th>
            <th>Patient ID</th>
            <th>Diagnosis</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($diagnoses as $diag): ?>
          <tr>
            <td><?= $diag['diagnosisID']; ?></td>
            <td><?= $diag['patientID']; ?></td>
            <td><?= nl2br(htmlspecialchars($diag['diagnosis'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No diagnosis records found for this doctor.</p>
      <?php endif; ?>
    </div>

    <!-- Prescription Section -->
    <div class="section" id="prescriptionRecords">
      <h2>Prescription Records</h2>
      <?php if (!empty($prescriptions)): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Prescription ID</th>
            <th>Patient ID</th>
            <th>Created At</th>
            <th>Date Issued</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prescriptions as $presc): ?>
          <tr>
            <td><?= $presc['prescriptionID']; ?></td>
            <td><?= $presc['patientID']; ?></td>
            <td><?= htmlspecialchars($presc['created_at']); ?></td>
            <td><?= htmlspecialchars($presc['dateIssued']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No prescription records found for this doctor.</p>
      <?php endif; ?>
    </div>

    <div class="no-print">
      <p>Generated on <?= date('Y-m-d H:i:s'); ?> | Electronic Medical Record System</p>
    </div>
  </div>

  <!-- Print Modal -->
  <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="printForm">
          <div class="modal-header">
            <h5 class="modal-title" id="printModalLabel">Print Report Options</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="reportType" class="form-label">Report Type</label>
                  <select class="form-select" id="reportType" name="reportType" required>
                      <option value="full">Full Report</option>
                      <option value="appointments">Appointment History</option>
                      <option value="diagnoses">Diagnosis Records</option>
                      <option value="prescriptions">Prescription Records</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label for="fromDate" class="form-label">From Date</label>
                  <input type="date" class="form-control" id="fromDate" name="fromDate">
              </div>
              <div class="mb-3">
                  <label for="toDate" class="form-label">To Date</label>
                  <input type="date" class="form-control" id="toDate" name="toDate">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Done</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Include Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom Script for Print Modal -->
  <script>
    document.getElementById("printForm").addEventListener("submit", function(e) {
      e.preventDefault();
      // Retrieve selected report type
      var reportType = document.getElementById("reportType").value;
      // List of section IDs
      var sections = ["doctorInfo", "appointmentHistory", "diagnosisRecords", "prescriptionRecords"];
      // Determine which section(s) to hide. For a "full" report, do not hide any.
      sections.forEach(function(sectionId) {
        // For specific report types, only show the matching section.
        if (reportType === "full") {
          document.getElementById(sectionId).classList.remove("d-none");
        } else {
          // Map report type to section ID
          if (
            (reportType === "appointments" && sectionId !== "appointmentHistory") ||
            (reportType === "diagnoses" && sectionId !== "diagnosisRecords") ||
            (reportType === "prescriptions" && sectionId !== "prescriptionRecords")
          ) {
            document.getElementById(sectionId).classList.add("d-none");
          } else {
            document.getElementById(sectionId).classList.remove("d-none");
          }
        }
      });
      
      // Optionally, you can also apply date filtering on the displayed table rows here
      // using fromDate and toDate values.

      // Close the modal before printing
      var printModal = bootstrap.Modal.getInstance(document.getElementById("printModal"));
      printModal.hide();

      // Use onafterprint to restore hidden sections after printing
      window.onafterprint = function() {
        sections.forEach(function(sectionId) {
          document.getElementById(sectionId).classList.remove("d-none");
        });
      };

      // Delay printing slightly to ensure the modal is closed
      setTimeout(function(){
        window.print();
      }, 500);
    });
  </script>
</body>
</html>
