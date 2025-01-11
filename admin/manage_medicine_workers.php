<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Medicine
    if (isset($_POST['addMedicine'])) {
        $medicineName = mysqli_real_escape_string($conn, $_POST['medicineName']);
        $cost = mysqli_real_escape_string($conn, $_POST['cost']);
        $details = mysqli_real_escape_string($conn, $_POST['details']);

        if (empty($medicineName) || empty($cost) || empty($details)) {
            $error = "All fields for medicine are required!";
        } else {
            $sql = "INSERT INTO medicines (medicineName, cost, details) VALUES ('$medicineName', '$cost', '$details')";
            if (mysqli_query($conn, $sql)) {
                $success = "Medicine added successfully!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }

    // Register Worker
    if (isset($_POST['registerWorker'])) {
        $workerName = mysqli_real_escape_string($conn, $_POST['workerName']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);

        if (empty($workerName) || empty($role) || empty($contact)) {
            $error = "All fields for worker registration are required!";
        } else {
            $sql = "INSERT INTO workers (workerName, role, contact) VALUES ('$workerName', '$role', '$contact')";
            if (mysqli_query($conn, $sql)) {
                $success = "Worker registered successfully!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines & Workers</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Medicines</h2>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Medicine Name</label>
                <input type="text" class="form-control" name="medicineName" required>
            </div>
            <div class="form-group">
                <label>Cost</label>
                <input type="text" class="form-control" name="cost" required>
            </div>
            <div class="form-group">
                <label>Details</label>
                <textarea class="form-control" name="details" rows="3" required></textarea>
            </div>
            <button type="submit" name="addMedicine" class="btn btn-primary">Add Medicine</button>
        </form>

        <h2 class="mt-5">Register Worker</h2>
        <form method="POST">
            <div class="form-group">
                <label>Worker Name</label>
                <input type="text" class="form-control" name="workerName" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" class="form-control" name="role" required>
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" class="form-control" name="contact" required>
            </div>
            <button type="submit" name="registerWorker" class="btn btn-primary">Register Worker</button>
        </form>
    </div>
</body>
</html>
<style>
    body {
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    color: #333;
    margin: 0;
    padding: 0;
}

.container {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 40px auto;
}

h2 {
    color: #007bff;
    font-weight: bold;
    margin-bottom: 20px;
    text-align: center;
}

.form-group label {
    font-weight: bold;
    color: #555;
}

.form-group input,
.form-group textarea {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 10px;
    width: 100%;
    font-size: 16px;
}

.btn-primary {
    background-color: #007bff;
    border: none;
    font-size: 16px;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.alert {
    margin-top: 10px;
    font-size: 14px;
}

textarea {
    resize: none;
}

</style>