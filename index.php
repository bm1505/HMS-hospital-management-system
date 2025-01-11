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

// Insert default admin if not exists
$default_admin_username = "st.norbert.admin";
$default_admin_password = password_hash("st.123@norAdmin", PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $default_admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $admin_role = "admin";
    $insert_stmt->bind_param("sss", $default_admin_username, $default_admin_password, $admin_role);
    $insert_stmt->execute();
    $insert_stmt->close();
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                switch ($row['role']) {
                    case 'admin':
                        header("Location: admin/admin_page.php");
                        break;
                    case 'doctor':
                        header("Location: doctor/doctor_dashboard.php");
                        break;
                    case 'nurse':
                        header("Location: nurse/nurse_dashboard.php");
                        break;
                    case 'lab_technician':
                        header("Location: lab/lab_dashboard.php");
                        break;
                    case 'pharmacist':
                        header("Location: pharmacist/pharmacist_dashboard.php");
                        break;
                    default:
                        header("Location: home.php");
                        break;
                }
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            margin: 0 0 20px;
            text-align: center;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>St. Norbert Hospital Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
