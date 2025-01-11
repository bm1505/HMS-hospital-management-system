<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital"; // Updated database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to get list of all prescriptions
function getPrescriptions($conn) {
    $sql = "SELECT * FROM prescriptions";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Add a new prescription
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patientID = $_POST['patientID'];
    $doctorID = $_POST['doctorID'];
    $medicationName = $_POST['medicationName'];
    $quantity = $_POST['quantity'];
    $dosage = $_POST['dosage'];
    $dateIssued = $_POST['dateIssued'];

    $sql = "INSERT INTO prescriptions (patientID, doctorID, medicationName, quantity, dosage, dateIssued) 
            VALUES ('$patientID', '$doctorID', '$medicationName', '$quantity', '$dosage', '$dateIssued')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Prescription added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Delete prescription
if (isset($_GET['delete'])) {
    $prescriptionID = $_GET['delete'];
    $sql = "DELETE FROM prescriptions WHERE prescriptionID = '$prescriptionID'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Prescription deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch prescriptions list
$prescriptions = getPrescriptions($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Prescription Management</h2>

    <!-- Add Prescription Form -->
    <form method="POST" action="prescription_fulfillment.php">
        <div class="form-group">
            <label for="patientID">Patient ID</label>
            <input type="text" class="form-control" id="patientID" name="patientID" required>
        </div>

        <div class="form-group">
            <label for="doctorID">Doctor ID</label>
            <input type="text" class="form-control" id="doctorID" name="doctorID" required>
        </div>

        <div class="form-group">
            <label for="medicationName">Medication Name</label>
            <input type="text" class="form-control" id="medicationName" name="medicationName" required>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>

        <div class="form-group">
            <label for="dosage">Dosage</label>
            <input type="text" class="form-control" id="dosage" name="dosage" required>
        </div>

        <div class="form-group">
            <label for="dateIssued">Date Issued</label>
            <input type="date" class="form-control" id="dateIssued" name="dateIssued" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Prescription</button>
    </form>

    <hr>

    <h3>Prescriptions List</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Doctor ID</th>
                <th>Medication Name</th>
                <th>Quantity</th>
                <th>Dosage</th>
                <th>Date Issued</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prescriptions as $prescription): ?>
                <tr>
                    <td><?php echo $prescription['patientID']; ?></td>
                    <td><?php echo $prescription['doctorID']; ?></td>
                    <td><?php echo $prescription['medicationName']; ?></td>
                    <td><?php echo $prescription['quantity']; ?></td>
                    <td><?php echo $prescription['dosage']; ?></td>
                    <td><?php echo $prescription['dateIssued']; ?></td>
                    <td>
                        <a href="prescription_fulfillment.php?delete=<?php echo $prescription['prescriptionID']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<footer class="footer bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2024 Pharmacy Management System</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
