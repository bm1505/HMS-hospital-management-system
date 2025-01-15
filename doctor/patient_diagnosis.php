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
$patients_result = $conn->query("SELECT patientID, first_name, last_name FROM patients");

// Initialize messages
$success_message = "";
$error_message = "";

// Handle form submission (for adding a diagnosis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_diagnosis'])) {
    if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
        $patient_id = $_POST['patient_id'];
        $diagnosis = $_POST['diagnosis'];
        $medications = $_POST['medications'];
        $lab_tests = $_POST['lab_tests'];
        $created_at = date('Y-m-d H:i:s');

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

// Fetch medical history based on patient search
$medical_history = [];
if (isset($_GET['patient_id']) && !empty($_GET['patient_id'])) {
    $patient_id = (int)$_GET['patient_id'];
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
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="patient_id">Select Patient:</label>
                        <select class="form-control" id="patient_id" name="patient_id" required>
                            <option value="">-- Select Patient --</option>
                            <?php while ($patient = $patients_result->fetch_assoc()): ?>
                                <option value="<?php echo $patient['patientID']; ?>">
                                    <?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?>
                                </option>
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
                    <?php if (!empty($medical_history)): ?>   
                    <?php endif; ?>
                </div>

                <!-- Search Patient Form -->
                <div class="form-group mt-4">
                    <label for="patient_name">Search Patient:</label>
                    <input type="text" class="form-control" id="patient_name" name="patient_name" placeholder="Type patient's name" onkeyup="searchPatient()">
                    <input type="hidden" id="selected_patient_id" name="selected_patient_id">
                    <div id="patient_results"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchPatient() {
            var patient_name = $('#patient_name').val();
            if (patient_name.length > 2) {
                $.ajax({
                    url: "search_patient.php",  // This should be a separate PHP file for searching patients
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

        function selectPatient(patient_id, patient_name) {
            $('#patient_name').val(patient_name);
            $('#selected_patient_id').val(patient_id);
            $('#patient_results').empty();

            // Optionally, you could trigger fetching additional details for the patient here.
            window.location.href = `patient_diagnosis.php?patient_id=${patient_id}`;
        }
    </script>
</body>
</html>
