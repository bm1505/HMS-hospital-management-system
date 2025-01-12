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

// Handle form submission for recording patient vitals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientID = $_POST['patientID'];
    $weight = $_POST['weight'];
    $blood_pressure = $_POST['blood_pressure'];
    $temperature = $_POST['temperature'];
    $height = $_POST['height'];
    $other_notes = $_POST['other_notes'];

    $stmt = $conn->prepare("
        INSERT INTO patient_vitals (patientID, weight, blood_pressure, temperature, height, other_notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iddsds", $patientID, $weight, $blood_pressure, $temperature, $height, $other_notes);

    if ($stmt->execute()) {
        $message = "Patient vitals recorded successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch patient vitals
$vitals_query = "
    SELECT 
        pv.*, 
        p.firstName, 
        p.lastName 
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
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        h1, h4 {
            color: #343a40;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .alert {
            font-size: 0.9rem;
        }
        .left-column {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .right-column {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Nurse Dashboard</h1>
    <hr>

    <!-- Display message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Patient Vitals Form -->
        <div class="col-md-5 left-column">
            <form method="POST" class="p-4">
                <h4 class="mb-3">Record Patient Vitals</h4>
                <div class="mb-3">
                    <label for="patientID" class="form-label">Patient ID</label>
                    <input type="number" name="patientID" id="patientID" class="form-control" required>
                </div>

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

                <button type="submit" class="btn btn-primary">Save Vitals</button>
            </form>
        </div>

        <!-- Right Column: Patient Vitals Table -->
        <div class="col-md-7 right-column">
            <h4 class="mb-3">Recorded Patient Vitals</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Weight (kg)</th>
                        <th>Blood Pressure (mmHg)</th>
                        <th>Temperature (°C)</th>
                        <th>Height (cm)</th>
                        <th>Notes</th>
                        <th>Date Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vital = $vitals_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($vital['firstName'] . " " . $vital['lastName']) ?></td>
                            <td><?= htmlspecialchars($vital['weight']) ?></td>
                            <td><?= htmlspecialchars($vital['blood_pressure']) ?></td>
                            <td><?= htmlspecialchars($vital['temperature']) ?></td>
                            <td><?= htmlspecialchars($vital['height']) ?></td>
                            <td><?= htmlspecialchars($vital['other_notes']) ?></td>
                            <td><?= htmlspecialchars($vital['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
