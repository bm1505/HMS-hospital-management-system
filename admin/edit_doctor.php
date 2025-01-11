<?php
// Start session for managing session data
session_start();

// Database connection variables
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctor details for editing
if (isset($_GET['id'])) {
    $doctor_id = $_GET['id'];
    $sql_doctor = "SELECT * FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql_doctor);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
}

// Handle doctor update
if (isset($_POST['update'])) {
    $doctor_id = $_POST['doctor_id'];
    $first_name = $_POST['firstName'];
    $middle_name = $_POST['middleName'];
    $surname = $_POST['surname'];
    $specialization = $_POST['specialization'];
    $contact_number = $_POST['contactNumber'];
    $email = $_POST['email'];

    $update_sql = "UPDATE doctors SET firstName=?, middleName=?, surname=?, specialization=?, contactNumber=?, email=? WHERE doctor_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $surname, $specialization, $contact_number, $email, $doctor_id);

    if ($stmt->execute()) {
        echo "<script>alert('Doctor updated successfully'); window.location.href='view_doctors.php';</script>";
    } else {
        echo "<script>alert('Error updating doctor');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor</title>
</head>
<body>
    <div class="container">
        <h2>Edit Doctor Information</h2>
        <form action="edit_doctor.php" method="POST">
            <input type="hidden" name="doctor_id" value="<?= $doctor['doctor_id'] ?>">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($doctor['firstName']) ?>" required><br><br>

            <label for="middleName">Middle Name:</label>
            <input type="text" id="middleName" name="middleName" value="<?= htmlspecialchars($doctor['middleName']) ?>" required><br><br>

            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($doctor['surname']) ?>" required><br><br>

            <label for="specialization">Specialization:</label>
            <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($doctor['specialization']) ?>" required><br><br>

            <label for="contactNumber">Contact Number:</label>
            <input type="text" id="contactNumber" name="contactNumber" value="<?= htmlspecialchars($doctor['contactNumber']) ?>" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($doctor['email']) ?>" required><br><br>

            <button type="submit" name="update">Update Doctor</button>
        </form>
    </div>
</body>
</html>
