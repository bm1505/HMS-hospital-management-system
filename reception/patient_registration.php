<?php
// Start session and include database connection
session_start();

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

// Initialize variables for messages
$message = '';

// Handle the action to send patient to nurse or remove from list
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_to_nurse'])) {
        $patientID = $_POST['patientID'];
        // Update patient status to "under treatment"
        $stmt = $conn->prepare("UPDATE patients SET status = 'under treatment' WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['remove_patient'])) {
        $patientID = $_POST['patientID'];
        
        // Delete related records from patient_vitals table
        $stmt = $conn->prepare("DELETE FROM patient_vitals WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $stmt->close();

        // Now remove patient from the patients table
        $stmt = $conn->prepare("DELETE FROM patients WHERE patientID = ?");
        $stmt->bind_param("i", $patientID);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['register_patient'])) {
        // Insert new patient
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $dateOfBirth = $_POST['dateOfBirth'];
        $gender = $_POST['gender'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $insurance_number = $_POST['insurance_number'];
        $emergency_contact = $_POST['emergency_contact'];
        $relationship = $_POST['relationship'];

        // Insert patient into database
        $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dateOfBirth, gender, phone, email, address, insurance_number, emergency_contact, relationship, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'waiting')");
        $stmt->bind_param("ssssssssss", $first_name, $last_name, $dateOfBirth, $gender, $phone, $email, $address, $insurance_number, $emergency_contact, $relationship);
        if ($stmt->execute()) {
            $message = "<div class='alert'>Patient successfully registered!</div>";
        } else {
            $message = "<div class='alert'>Error registering patient. Please try again.</div>";
        }
        $stmt->close();
    }
}

// Fetch patients with status "waiting"
$waitingPatients = $conn->query("SELECT * FROM patients WHERE status='waiting'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - St. Norbert Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Add this part after the form, where you want the buttons to appear -->
   <!-- Navbar -->
   <nav class="navbar navbar-expand-lg navbar-dark" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="btn-container" style="display: flex; justify-content: flex-start; align-items: center;">
            <a href="reception.php" class="btn btn-primary" style="padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease; background-color: yellow; color: black; text-decoration: none; display: inline-block;">Back</a>
        </div>
        <div class="btn-container" style="display: flex; justify-content: flex-end; align-items: center;">
            <a href="../index.php" class="btn btn-danger" style="padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease; background-color: #e74c3c; color: white; text-decoration: none; display: inline-block;">Logout</a>
        </div>
    </nav>
    <style>
        /* Optional: Adding custom styles for navbar and buttons */
        .navbar {
            background-color: #3498db; /* Blue background */
            padding: 10px;
        }
        .navbar .btn {
            margin: 0 5px; /* Space between buttons */
        }
    </style>


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
/* General Button Styles */
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

/* Primary Button (Send to Nurse) */
.btn-primary {
    background-color: #3498db;
    color: white;
}
.btn-primary:hover {
    background-color: #2980b9;
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
}

/* Danger Button (Remove) */
.btn-danger {
    background-color: #e74c3c;
    color: white;
}
.btn-danger:hover {
    background-color: #c0392b;
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

/* Small Button Styling */
.btn-sm {
    padding: 8px 16px;
    font-size: 12px;
    border-radius: 6px;
}

/* Focused Button Effect */
.btn:focus, .btn:active {
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.5);
}

        .btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #27ae60;
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
                <p class="text-center text-muted"><marquee behavior="slow" direction="left">Fill the form to register a new patient.</marquee></p>
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
                        <label for="relationship" class="form-label">Relationship to Emergency Contact</label>
                        <input type="text" class="form-control" id="relationship" name="relationship">
                    </div>
                    <div class="text-center">
                       
                    <button type="submit" name="register_patient" class="btn btn-primary">Register Patient</button>
                    <br>
                    
                    <br>
                    <button type="button" class="btn btn-lg btn-info" onclick="window.location.href='viewpatient.php'" style="display: block; margin: 0 auto; text-align: center;">
    <i class="bi bi-eye"></i> View All Registered Patients
</button>

                    </div>

                </form>
            </div>
       
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

            <!-- Right Side: Registered Patients List -->
            <div class="col-md-6 right-column">
                <h1 class="text-center">Waiting Patients</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $waitingPatients->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['patientID'] ?></td>
                                <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="patientID" value="<?= $row['patientID'] ?>">
                                        <button type="submit" name="send_to_nurse" class="btn btn-primary btn-sm">Send to Nurse</button>
                                        <button type="submit" name="remove_patient" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <br>
    <br>
    
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
