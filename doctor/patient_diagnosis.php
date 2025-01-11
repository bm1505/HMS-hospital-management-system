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
//     $diagnosisName = $_POST['diagnosisName'];
//     $diagnosisDescription = $_POST['diagnosisDescription'];
//     $symptoms = $_POST['symptoms'];
//     $treatmentPlan = $_POST['treatmentPlan'];
//     $notes = $_POST['notes'];
//     $dateDiagnosed = date('Y-m-d'); // Today's date

//     // Form validation
//     if (empty($patientID) || empty($doctorID) || empty($diagnosisName) || empty($diagnosisDescription) || empty($symptoms) || empty($treatmentPlan)) {
//         $error = "All fields are required!";
//     } else {
//         // Insert the diagnosis into the database
//         $sql = "INSERT INTO diagnoses (patientID, doctorID, diagnosisName, diagnosisDescription, symptoms, treatmentPlan, dateDiagnosed, notes)
//                 VALUES ('$patientID', '$doctorID', '$diagnosisName', '$diagnosisDescription', '$symptoms', '$treatmentPlan', '$dateDiagnosed', '$notes')";
        
//         if (mysqli_query($conn, $sql)) {
//             $success = "Diagnosis recorded successfully!";
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
    <title>Patient Diagnosis</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <h4 class="navbar-text mx-auto" style="font-weight: bold; color: white;">
                Patient Diagnosis
            </h4>
        </div>
    </nav>

    <!-- Diagnosis Form -->
    <div class="container mt-5">
        <h2>Record Patient Diagnosis</h2>
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
                <label for="diagnosisName">Diagnosis Name</label>
                <input type="text" class="form-control" id="diagnosisName" name="diagnosisName" required>
            </div>

            <div class="form-group">
                <label for="diagnosisDescription">Diagnosis Description</label>
                <textarea class="form-control" id="diagnosisDescription" name="diagnosisDescription" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="symptoms">Symptoms</label>
                <textarea class="form-control" id="symptoms" name="symptoms" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="treatmentPlan">Treatment Plan</label>
                <textarea class="form-control" id="treatmentPlan" name="treatmentPlan" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Record Diagnosis</button>
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
