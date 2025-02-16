<?php
session_start();

// Database connection details
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

// Create database connection
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$message = "";
$results = [];
$patients = [];

// Check if doctorID is available in the session
if (isset($_SESSION['doctorID'])) {
    $doctorID = $_SESSION['doctorID'];
} else {
    $message = "Doctor is not logged in.";
}

// Fetch patient names whose lab results exist for the logged-in doctor
$fetch_patients_query = "SELECT DISTINCT p.patientID, p.first_name, p.last_name 
                         FROM laboratory_results lr
                         JOIN patients p ON lr.patientID = p.patientID
                         LEFT JOIN prescriptions pr ON p.patientID = pr.patientID AND pr.doctorID = '$doctorID'
                         WHERE lr.doctorID = '$doctorID' AND pr.prescriptionID IS NULL";
$patient_result = mysqli_query($conn, $fetch_patients_query);
if ($patient_result && mysqli_num_rows($patient_result) > 0) {
    while ($row = mysqli_fetch_assoc($patient_result)) {
        $patients[] = $row;
    }
}

// If a patient is selected via GET, fetch that patient's lab results
if (isset($_GET['patient_id'])) {
    $patientID = mysqli_real_escape_string($conn, $_GET['patient_id']);
    $query = "SELECT lr.*, p.first_name, p.last_name 
              FROM laboratory_results lr
              JOIN patients p ON lr.patientID = p.patientID
              WHERE lr.patientID = '$patientID' AND lr.doctorID = '$doctorID'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    } else {
        $message = "No results found for the selected patient.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Results & Prescriptions</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Global Styles */
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      background-color: #f0f4f8;
      display: flex;
      color: #2c3e50;
    }
    /* Sidebar Styles */
    .sidebar {
      width: 250px;
      background-color: #ecf0f1;
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      height: 100vh;
    }
    .sidebar h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #3498db;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar li {
      margin-bottom: 15px;
    }
    .sidebar a {
      text-decoration: none;
      display: block;
      padding: 10px;
      background-color: #3498db;
      color: #fff;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }
    .sidebar a:hover {
      background-color: #2980b9;
    }
    .back-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      margin-bottom: 20px;
      background-color: #3498db;
      color: #fff;
      border-radius: 50%;
      text-decoration: none;
      font-size: 24px;
      transition: background-color 0.3s ease;
    }
    .back-btn:hover {
      background-color: #2980b9;
      transform: scale(1.1);
    }
    /* Main Content Styles */
    .main-content {
      flex-grow: 1;
      padding: 20px;
    }
    .main-content h1 {
      text-align: center;
      color: #3498db;
      margin-bottom: 20px;
    }
    /* Table Styles */
    .results-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .results-table th, .results-table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ecf0f1;
    }
    .results-table th {
      background-color: #3498db;
      color: #fff;
      text-transform: uppercase;
    }
    .results-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .results-table tr:hover {
      background-color: #f1f8ff;
    }
    /* Prescription Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 1050;
    }
    .modal-content {
      background-color: #fff;
      border-radius: 8px;
      width: 80%;
      max-width: 900px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 20px;
      position: relative;
    }
    .modal-content h2 {
      color: #3498db;
      margin-bottom: 20px;
      text-align: center;
    }
    .modal-content .close {
      position: absolute;
      right: 20px;
      top: 20px;
      font-size: 28px;
      cursor: pointer;
      color: #2c3e50;
    }
    /* Two-Column Prescription Layout */
    .prescription-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    .prescription-form,
    .medicines-list {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      flex: 1;
      min-width: 300px;
    }
    .prescription-form h3,
    .medicines-list h3 {
      text-align: center;
      color: #3498db;
      margin-bottom: 15px;
    }
    .prescription-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    .prescription-form input,
    .prescription-form textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ecf0f1;
      border-radius: 5px;
    }
    .prescription-form button,
    .medicines-list button {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background-color: #3498db;
      color: #fff;
      font-size: 16px;
      font-weight: 500;
      transition: background-color 0.3s ease;
      cursor: pointer;
    }
    .prescription-form button:hover,
    .medicines-list button:hover {
      background-color: #2980b9;
    }
    .medicines-list table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .medicines-list th,
    .medicines-list td {
      padding: 10px;
      border: 1px solid #ecf0f1;
      text-align: left;
    }
    .medicines-list th {
      background-color: #3498db;
      color: #fff;
      text-transform: uppercase;
    }
    .medicines-list tr:nth-child(even) {
      background-color: #f1f1f1;
    }
    .medicines-list tr:hover {
      background-color: #e3f2fd;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <a href="doctor.php" class="back-btn">⬅️</a>
    <h2>Patients</h2>
    <ul>
      <?php foreach ($patients as $patient) : ?>
        <li>
          <a href="?patient_id=<?= $patient['patientID'] ?>">
            <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <!-- Main Content -->
  <div class="main-content">
    <h1>Patient Results & Prescriptions</h1>
    <?php if (!empty($message)) : ?>
      <div class="alert alert-warning">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($results)) : ?>
      <table class="results-table">
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Test Result</th>
            <th>Technician Name</th>
            <th>Status Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $row) : ?>
            <tr>
              <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td><?= htmlspecialchars($row['test_result']) ?></td>
              <td><?= htmlspecialchars($row['technician_name']) ?></td>
              <td><?= htmlspecialchars($row['test_date']) ?></td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-success add-prescription" onclick="openModal('<?= $row['patientID'] ?>')">Add Prescription</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="text-align: center;">Select a patient from the sidebar to view results.</p>
    <?php endif; ?>
  </div>

  <!-- Prescription Modal -->
  <div id="prescriptionModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Add Prescription</h2>
      <div class="prescription-container">
        <!-- Left: Prescription Form -->
        <div class="prescription-form">
          <h3>Prescription Form</h3>
          <form id="addMedicineForm">
            <input type="hidden" id="patientID" name="patientID">
            <label for="medicationName">Medication Name:</label>
            <input type="text" id="medicationName" name="medicationName" required>
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>
            <label for="dosage">Dosage:</label>
            <input type="text" id="dosage" name="dosage" required>
            <label for="instructions">Instructions:</label>
            <textarea id="instructions" name="instructions" rows="4" required></textarea>
            <button type="button" onclick="addMedicine()">Add Medicine</button>
          </form>
        </div>
        <!-- Right: Medicines List -->
        <div class="medicines-list">
          <h3>Added Medicines</h3>
          <table>
            <thead>
              <tr>
                <th>Medication</th>
                <th>Qty</th>
                <th>Dosage</th>
                <th>Instructions</th>
                <th>Remove</th>
              </tr>
            </thead>
            <tbody id="medicinesList">
              <!-- Medicines added dynamically -->
            </tbody>
          </table>
          <button type="button" onclick="submitPrescription()">Submit Prescription</button>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let medicines = [];

    // Open the prescription modal and set patient ID
    function openModal(patientID) {
      document.getElementById('patientID').value = patientID;
      document.getElementById('prescriptionModal').style.display = 'flex';
    }

    // Close the modal and reset the medicines list
    function closeModal() {
      document.getElementById('prescriptionModal').style.display = 'none';
      medicines = [];
      document.getElementById('medicinesList').innerHTML = '';
    }

    // Add a medicine to the list
    function addMedicine() {
      const medicationName = document.getElementById('medicationName').value.trim();
      const quantity = document.getElementById('quantity').value.trim();
      const dosage = document.getElementById('dosage').value.trim();
      const instructions = document.getElementById('instructions').value.trim();

      if (medicationName && quantity && dosage && instructions) {
        const medicine = { medicationName, quantity, dosage, instructions };
        medicines.push(medicine);
        updateMedicinesTable();
        document.getElementById('addMedicineForm').reset();
      } else {
        alert('Please fill all fields.');
      }
    }

    // Update the medicines table in the modal
    function updateMedicinesTable() {
      const medicinesList = document.getElementById('medicinesList');
      medicinesList.innerHTML = medicines.map((medicine, index) => `
        <tr>
          <td>${medicine.medicationName}</td>
          <td>${medicine.quantity}</td>
          <td>${medicine.dosage}</td>
          <td>${medicine.instructions}</td>
          <td><button class="btn btn-danger btn-sm" onclick="removeMedicine(${index})">Remove</button></td>
        </tr>
      `).join('');
    }

    // Remove a medicine from the list
    function removeMedicine(index) {
      medicines.splice(index, 1);
      updateMedicinesTable();
    }

    // Submit the prescription using AJAX
    function submitPrescription() {
      const patientID = document.getElementById('patientID').value;
      if (medicines.length === 0) {
        alert('Please add at least one medicine.');
        return;
      }

      const formData = new FormData();
      formData.append('patientID', patientID);
      formData.append('medicines', JSON.stringify(medicines));

      fetch('add_prescription.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Prescription added successfully!');
          closeModal();
          window.location.reload();
        } else {
          alert('Error adding prescription: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error: ' + error);
      });
    }
  </script>
  <?php mysqli_close($conn); ?>
</body>
</html>
