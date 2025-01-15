<!-- view_vitals.php -->
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

// Fetch patient vitals
$vitals_query = "
    SELECT 
        pv.*, 
        p.first_name, 
        p.last_name, 
        p.dateOfBirth, 
        p.gender, 
        p.phone, 
        p.email, 
        p.address, 
        p.insurance_number, 
        p.emergency_contact
    FROM 
        patient_vitals pv
    JOIN 
        patients p 
    ON 
        pv.patientID = p.patientID 
    ORDER BY 
        pv.created_at DESC";
$vitals_result = $conn->query($vitals_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Vitals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        h1 {
            font-weight: 600;
            color: #0d6efd;
        }
        .card {
            border: none;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #0d6efd;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
<div>
    <h1 class="text-center">Patient Vitals</h1>
    <hr>

    <div class="card p-4 mt-4">
        <h4 class="mb-3">Recorded Vitals</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Email Address</th>
                    <th>Address</th>
                    <th>Insurance Number</th>
                    <th>Emergency Contact</th>
                    <th>Weight (kg)</th>
                    <th>Blood Pressure</th>
                    <th>Temperature (°C)</th>
                    <th>Height (cm)</th>
                    <th>Other Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Database connection
                $servername = "localhost";
                $db_username = "root";
                $db_password = "";
                $dbname = "st_norbert_hospital";

                $conn = new mysqli($servername, $db_username, $db_password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $vitals_query = "
                    SELECT 
                        pv.*, 
                        p.first_name, 
                        p.last_name, 
                        p.dateOfBirth, 
                        p.gender, 
                        p.phone, 
                        p.email, 
                        p.address, 
                        p.insurance_number, 
                        p.emergency_contact
                    FROM 
                        patient_vitals pv
                    JOIN 
                        patients p 
                    ON 
                        pv.patientID = p.patientID 
                    ORDER BY 
                        pv.created_at DESC";

                $vitals_result = $conn->query($vitals_query);

                if ($vitals_result && $vitals_result->num_rows > 0):
                    while ($vital = $vitals_result->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= htmlspecialchars($vital['patientID']) ?></td>
                            <td><?= htmlspecialchars($vital['first_name'] . ' ' . $vital['last_name']) ?></td>
                            <td><?= htmlspecialchars($vital['dateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($vital['gender']) ?></td>
                            <td><?= htmlspecialchars($vital['phone']) ?></td>
                            <td><?= htmlspecialchars($vital['email']) ?></td>
                            <td><?= htmlspecialchars($vital['address']) ?></td>
                            <td><?= htmlspecialchars($vital['insurance_number']) ?></td>
                            <td><?= htmlspecialchars($vital['emergency_contact']) ?></td>
                            <td><?= htmlspecialchars($vital['weight']) ?></td>
                            <td><?= htmlspecialchars($vital['blood_pressure']) ?></td>
                            <td><?= htmlspecialchars($vital['temperature']) ?></td>
                            <td><?= htmlspecialchars($vital['height']) ?></td>
                            <td><?= htmlspecialchars($vital['other_notes']) ?></td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="14" class="text-center">No vitals recorded yet.</td>
                    </tr>
                <?php
                endif;
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
