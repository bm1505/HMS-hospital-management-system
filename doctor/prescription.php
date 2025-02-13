<?php
session_start();

// Database connection details
$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "st_norbert_hospital";

// Create database connection
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// If the request method is POST and required data is sent, process the prescription insertion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['medicines']) && isset($_POST['patientID'])) {
    // Check if doctor is logged in
    if (!isset($_SESSION['doctorID'])) {
        echo json_encode(['success' => false, 'message' => 'Doctor is not logged in.']);
        exit;
    }
    $doctorID = $_SESSION['doctorID'];
    
    // Get and validate inputs
    $patientID = mysqli_real_escape_string($conn, $_POST['patientID']);
    $medicines = json_decode($_POST['medicines'], true);
    
    if (empty($patientID) || !is_array($medicines) || count($medicines) === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }
    
    // Ensure each medicine has complete data
    foreach ($medicines as $medicine) {
        if (
            empty($medicine['medicationName']) ||
            empty($medicine['quantity']) ||
            empty($medicine['dosage']) ||
            empty($medicine['instructions'])
        ) {
            echo json_encode(['success' => false, 'message' => 'All medicine fields must be complete.']);
            exit;
        }
    }
    
    // Insert into the prescriptions table (header)
    $sql = "INSERT INTO prescriptions (doctorID, patientID, created_at) 
            VALUES ('$doctorID', '$patientID', NOW())";
    if (mysqli_query($conn, $sql)) {
        $prescriptionID = mysqli_insert_id($conn);
        $allInserted = true;
        // Loop over each medicine and insert separately
        foreach ($medicines as $medicine) {
            $medicationName = mysqli_real_escape_string($conn, $medicine['medicationName']);
            $quantity       = mysqli_real_escape_string($conn, $medicine['quantity']);
            $dosage         = mysqli_real_escape_string($conn, $medicine['dosage']);
            $instructions   = mysqli_real_escape_string($conn, $medicine['instructions']);
            
            $insertMedicineSql = "INSERT INTO prescription_medicines (prescriptionID, medicationName, quantity, dosage, instructions)
                                  VALUES ('$prescriptionID', '$medicationName', '$quantity', '$dosage', '$instructions')";
            if (!mysqli_query($conn, $insertMedicineSql)) {
                $allInserted = false;
                echo json_encode(['success' => false, 'message' => 'Error inserting medicine: ' . mysqli_error($conn)]);
                exit;
            }
        }
        if ($allInserted) {
            echo json_encode(['success' => true]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error inserting prescription: ' . mysqli_error($conn)]);
    }
    exit;
}

// Initialize variables for displaying patient results
$message  = "";
$results  = [];
$patients = [];

// Check if doctorID is available in the session
if (isset($_SESSION['doctorID'])) {
    $doctorID = $_SESSION['doctorID'];
} else {
    $message = "Doctor is not logged in.";
}

// Fetch patient names whose results exist in the laboratory_results table for the logged-in doctor
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

// If a patient is selected via GET parameter, fetch the patient's results along with patient name info
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Results & Prescriptions</title>
  <style>
    /* Global Styles */
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f0f4f8;
      color: #2c3e50;
      display: flex;
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
    .results-table, .medicines-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .results-table th, .results-table td, 
    .medicines-table th, .medicines-table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ecf0f1;
    }
    .results-table th, .medicines-table th {
      background-color: #3498db;
      color: #fff;
      text-transform: uppercase;
    }
    .results-table tr:nth-child(even), 
    .medicines-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .results-table tr:hover, 
    .medicines-table tr:hover {
      background-color: #f1f8ff;
    }
    /* Action Buttons */
    .action-btns {
      display: flex;
      gap: 10px;
    }
    .action-btns button {
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: #fff;
      transition: background-color 0.3s ease;
    }
    .add-prescription {
      background-color: #27ae60;
    }
    .add-prescription:hover {
      background-color: #2ecc71;
    }
    .remove-medicine {
      background-color: #e74c3c;
    }
    .remove-medicine:hover {
      background-color: #c0392b;
    }
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 700px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .modal-content h2 {
      margin-bottom: 20px;
      color: #3498db;
    }
    .modal-content .close {
      float: right;
      cursor: pointer;
      font-size: 20px;
      color: #2c3e50;
    }
    /* Two-column layout for modal */
    .modal-container {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }
    .form-container,
    .table-container {
      flex: 1;
      min-width: 300px;
      padding: 15px;
      background-color: #f9f9f9;
      border: 1px solid #ecf0f1;
      border-radius: 5px;
    }
    .form-container label {
      display: block;
      margin-bottom: 5px;
      color: #2c3e50;
    }
    .form-container input,
    .form-container textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ecf0f1;
      border-radius: 5px;
    }
    .form-container button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: #fff;
      background-color: #3498db;
      transition: background-color 0.3s ease;
    }
    .form-container button:hover {
      background-color: #2980b9;
    }
    .table-container button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: #fff;
      background-color: #3498db;
      transition: background-color 0.3s ease;
    }
    .table-container button:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>
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
  <div class="main-content">
    <h1>Patient Results & Prescriptions</h1>
    <?php if (!empty($message)) : ?>
      <div class="message <?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
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
                  <button class="add-prescription" onclick="openModal('<?= $row['patientID'] ?>')">Add Prescription</button>
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

  <!-- Modal for Adding Prescription -->
  <div id="prescriptionModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Add Prescription</h2>
      <div class="modal-container">
        <!-- Left Side: Prescription Form -->
        <div class="form-container">
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
        <!-- Right Side: Added Medicines Table -->
        <div class="table-container">
          <h3>Added Medicines</h3>
          <table class="medicines-table">
            <thead>
              <tr>
                <th>Medication Name</th>
                <th>Quantity</th>
                <th>Dosage</th>
                <th>Instructions</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="medicinesList">
              <!-- Medicines will be added here dynamically -->
            </tbody>
          </table>
          <button type="button" onclick="submitPrescription()" style="margin-top: 20px;">Submit Prescription</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let medicines = [];

    // Function to open the modal and set the patient ID
    function openModal(patientID) {
      document.getElementById('patientID').value = patientID;
      document.getElementById('prescriptionModal').style.display = 'flex';
    }

    // Function to close the modal and reset the medicines list
    function closeModal() {
      document.getElementById('prescriptionModal').style.display = 'none';
      medicines = []; // Reset medicines list
      document.getElementById('medicinesList').innerHTML = ''; // Clear table
    }

    // Function to add a medicine to the list
    function addMedicine() {
      const medicationName = document.getElementById('medicationName').value.trim();
      const quantity = document.getElementById('quantity').value.trim();
      const dosage = document.getElementById('dosage').value.trim();
      const instructions = document.getElementById('instructions').value.trim();

      if (medicationName && quantity && dosage && instructions) {
        const medicine = { medicationName, quantity, dosage, instructions };
        medicines.push(medicine);
        updateMedicinesTable();
        document.getElementById('addMedicineForm').reset(); // Clear form
      } else {
        alert('Please fill all fields.');
      }
    }

    // Function to update the medicines table with current list
    function updateMedicinesTable() {
      const medicinesList = document.getElementById('medicinesList');
      medicinesList.innerHTML = medicines.map((medicine, index) => `
        <tr>
          <td>${medicine.medicationName}</td>
          <td>${medicine.quantity}</td>
          <td>${medicine.dosage}</td>
          <td>${medicine.instructions}</td>
          <td><button class="remove-medicine" onclick="removeMedicine(${index})">Remove</button></td>
        </tr>
      `).join('');
    }

    // Function to remove a medicine from the list by index
    function removeMedicine(index) {
      medicines.splice(index, 1);
      updateMedicinesTable();
    }

    // Function to submit the prescription via AJAX to the same script
    function submitPrescription() {
      const patientID = document.getElementById('patientID').value;
      if (medicines.length === 0) {
        alert('Please add at least one medicine.');
        return;
      }

      const formData = new FormData();
      formData.append('patientID', patientID);
      formData.append('medicines', JSON.stringify(medicines));

      fetch(window.location.href, {
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
