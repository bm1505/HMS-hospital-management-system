<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Include the database connection
// include('db_connection.php');

// // Get the list of doctors from the database
// $doctorQuery = "SELECT doctorID, doctorName FROM doctors";
// $doctorResult = mysqli_query($conn, $doctorQuery);

// // Get the list of patients from the database
// $patientQuery = "SELECT patientID, patientName FROM patients";
// $patientResult = mysqli_query($conn, $patientQuery);

// // Handle form submission
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $patientID = $_POST['patientID'];
//     $doctorID = $_POST['doctorID'];
//     $diagnosis = $_POST['diagnosis'];
//     $prescription = $_POST['prescription'];
//     $treatmentPlan = $_POST['treatmentPlan'];
//     $testResults = $_POST['testResults'];
//     $notes = $_POST['notes'];
//     $dateCreated = date('Y-m-d'); // Today's date

//     // Form validation
//     if (empty($patientID) || empty($doctorID) || empty($diagnosis) || empty($prescription) || empty($treatmentPlan)) {
//         $error = "All fields are required!";
//     } else {
//         // Insert the medical record into the database
//         $sql = "INSERT INTO medical_records (patientID, doctorID, diagnosis, prescription, treatmentPlan, testResults, dateCreated, notes)
//                 VALUES ('$patientID', '$doctorID', '$diagnosis', '$prescription', '$treatmentPlan', '$testResults', '$dateCreated', '$notes')";
        
//         if (mysqli_query($conn, $sql)) {
//             $success = "Medical record added successfully!";
//         } else {
//             $error = "Error: " . mysqli_error($conn);
//         }
//     }
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronic Medical Records</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <h4 class="navbar-text mx-auto" style="font-weight: bold; color: white;">
                Electronic Medical Records
            </h4>
        </div>
    </nav>

    <!-- Medical Record Form -->
    <div class="container mt-5">
        <h2>Add Patient Medical Record</h2>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="patientID">Patient</label>
                <select class="form-control" id="patientID" name="patientID" required>
                    <option value="">Select Patient</option>
                    <?php while ($row = mysqli_fetch_assoc($patientResult)) { ?>
                        <option value="<?php echo $row['patientID']; ?>"><?php echo $row['patientName']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="doctorID">Doctor</label>
                <select class="form-control" id="doctorID" name="doctorID" required>
                    <option value="">Select Doctor</option>
                    <?php while ($row = mysqli_fetch_assoc($doctorResult)) { ?>
                        <option value="<?php echo $row['doctorID']; ?>"><?php echo $row['doctorName']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="diagnosis">Diagnosis</label>
                <input type="text" class="form-control" id="diagnosis" name="diagnosis" required>
            </div>

            <div class="form-group">
                <label for="prescription">Prescription</label>
                <textarea class="form-control" id="prescription" name="prescription" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="treatmentPlan">Treatment Plan</label>
                <textarea class="form-control" id="treatmentPlan" name="treatmentPlan" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="testResults">Test Results</label>
                <textarea class="form-control" id="testResults" name="testResults" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Add Record</button>
            <a href="javascript:history.back()" class="btn btn-secondary ml-3">Go Back</a>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2024 Hospital Management System</p>
    </footer>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
