<?php
// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch patients
$patients_result = $conn->query("SELECT * FROM patients");

// Initialize messages
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_diagnosis'])) {
    // Ensure patient_id is set from the form
    if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
        $patient_id = $_POST['patient_id'];
        $diagnosis = $_POST['diagnosis'];
        $medications = $_POST['medications'];
        $lab_tests = $_POST['lab_tests'];
        $created_at = date('Y-m-d H:i:s');

        // Insert into patient_diagnosis
        $stmt = $conn->prepare("INSERT INTO patient_diagnosis (patient_id, diagnosis, medications, lab_tests, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $patient_id, $diagnosis, $medications, $lab_tests, $created_at);

        if ($stmt->execute()) {
            $success_message = "Diagnosis recorded successfully.";
        } else {
            $error_message = "Error recording diagnosis: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch patient's medical history
$medical_history = [];
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $history_result = $conn->query("SELECT * FROM patient_diagnosis WHERE patient_id = $patient_id");
    while ($row = $history_result->fetch_assoc()) {
        $medical_history[] = $row;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Diagnosis</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h3>Patient Diagnosis</h3>
        <p>Record a diagnosis, prescribe medications, and order laboratory tests for a patient.</p>

        <!-- Display messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Side: Diagnosis Form -->
            <div class="col-md-6">
                <!-- Diagnosis form -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="patient_id">Select Patient:</label>
                        <select class="form-control" id="patient_id" name="patient_id" required>
                            <option value="">-- Select Patient --</option>
                            <?php while ($patient = $patients_result->fetch_assoc()): ?>
                                <option value="<?php echo $patient['id']; ?>"><?php echo $patient['id']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="medications">Medications:</label>
                        <textarea class="form-control" id="medications" name="medications" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="lab_tests">Lab Tests:</label>
                        <textarea class="form-control" id="lab_tests" name="lab_tests" rows="2" required></textarea>
                    </div>
                    <button type="submit" name="submit_diagnosis" class="btn btn-primary">Submit Diagnosis</button>
                </form>
            </div>

            <!-- Right Side: Patient Details and Medical History -->
            <div class="col-md-6">
                <h5>Patient Details</h5>
                <div id="patient_details">
                    <!-- Display Patient's Details (If Patient Exists) -->
                    <?php if (!empty($medical_history)): ?>
                        <h5>Medical History:</h5>
                        <ul>
                            <?php foreach ($medical_history as $history): ?>
                                <li>
                                    <strong>Diagnosis:</strong> <?php echo $history['diagnosis']; ?><br>
                                    <strong>Medications:</strong> <?php echo $history['medications']; ?><br>
                                    <strong>Lab Tests:</strong> <?php echo $history['lab_tests']; ?><br>
                                    <strong>Date:</strong> <?php echo $history['created_at']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No medical history found.</p>
                    <?php endif; ?>
                </div>

                <!-- Patient Search -->
                <div class="form-group mt-4">
                    <label for="patient_name">Search Patient:</label>
                    <input type="text" class="form-control" id="patient_name" name="patient_name" placeholder="Type patient's name" onkeyup="searchPatient()">
                    <input type="hidden" id="patient_id" name="patient_id">
                    <div id="patient_results"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Close database connection -->
    <?php $conn->close(); ?>

    <script>
        // JavaScript to search for patient and show medical history
        function searchPatient() {
            var patient_name = $('#patient_name').val();
            if (patient_name.length > 2) { // Start searching after 3 characters
                $.ajax({
                    url: "search_patient.php", // This file will handle search and return patient results
                    method: "GET",
                    data: { patient_name: patient_name },
                    success: function(response) {
                        $('#patient_results').html(response);
                    }
                });
            } else {
                $('#patient_results').empty();
            }
        }

        // JavaScript to handle patient selection and populate the hidden input field
        function selectPatient(patient_id, patient_name) {
            $('#patient_name').val(patient_name);
            $('#patient_id').val(patient_id);
            $('#patient_results').empty(); // Hide the results after selection
            fetchPatientDetails(patient_id); // Fetch and display patient details
        }

        // Function to fetch and display patient details
        function fetchPatientDetails(patient_id) {
            $.ajax({
                url: "fetch_patient_details.php", // This file will return the patient's details and medical history
                method: "GET",
                data: { patient_id: patient_id },
                success: function(response) {
                    $('#patient_details').html(response);
                }
            });
        }
    </script>
</body>
</html>
