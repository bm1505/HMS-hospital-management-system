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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registerWorker'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } else {
        // Check if the username already exists
        $check_username = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($check_username);
        
        if ($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Insert into users table
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
            if ($conn->query($sql) === TRUE) {
                $success = "Worker registered successfully!";
            } else {
                $error = "Registration failed: " . $conn->error;
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
    <title>Register Worker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
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
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Register Worker</h2>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="lab_technician">Lab Technician</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="user">reception</option>
                </select>
            </div>
            <button type="submit" name="registerWorker">Register Worker</button>
        </form>
    </div>
</body>
</html>
