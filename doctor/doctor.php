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
        body, html {
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color:rgb(3, 66, 14);
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 20rem;
            margin: 1rem;
            text-align: center;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgb(241, 237, 2);
        }
        .card:hover {
            transform: translateY(-5px);
            transition: 0.3s;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color:rgb(5, 121, 34);
        }
        .footer {
            background-color:rgb(2, 60, 117);
            color: #ffffff;
            padding: 10px;
            text-align: center;
            margin-top: auto;
        }
    
        .navbar {
            background-color: white;
            padding: 1.5rem;
        }
        .navbar-text {
            font-size: 1rem;
            color: black;
        }
        .logout-btn {
            background-color:rgb(245, 6, 30);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
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
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: white;
            color: black;
            font-size: 0.8rem;
            border-radius: 50%;
            padding: 3px 7px;
        }
        .notification-popup {
            position: absolute;
            top: 40px;
            right: 0;
            background-color: whitesmoke;
            border: 1px solid green;
            box-shadow: 0 4px 6px rgb(240, 3, 3);
            padding: 10px;
            width: 200px;
            display: none;
            z-index: 10;
        }
        .notification-item {
            padding: 5px 0;
            border-bottom: 1px solidrgb(245, 8, 8);
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notifications:hover .notification-popup {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-text">
                Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            </span>
            <span class="navbar-text ml-auto" id="currentDateTime"></span>
            <div class="notifications ml-3">
                <a href="#" class="nav-link text-white">
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
