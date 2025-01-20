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

// Handle Doctor Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registerDoctor'])) {
    // Sanitize input data
    $firstName = isset($_POST['firstName']) ? mysqli_real_escape_string($conn, $_POST['firstName']) : '';
    $middleName = isset($_POST['middleName']) ? mysqli_real_escape_string($conn, $_POST['middleName']) : '';
    $surname = isset($_POST['surname']) ? mysqli_real_escape_string($conn, $_POST['surname']) : '';
    $specialization = isset($_POST['specialization']) ? mysqli_real_escape_string($conn, $_POST['specialization']) : '';
    $contactNumber = isset($_POST['contactNumber']) ? mysqli_real_escape_string($conn, $_POST['contactNumber']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $qualification = isset($_POST['qualification']) ? mysqli_real_escape_string($conn, $_POST['qualification']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : '';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($firstName) || empty($middleName) || empty($surname) || empty($specialization) || empty($contactNumber) || empty($email) || empty($qualification) || empty($address) || empty($username) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $conn->autocommit(FALSE); // Start transaction
        try {
            // Insert into doctors table
            $sql_doctor = "INSERT INTO doctors (firstName, middleName, surname, specialization, contactNumber, email, qualification, address) 
                           VALUES ('$firstName', '$middleName', '$surname', '$specialization', '$contactNumber', '$email', '$qualification', '$address')";
            if (!$conn->query($sql_doctor)) {
                throw new Exception("Doctor registration failed: " . $conn->error);
            }

            // Get the last inserted doctor ID
            $doctorID = $conn->insert_id;

            // Insert into users table with the doctorID as foreign key
            $sql_user = "INSERT INTO users (username, password, role) 
                         VALUES ('$username', '$hashed_password', 'doctor')";
            if (!$conn->query($sql_user)) {
                throw new Exception("User registration failed: " . $conn->error);
            }

            // After inserting the user, link the doctorID in the users table
            $sql_update_user = "UPDATE users SET doctorID = $doctorID WHERE username = '$username'";
            if (!$conn->query($sql_update_user)) {
                throw new Exception("Failed to link doctor with user: " . $conn->error);
            }

            $conn->commit();
            $success = "Doctor and user account registered successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

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
    <title>Register Doctor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
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
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
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
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
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
        <h2>Register a Doctor</h2>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" name="firstName" id="firstName" placeholder="First Name" required>
            </div>
            <div class="form-group">
                <label for="middleName">Middle Name</label>
                <input type="text" name="middleName" id="middleName" placeholder="Middle Name" required>
            </div>
            <div class="form-group">
                <label for="surname">Surname</label>
                <input type="text" name="surname" id="surname" placeholder="Surname" required>
            </div>
            <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" name="specialization" id="specialization" placeholder="Specialization" required>
            </div>
            <div class="form-group">
                <label for="contactNumber">Contact Number</label>
                <input type="text" name="contactNumber" id="contactNumber" placeholder="Contact Number" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="qualification">Qualification</label>
                <input type="text" name="qualification" id="qualification" placeholder="Qualification" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" placeholder="Address" required></textarea>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <button type="submit" name="registerDoctor">Register Doctor</button>
            <br>
            
            <br>

            <button type="button" onclick="window.location.href='view_doctors.php'">View Doctors</button>
        </form>
    </div>

   
</body>
</html>
