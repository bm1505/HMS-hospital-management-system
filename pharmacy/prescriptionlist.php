<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending prescriptions with patient and doctor names
$sql_pending = "SELECT p.medicationName, p.dosage, p.instructions, pa.first_name AS patient_first_name, pa.last_name AS patient_last_name, 
                      d.firstName AS doctor_first_name, d.middleName AS doctor_middle_name, d.surname AS doctor_surname, p.prescriptionID, p.status
               FROM prescriptions p
               JOIN patients pa ON p.patientID = pa.patientID
               JOIN doctors d ON p.doctorID = d.doctorID
               WHERE p.status = 'Pending'";

$result_pending = $conn->query($sql_pending);

// Fetch finished prescriptions for display on the right side
$sql_finished = "SELECT * FROM finished_prescriptions";
$result_finished = $conn->query($sql_finished);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .table-container {
    width: 48%;
    margin-bottom: 30px; /* Add space below each table */
}

.table-container .table {
    margin-top: 20px;
}

/* Optional: You can increase space between both tables with more padding/margin here */
.row {
    display: flex;
    justify-content: space-between;
    gap: 22px; /* Increase space between the two tables */
}

        .container {
            margin-top: 20px;
        }
        h2 {
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
        }
        h3 {
            color: #1e3d58;
            margin-bottom: 20px;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table th, .table td {
            text-align: center;
            padding: 12px;
            vertical-align: middle;
        }
        .table th {
            background-color: #1e3d58;
            color: white;
            font-weight: 600;
        }
        .table td {
            background-color: #f8f9fa;
            color: #495057;
        }
        .btn-finish {
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            padding: 8px 16px;
            font-weight: bold;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .btn-finish:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .alert {
            font-size: 16px;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
        .table-container {
            width: 40%;
        }
        .table-container .alert {
            text-align: center;
        }
        .table-container h3 {
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .table-container .table {
            margin-top: 20px;
        }
        .table-container .btn-finish {
            width: 100%;
        }
        @media (max-width: 768px) {
            .row {
                flex-direction: column;
                align-items: center;
            }
            .table-container {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Prescription List</h2>
    <div class="row">
        <!-- Left side: Pending prescriptions -->
        <div class="table-container">
            <h3>Pending Prescriptions</h3>
            <?php if ($result_pending->num_rows > 0) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Doctor Name</th>
                            <th>Medication Name</th>
                            <th>Dosage</th>
                            <th>Instructions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result_pending->fetch_assoc()) {
                            $patient_name = $row['patient_first_name'] . ' ' . $row['patient_last_name'];
                            $doctor_name = $row['doctor_first_name'] . ' ' . $row['doctor_middle_name'] . ' ' . $row['doctor_surname'];
                            echo "<tr id='prescription-" . $row['prescriptionID'] . "'>
                                    <td>$patient_name</td>
                                    <td>$doctor_name</td>
                                    <td>" . $row['medicationName'] . "</td>
                                    <td>" . $row['dosage'] . "</td>
                                    <td>" . $row['instructions'] . "</td>
                                    <td>
                                        <button class='btn-finish' onclick='markFinished(" . $row['prescriptionID'] . ", \"$patient_name\", \"" . $row['medicationName'] . "\", \"" . $row['dosage'] . "\", \"" . $row['instructions'] . "\")'>Finish Treatment</button>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-warning" role="alert">
                    No pending prescriptions found.
                </div>
            <?php } ?>
        </div>
<br>

<br>

        <!-- Right side: Finished prescriptions -->
        <div class="table-container">
            <h3>Finished Prescriptions</h3>
            <?php if ($result_finished->num_rows > 0) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Medication Name</th>
                            <th>Dosage</th>
                            <th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result_finished->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row['patientName'] . "</td>
                                    <td>" . $row['medicationName'] . "</td>
                                    <td>" . $row['dosage'] . "</td>
                                    <td>" . $row['instructions'] . "</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-warning" role="alert">
                    No finished prescriptions found.
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

<script>
    function markFinished(prescriptionID, patientName, medicationName, dosage, instructions) {
        // Send AJAX request to finish treatment
        $.ajax({
            url: 'finish_treatment.php',
            type: 'POST',
            data: {
                prescriptionID: prescriptionID,
                patientName: patientName,
                medicationName: medicationName,
                dosage: dosage,
                instructions: instructions
            },
            success: function(response) {
                alert("Treatment marked as finished.");
                // Optionally, remove the prescription row from the table
                $('#prescription-' + prescriptionID).remove(); // Remove the row
            },
            error: function(xhr, status, error) {
                alert("An error occurred: " + error);
            }
        });
    }
</script>

</body>
</html>
