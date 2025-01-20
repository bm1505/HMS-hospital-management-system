<?php
// Start the session
session_start();

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure nurseID is set in the session
if (!isset($_SESSION['nurseID'])) {
    die("Error: You must log in to access this page.");
}
$nurseID = $_SESSION['nurseID'];

// Fetch the role of the logged-in user
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $nurseID);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Check if the user is authorized
if ($role !== 'nurse') {
    die("Error: You do not have the required permissions to access this page.");
}

// Initialize message variable
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_to_nurse'])) {
        $patientID = $_POST['patientID'];

        // Update patient status and insert treatment record
        $stmt = $conn->prepare("UPDATE patients SET status = 'under treatment' WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO patient_treatment (patientID, status, sent_to_nurse, nurseID) VALUES (?, 'under treatment', NOW(), ?)");
            $stmt->bind_param("ii", $patientID, $nurseID);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Patient sent to nurse successfully.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error recording treatment. Please try again.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Error updating patient status. Please try again.</div>";
        }
        $stmt->close();
    } elseif (isset($_POST['remove_patient'])) {
        // Remove patient and related records
        $patientID = $_POST['patientID'];
        $stmt = $conn->prepare("DELETE FROM patient_vitals WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM patients WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Patient removed successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error removing patient. Please try again.</div>";
        }
        $stmt->close();
    } elseif (isset($_POST['register_patient'])) {
        // Register new patient
        $first_name = htmlspecialchars($_POST['first_name']);
        $last_name = htmlspecialchars($_POST['last_name']);
        $dateOfBirth = $_POST['dateOfBirth'];
        $gender = $_POST['gender'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $insurance_number = $_POST['insurance_number'];
        $emergency_contact = $_POST['emergency_contact'];
        $relationship = $_POST['relationship'];
        $doctor_type = $_POST['doctor_type'];

        $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dateOfBirth, gender, phone, email, address, insurance_number, emergency_contact, relationship, status, doctor_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'waiting', ?)");
        $stmt->bind_param("sssssssssss", $first_name, $last_name, $dateOfBirth, $gender, $phone, $email, $address, $insurance_number, $emergency_contact, $relationship, $doctor_type);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Patient successfully registered!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error registering patient. Please try again.</div>";
        }
        $stmt->close();
    }
}

// Fetch waiting patients
$waitingPatients = $conn->query("SELECT * FROM patients WHERE status = 'waiting'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - St. Norbert Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin-top: 30px;
        }

        h1, h4 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .table thead {
            background-color: #3498db;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: center;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #ecf0f1;
        }

        .table-striped tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .alert {
            font-size: 1rem;
            padding: 10px 20px;
            background-color: #f39c12;
            color: white;
            border-radius: 5px;
        }

        .left-column {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .right-column {
            overflow-x: auto;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: inline-block;
            border: none;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            border-top: 1px solid #ddd;
        }

        footer a {
            color: #ecf0f1;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Left Side: Patient Registration Form -->
            <div class="col-md-6 left-column">
                <h1 class="text-center">Patient Registration</h1>
                <p class="text-center text-muted"><marquee behavior="slow" direction="left"  >Fill the form to register a new patient.</marquee></p>
                <?= $message ?>
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dateOfBirth" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" required>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="" disabled selected>Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="insurance_number" class="form-label">Insurance Number</label>
                            <input type="text" class="form-control" id="insurance_number" name="insurance_number">
                        </div>
                        <div class="col-md-6">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="relationship" class="form-label">Relationship with Emergency Contact</label>
                        <input type="text" class="form-control" id="relationship" name="relationship">
                    </div>
                    <div class="mb-3">
                        <label for="doctor_type" class="form-label">Doctor Type *</label>
                        <select class="form-select" id="doctor_type" name="doctor_type" required>
                            <option value="" disabled selected>Select Doctor Type</option>
                            <option value="born">born</option>
                            <option value="normal">normal</option>
                            <option value="heart">heart</option>
                            <option value="skin">skin</option>
                        </select>
                    </div>
                    <button type="submit" name="register_patient" class="btn btn-primary w-100">Register Patient</button>
                </form>
            </div>

            <!-- Right Side: Patients Waiting for Treatment -->
            <div class="col-md-6 right-column">
                <h1 class="text-center">Patients Waiting for Treatment</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date of Birth</th>
                            <th>Doctor Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($waitingPatients->num_rows > 0) {
                            while ($patient = $waitingPatients->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($patient['first_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($patient['last_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($patient['dateOfBirth']) . "</td>";
                                echo "<td>" . htmlspecialchars($patient['doctor_type']) . "</td>";
                                echo "<td>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='patientID' value='" . $patient['patientID'] . "'>
                                            <button type='submit' name='send_to_nurse' class='btn btn-primary'>Send to Nurse</button>
                                        </form>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='patientID' value='" . $patient['patientID'] . "'>
                                            <button type='submit' name='remove_patient' class='btn btn-danger'>Remove</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No patients waiting.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 St. Norbert Hospital. All Rights Reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
