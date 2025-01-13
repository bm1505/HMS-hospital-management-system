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

// Fetch waiting patients
$query = "SELECT * FROM patients WHERE status = 'Waiting'";
$waitingPatients = $conn->query($query);

// Fetch all registered patients ordered by registration time, date, and year
$query = "SELECT * FROM patients ORDER BY created_at DESC"; // Assuming 'created_at' is the timestamp field
$allPatientsResult = $conn->query($query);
$allPatients = [];
if ($allPatientsResult) {
    while ($row = $allPatientsResult->fetch_assoc()) {
        $allPatients[] = $row;
    }
} else {
    echo "Error fetching all patients: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient - St. Norbert Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .container {
            margin-top: 30px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn-container a {
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Navigation Buttons -->
        <div class="btn-container">
            <a href="patient_registration.php" class="btn btn-primary">Back to Patient Registration</a>
            <a href="../index.php" class="btn btn-danger">Logout</a>
        </div>

        <div class="row">
            <!-- Right Side: List of Patients -->
            <div class="col-md-12">
                <h4>All Registered Patients</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPatients as $patient) { ?>
                            <tr>
                                <td><?= $patient['patientID'] ?></td>
                                <td><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></td>
                                <td><?= $patient['status'] ?></td>
                                <td><?= $patient['created_at'] ?></td> <!-- Assuming 'created_at' stores the registration date -->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 St. Norbert Hospital</p>
    </div>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
