<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Handle Doctor Deletion
if (isset($_GET['delete'])) {
    $doctor_id = $_GET['delete'];
    $sql_delete = "DELETE FROM doctors WHERE doctor_id = $doctor_id";
    if ($conn->query($sql_delete)) {
        $success = "Doctor record deleted successfully!";
    } else {
        $error = "Failed to delete doctor record!";
    }
}

// Fetch all doctors from database
$doctor_list = [];
$sql_doctors = "SELECT * FROM doctors";
$result = $conn->query($sql_doctors);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctor_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctors</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .doctor-list {
            margin-top: 30px;
        }
        .doctor-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .doctor-list th, .doctor-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .doctor-list th {
            background-color: #f2f2f2;
        }
        .doctor-list .actions button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }
        .doctor-list .actions button:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Doctors List</h2>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <div class="doctor-list">
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Surname</th>
                        <th>Specialization</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Qualification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctor_list as $doctor) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doctor['firstName']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['middleName']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['surname']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['contactNumber']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['qualification']); ?></td>
                        <td class="actions">
                            <!-- Edit Button -->
                           
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
