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
    // Sanitize input data
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($full_name) || empty($age) || empty($gender) || empty($email) || empty($address) || 
        empty($specialization) || empty($qualification) || empty($marital_status) || 
        empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } else {
        // Check if the username already exists in any role table
        $tables = ['doctors', 'nurses', 'lab_technicians', 'pharmacists', 'reception'];
        $username_exists = false;

        foreach ($tables as $table) {
            $check_username = "SELECT * FROM $table WHERE username = '$username'";
            $result = $conn->query($check_username);
            if ($result->num_rows > 0) {
                $username_exists = true;
                break;
            }
        }

        if ($username_exists) {
            $error = "Username already exists!";
        } else {
            // Determine the table based on the selected role
            $table_name = "";
            switch ($role) {
                case 'doctor':
                    $table_name = 'doctors';
                    break;
                case 'nurse':
                    $table_name = 'nurses';
                    break;
                case 'lab_technician':
                    $table_name = 'lab_technicians';
                    break;
                case 'pharmacist':
                    $table_name = 'pharmacists';
                    break;
                case 'reception':
                    $table_name = 'reception';
                    break;
                default:
                    $error = "Invalid role selected!";
                    break;
            }

            if (!empty($table_name)) {
                // Insert into the appropriate table
                $sql = "INSERT INTO $table_name (full_name, age, gender, email, address, specialization, 
                        qualification, marital_status, username, password) 
                        VALUES ('$full_name', '$age', '$gender', '$email', '$address', '$specialization', 
                        '$qualification', '$marital_status', '$username', '$hashed_password')";
                if ($conn->query($sql) === TRUE) {
                    $success = "Worker registered successfully as a $role!";
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
            }
        }
    }
}
?>
    <style>
        /* Styling remains unchanged */
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
        input[type="number"],
        input[type="email"],
        select,
        textarea {
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
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" name="age" id="age" placeholder="Age" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="3" placeholder="Address" required></textarea>
            </div>
            <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" name="specialization" id="specialization" placeholder="Specialization" required>
            </div>
            <div class="form-group">
                <label for="qualification">Qualification</label>
                <input type="text" name="qualification" id="qualification" placeholder="Qualification" required>
            </div>
            <div class="form-group">
                <label for="marital_status">Marital Status</label>
                <select name="marital_status" id="marital_status" required>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="divorced">Divorced</option>
                </select>
            </div>
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
                    <option value="reception">Reception</option>
                </select>
            </div>
            <button type="submit" name="registerWorker">Register Worker</button>
        </form>
    </div>
</body>
</html>
