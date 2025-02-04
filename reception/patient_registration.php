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

// Fetch the role of the logged-in user
$stmt = $conn->prepare("SELECT role FROM nurses WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

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
                $message = "<div class='alert alert-success' id='message'>Patient sent to nurse successfully.</div>";
            } else {
                $message = "<div class='alert alert-danger' id='message'>Error recording treatment. Please try again.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger' id='message'>Error updating patient status. Please try again.</div>";
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
            $message = "<div class='alert alert-success' id='message'>Patient removed successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger' id='message'>Error removing patient. Please try again.</div>";
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
            $message = "<div class='alert alert-success' id='message'>Patient successfully registered!</div>";
        } else {
            $message = "<div class='alert alert-danger' id='message'>Error registering patient. Please try again.</div>";
        }
        $stmt->close();
    }
}

// Fetch waiting patients
$waitingPatients = $conn->query("SELECT * FROM patients WHERE status = 'waiting'");
?>

<!-- Add JavaScript to hide the message after 5 seconds -->
<script>
    // Check if a message is present
    window.onload = function() {
        var message = document.getElementById('message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none'; // Hide the message
            }, 5000); // 5000 milliseconds = 5 seconds
        }
    };
</script>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - St. Norbert Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            font-weight: bold;
        }

        .form-control, .form-select {
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #3498db;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .table {
            margin-top: 20px;
        }

        .table thead {
            background-color: #3498db;
            color: white;
        }

        .alert {
            margin-top: 20px;
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
                <th>Patient ID</th> <!-- Added Patient ID column -->
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
                    echo "<td>" . htmlspecialchars($patient['patientID']) . "</td>"; // Displaying patientID
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
                echo "<tr><td colspan='6' class='text-center'>No patients waiting.</td></tr>"; // Adjusted colspan to 6
            }
            ?>
        </tbody>
    </table>
</div>

</html>

<?php
// Close database connection
$conn->close();
?>
