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

// Handle form submission for recording patient vitals and personal information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientID = $_POST['patientID'];
    $weight = $_POST['weight'];
    $blood_pressure = $_POST['blood_pressure'];
    $temperature = $_POST['temperature'];
    $height = $_POST['height'];
    $other_notes = $_POST['other_notes'];
    $dateOfBirth = $_POST['dateOfBirth'];
    $gender = $_POST['gender'];
    $phone_number = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $insurance_number = $_POST['insurance_number'];
    $emergency_contact = $_POST['emergency_contact'];
    $doctor_type = $_POST['doctor_type'];

    // Check for empty fields
    if (empty($phone_number) || empty($email) || empty($address)) {
        $message = "Error: Please fill all the required fields.";
    } else {
        $stmt = $conn->prepare("
        INSERT INTO patient_vitals 
        (patientID, weight, blood_pressure, temperature, height, other_notes, dateOfBirth, gender, phone, email, address, insurance_number, emergency_contact, doctor_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $stmt->bind_param("iddsdsisssssss", 
            $patientID, $weight, $blood_pressure, $temperature, $height, 
            $other_notes, $dateOfBirth, $gender, $phone_number, $email, 
            $address, $insurance_number, $emergency_contact, $doctor_type
        );

        if ($stmt->execute()) {
            $message = "Patient vitals and personal information recorded successfully!";
            // Redirect to view_vitals.php after successful save
            header("Location: view_vitals.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch patient data based on patientID if available
$patient = null;
if (isset($_GET['patientID'])) {
    $patientID = $_GET['patientID'];
    $sql = "SELECT patientID, first_name, last_name, dateOfBirth, gender, phone, email, address, insurance_number, emergency_contact, doctor_type
            FROM patients
            WHERE patientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($patientID, $first_name, $last_name, $dateOfBirth, $gender, $phone, $email, $address, $insurance_number, $emergency_contact, $doctor_type);

    if ($stmt->fetch()) {
        $patient = [
            'patientID' => $patientID,
            'name' => $first_name . ' ' . $last_name,
            'dob' => $dateOfBirth,
            'gender' => $gender,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'insurance_number' => $insurance_number,
            'emergency_contact' => $emergency_contact,
            'doctor_type' => $doctor_type
            
        ];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Signs - Nurse Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        h1 {
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between">
        <a href="nurse_dashboard.php" class="btn btn-secondary">Back</a>
        <a href="index.php" class="btn btn-danger">Logout</a>
    </div>
    <h1 class="text-center mb-4">Nurse Dashboard - Record Patient Vitals</h1>
    <hr>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4">
                <h4 class="mb-3">Record Patient Vitals</h4>
                <form method="POST">
                    <!-- Patient ID -->
                    <div class="mb-3">
                        <label for="patientID" class="form-label">Patient ID</label>
                        <input type="number" name="patientID" id="patientID" class="form-control" value="<?= $patient['patientID'] ?? '' ?>" required readonly>
                    </div>

                    <!-- Patient Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Patient Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $patient['name'] ?? '' ?>" required readonly>
                    </div>

                    <!-- Date of Birth -->
                    <div class="mb-3">
                        <label for="dateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" name="dateOfBirth" id="dateOfBirth" class="form-control" value="<?= $patient['dob'] ?? '' ?>" required>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option value="Male" <?= isset($patient['gender']) && $patient['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= isset($patient['gender']) && $patient['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= isset($patient['gender']) && $patient['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?= $patient['phone'] ?? '' ?>" required>
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= $patient['email'] ?? '' ?>" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required><?= $patient['address'] ?? '' ?></textarea>
                    </div>

                    <!-- Insurance Number -->
                    <div class="mb-3">
                        <label for="insurance_number" class="form-label">Insurance Number</label>
                        <input type="text" name="insurance_number" id="insurance_number" class="form-control" value="<?= $patient['insurance_number'] ?? '' ?>" required>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" value="<?= $patient['emergency_contact'] ?? '' ?>" required>
                    </div>

                    <!--doctory type -->
                    <div class="mb-3">
                        <label for="doctor_type" class="form-label">Doctor Type</label>
                        <input type="text" name="doctor_type" id="doctor_type" class="form-control" value="<?= $patient['doctor_type'] ?? '' ?>" required>
                    </div>
                    <!-- Patient Vitals -->
                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" id="weight" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="blood_pressure" class="form-label">Blood Pressure (mmHg)</label>
                        <input type="text" name="blood_pressure" id="blood_pressure" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="temperature" class="form-label">Body Temperature (°C)</label>
                        <input type="number" step="0.1" name="temperature" id="temperature" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="height" class="form-label">Height (cm)</label>
                        <input type="number" step="0.1" name="height" id="height" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="other_notes" class="form-label">Other Notes</label>
                        <textarea name="other_notes" id="other_notes" class="form-control" rows="3"></textarea>
                    </div>
                   
                    <button type="submit" class="btn btn-primary w-100">Save Vitals</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
