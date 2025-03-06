<?php
session_start();

// Database connection details
$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "st_norbert_hospital";

// Create connection using MySQLi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process update if form is submitted
$updateMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Escape all input values
    $id             = $conn->real_escape_string($_POST['id']);
    $first_name     = $conn->real_escape_string($_POST['first_name']);
    $last_name      = $conn->real_escape_string($_POST['last_name']);
    $dateofBirth    = $conn->real_escape_string($_POST['dateofBirth']);
    $gender         = $conn->real_escape_string($_POST['gender']);
    $phone          = $conn->real_escape_string($_POST['phone']);
    $lab_result     = $conn->real_escape_string($_POST['lab_result']);
    $medicationName = $conn->real_escape_string($_POST['medicationName']);
    $dosages        = $conn->real_escape_string($_POST['dosages']);

    // Update the medical_history record based on the record id
    $updateQuery = "
        UPDATE medical_history 
        SET first_name = '$first_name', 
            last_name = '$last_name', 
            dateofBirth = '$dateofBirth', 
            gender = '$gender', 
            phone = '$phone', 
            lab_result = '$lab_result', 
            medicationName = '$medicationName', 
            dosages = '$dosages'
        WHERE id = '$id'
    ";
    if ($conn->query($updateQuery)) {
        $updateMessage = "Record updated successfully.";
    } else {
        $updateMessage = "Error updating record: " . $conn->error;
    }
}

// Search functionality: use GET parameter "search" to filter by first or last name
$search = $_GET['search'] ?? '';
$searchCondition = '';
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $searchCondition = " WHERE first_name LIKE '%$searchEscaped%' OR last_name LIKE '%$searchEscaped%'";
}

// Fetch data from the medical_history table with the search condition
$medicalHistoryQuery = "
    SELECT 
        id,
        doctorID,
        patientID,
        prescriptionID,
        first_name,
        last_name,
        dateofBirth,
        gender,
        phone,
        lab_result,
        medicationName,
        dosages,
        created_at
    FROM 
        medical_history
    $searchCondition
    ORDER BY 
        created_at DESC
";
$medicalHistoryResult = $conn->query($medicalHistoryQuery);
if (!$medicalHistoryResult) {
    die("Medical history query error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Management System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <style>
    /* General Styles */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      margin: 0;
      padding: 0;
    }
    .container {
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 20px;
      text-align: center;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    /* Search Bar Styles */
    .search-container {
      margin-bottom: 30px;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }
    .search-input {
      border-radius: 25px;
      padding: 10px 20px;
      border: 1px solid #ced4da;
      font-size: 1rem;
    }
    .search-btn {
      border-radius: 25px;
      padding: 10px 20px;
      background-color: #3498db;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s ease;
    }
    .search-btn:hover {
      background-color: #2980b9;
    }
    /* Table Styles */
    .table-responsive {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .table {
      margin-bottom: 0;
      background-color: white;
    }
    .table thead {
      background-color: #3498db;
      color: white;
    }
    .table th, .table td {
      padding: 15px;
      text-align: left;
    }
    .table tbody tr {
      transition: background-color 0.2s ease;
      cursor: pointer;
    }
    .table tbody tr:hover {
      background-color: #f1f1f1;
    }
    /* Modal Styles */
    .modal-content {
      border-radius: 10px;
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .modal-header {
      background-color: #3498db;
      color: white;
      border-radius: 10px 10px 0 0;
      padding: 15px;
    }
    .modal-title {
      font-weight: 600;
    }
    .modal-body {
      padding: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Patient Management System</h1>
    
    <?php if (!empty($updateMessage)): ?>
      <div class="alert alert-success text-center">
        <?= htmlspecialchars($updateMessage) ?>
      </div>
    <?php endif; ?>
    
    <!-- Search Bar -->
    <div class="search-container">
      <form method="GET" action="">
        <div class="input-group">
          <input type="text" class="form-control search-input" 
                 name="search"
                 placeholder="Search by patient first or last name..." 
                 value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="search-btn">Search</button>
        </div>
      </form>
    </div>

    <!-- Display Medical History -->
    <h2>Medical History</h2>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th>Phone</th>
            <th>Lab Result</th>
            <th>Medication Name</th>
            <th>Dosage</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($medicalHistoryResult->num_rows > 0): ?>
            <?php while ($row = $medicalHistoryResult->fetch_assoc()): ?>
              <tr class="patient-row" 
                  data-id="<?= $row['id'] ?>"
                  data-first-name="<?= htmlspecialchars($row['first_name']) ?>"
                  data-last-name="<?= htmlspecialchars($row['last_name']) ?>"
                  data-dob="<?= htmlspecialchars($row['dateofBirth']) ?>"
                  data-gender="<?= htmlspecialchars($row['gender']) ?>"
                  data-phone="<?= htmlspecialchars($row['phone']) ?>"
                  data-lab-result="<?= htmlspecialchars($row['lab_result']) ?>"
                  data-medication-name="<?= htmlspecialchars($row['medicationName']) ?>"
                  data-dosages="<?= htmlspecialchars($row['dosages']) ?>">
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['dateofBirth']) ?></td>
                <td><?= htmlspecialchars($row['gender']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['lab_result']) ?></td>
                <td><?= htmlspecialchars($row['medicationName']) ?></td>
                <td><?= htmlspecialchars($row['dosages']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
              <tr>
                <td colspan="9" class="text-center py-4">No medical history found</td>
              </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal for Viewing and Updating Medical History -->
  <div class="modal fade" id="patientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST" action="">
          <div class="modal-header">
            <h5 class="modal-title">Update Medical History</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="id" name="id">
            <div class="row mb-3">
              <div class="col">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
              </div>
              <div class="col">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label for="dateofBirth" class="form-label">Date of Birth</label>
                <input type="date" id="dateofBirth" name="dateofBirth" class="form-control" required>
              </div>
              <div class="col">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" class="form-select" required>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input type="text" id="phone" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="lab_result" class="form-label">Lab Result</label>
              <input type="text" id="lab_result" name="lab_result" class="form-control" required>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label for="medicationName" class="form-label">Medication Name</label>
                <input type="text" id="medicationName" name="medicationName" class="form-control" required>
              </div>
              <div class="col">
                <label for="dosages" class="form-label">Dosage</label>
                <input type="text" id="dosages" name="dosages" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update Record</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // When a record row is clicked, populate and show the modal with the record's data
    document.querySelectorAll('.patient-row').forEach(row => {
      row.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const firstName = this.getAttribute('data-first-name');
        const lastName = this.getAttribute('data-last-name');
        const dob = this.getAttribute('data-dob');
        const gender = this.getAttribute('data-gender');
        const phone = this.getAttribute('data-phone');
        const labResult = this.getAttribute('data-lab-result');
        const medicationName = this.getAttribute('data-medication-name');
        const dosages = this.getAttribute('data-dosages');

        // Populate modal fields
        document.getElementById('id').value = id;
        document.getElementById('first_name').value = firstName;
        document.getElementById('last_name').value = lastName;
        document.getElementById('dateofBirth').value = dob;
        document.getElementById('gender').value = gender;
        document.getElementById('phone').value = phone;
        document.getElementById('lab_result').value = labResult;
        document.getElementById('medicationName').value = medicationName;
        document.getElementById('dosages').value = dosages;

        // Open the modal
        const modal = new bootstrap.Modal(document.getElementById('patientModal'));
        modal.show();
      });
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
