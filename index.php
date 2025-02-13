<?php
session_start(); // Start the session

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Connect to MySQL
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

// Insert Default Admin if Not Exists
function insertDefaultAdmin($conn) {
    $default_admin_username = "st.norbert.admin";
    $default_admin_password = password_hash("st.123@norAdmin", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("s", $default_admin_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insert_stmt = $conn->prepare("INSERT INTO admin (username, password, role) VALUES (?, ?, ?)");
        if ($insert_stmt === false) {
            die('MySQL prepare error: ' . $conn->error);
        }

        $admin_role = "admin";
        $insert_stmt->bind_param("sss", $default_admin_username, $default_admin_password, $admin_role);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
}
insertDefaultAdmin($conn);

// Handle Login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        // Define login queries with correct ID field names
        $sql_queries = [
            'admin' => "SELECT id, full_name, username, password FROM admin WHERE username = ?",
            'doctor' => "SELECT doctorID, CONCAT(firstName, ' ', middleName) AS full_name, username, password FROM doctors WHERE username = ?",
            'nurse' => "SELECT id, full_name, username, password FROM nurses WHERE username = ?",
            'lab_technician' => "SELECT id, full_name, username, password FROM lab_technicians WHERE username = ?",
            'pharmacist' => "SELECT id, full_name, username, password FROM pharmacists WHERE username = ?",
            'reception' => "SELECT id, full_name, username, password FROM reception WHERE username = ?"
        ];

        $authenticated = false;
        $user_data = null;

        foreach ($sql_queries as $role => $sql) {
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die('MySQL prepare error: ' . $conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $authenticated = true;
                    $user_data = [
                        'id' => isset($row['doctorID']) ? $row['doctorID'] : $row['id'], // Use doctorID for doctors
                        'username' => $row['username'],
                        'role' => $role,
                        'full_name' => $row['full_name']
                    ];
                    break;
                }
            }
            $stmt->close();
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_data['id']; // Store user ID (doctorID for doctors)
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['full_name'] = $user_data['full_name'];

            // Store doctorID separately if the user is a doctor
            if ($user_data['role'] === 'doctor') {
                $_SESSION['doctorID'] = $user_data['id'];
            }

            $redirect_pages = [
                'admin' => "admin/admin_page.php",
                'doctor' => "doctor/doctor.php",
                'nurse' => "nurse/nurse_dashboard.php",
                'lab_technician' => "laboratory/laboratory.php",
                'pharmacist' => "pharmacy/pharmacy.php",
                'reception' => "reception/reception.php"
            ];
            header("Location: " . ($redirect_pages[$user_data['role']] ?? "home.php"));
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - St. Norbert Hospital</title>
    <style>
        /* General Body Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(152, 228, 238); /* Light blue background for a calming effect */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Login Container */
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        /* Hospital Logo or Title */
        .login-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #00796b; /* Dark teal for a professional look */
            font-weight: 600;
        }

        /* Form Styling */
        .login-container form {
            display: flex;
            flex-direction: column;
        }

        /* Input Fields */
        .login-container input {
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #b2dfdb; /* Light teal border */
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .login-container input:focus {
            border-color: #00796b; /* Dark teal on focus */
        }

        /* Login Button */
        .login-container button {
            background-color: #00796b; /* Dark teal */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-container button:hover {
            background-color: #004d40; /* Darker teal on hover */
        }

        /* Error Message */
        .error {
            color: #d32f2f; /* Red for errors */
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }

        /* Additional Styling for Hospital Theme */
        .login-container::before {
            content: "🏥";
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>St. Norbert Hospital</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>