<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: ../index.php");
    exit;
}

// Get the username from the session
$username = $_SESSION['username'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Module Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color:rgb(95, 172, 250);
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        body, html {
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color:rgb(187, 243, 201);
            box-shadow: var(--card-shadow);
            padding: 1rem 2rem;
        }

        .navbar-text {
            font-size: 1rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        .logout-btn {
            background-color: var(--accent-color);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .notifications {
            position: relative;
            cursor: pointer;
            margin-right: 1.5rem;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: var(--accent-color);
            color: white;
            font-size: 0.8rem;
            border-radius: 50%;
            padding: 3px 7px;
        }

        .notification-popup {
            position: absolute;
            top: 40px;
            right: 0;
            background-color: white;
            border: 1px solid #e0e0e0;
            box-shadow: var(--card-shadow);
            padding: 10px;
            width: 250px;
            display: none;
            z-index: 10;
            border-radius: 8px;
        }

        .notification-item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }

        .notification-item:hover {
            background-color:rgb(248, 250, 250);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notifications:hover .notification-popup {
            display: block;
        }

        .container {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        .card {
            width: 22rem;
            margin: 1rem;
            text-align: center;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }

        .card-text {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: center;
            margin-top: auto;
            font-size: 0.9rem;
        }

        #currentDateTime {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <span class="navbar-text">
                 <strong><?php echo htmlspecialchars($username); ?></strong>
            </span>
            <span class="navbar-text ml-auto" id="currentDateTime"></span>
            <div class="notifications">
                <a href="#" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                </a>
                <div class="notification-popup">
                    <div class="notification-item"><a href="notificationPage.php">New Appointment</a></div>
                    <div class="notification-item"><a href="notificationPage.php">Patient Diagnosis</a></div>
                    <div class="notification-item"><a href="notificationPage.php">Prescription Update</a></div>
                </div>
            </div>
            <form action="../logout.php" method="POST" class="ml-3">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <!-- Dashboard Modules -->
    <div class="container">
        <!-- Module Cards -->
        <?php 
        $modules = [
            ["Appointment Scheduling", "fas fa-calendar-check", "Schedule appointments for doctors and patients.", "scheduleAppointment.php"],
            ["Patient Diagnosis", "fas fa-user-md", "Access medical history, record diagnoses, and prescribe treatments.", "patient_diagnosis.php"],
            ["Electronic Medical Records", "fas fa-file-medical", "View and update patient medical history and doctor's notes.", "emr.php"],
            ["Prescription Management", "fas fa-prescription", "Create and send prescriptions to the pharmacy.", "prescription.php"]
        ];

        foreach ($modules as $module) { ?>
        <div class="card">
            <div class="card-body">
                <div class="icon"><i class="<?php echo $module[1]; ?>"></i></div>
                <h5 class="card-title"><?php echo $module[0]; ?></h5>
                <p class="card-text"><?php echo $module[2]; ?></p>
                <a href="<?php echo $module[3]; ?>" class="btn btn-primary">Explore</a>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Hospital Management System &copy; 2024. All Rights Reserved.</p>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('en-US', options);
        }
        setInterval(updateDateTime, 1000);

        document.addEventListener('DOMContentLoaded', () => {
            const notificationCount = 3;
            const notificationBadge = document.getElementById('notificationCount');
            if (notificationCount > 0) {
                notificationBadge.style.display = 'inline';
                notificationBadge.innerText = notificationCount;
            }
        });
    </script>
</body>
</html>