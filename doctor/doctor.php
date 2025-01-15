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
            background-color: #f8f9fa;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            color: green;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            margin-top: auto;
        }
        .navbar {
            padding: 0.5rem;
        }
        .navbar-text {
            font-size: 1rem;
            color: black;
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
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px;
            width: 200px;
            display: none;
            z-index: 10;
        }
        .notification-item {
            padding: 5px 0;
            border-bottom: 1px solid #f1f1f1;
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
            <!-- Doctor Information -->
            <span class="navbar-text ml-auto">
                <span id="currentDateTime"></span>
            </span>
            <!-- Notifications -->
            <div class="notifications ml-3">
                <a href="#" class="nav-link text-white">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                </a>
                <!-- Notification Popup -->
                <div class="notification-popup">
                    <div class="notification-item"><a href="notificationPage.php">New Appointment</a></div>
                    <div class="notification-item"><a href="notificationPage.php">Patient Diagnosis</a></div>
                    <div class="notification-item"><a href="notificationPage.php">Prescription Update</a></div>
                </div>
            </div>
            <!-- Logout -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Modules -->
    <div class="container">
        <!-- Appointment Scheduling -->
        <div class="card">
            <div class="card-body">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <h5 class="card-title">Appointment Scheduling</h5>
                <p class="card-text">Schedule appointments for doctors and patients based on availability.</p>
                <a href="scheduleAppointment.php" class="btn btn-primary">Schedule Appointment</a>
            </div>
        </div>

        <!-- Patient Diagnosis -->
        <div class="card">
            <div class="card-body">
                <div class="icon"><i class="fas fa-user-md"></i></div>
                <h5 class="card-title">Patient Diagnosis</h5>
                <p class="card-text">Access medical history, record diagnoses, and prescribe treatments.</p>
                <a href="patient_diagnosis.php" class="btn btn-primary">Record Diagnosis</a>
            </div>
        </div>

        <!-- Electronic Medical Records (EMR) -->
        <div class="card">
            <div class="card-body">
                <div class="icon"><i class="fas fa-file-medical"></i></div>
                <h5 class="card-title">Electronic Medical Records</h5>
                <p class="card-text">View and update patient medical history and doctor's notes.</p>
                <a href="emr.php" class="btn btn-primary">View EMR</a>
            </div>
        </div>

        <!-- Prescription Management -->
        <div class="card">
            <div class="card-body">
                <div class="icon"><i class="fas fa-prescription"></i></div>
                <h5 class="card-title">Prescription Management</h5>
                <p class="card-text">Create and send prescriptions directly to the pharmacy module.</p>
                <a href="prescription.php" class="btn btn-primary">Manage Prescriptions</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Hospital Management System &copy; 2024. All Rights Reserved.</p>
    </div>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display Current Date & Time
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('en-US', options);
        }
        setInterval(updateDateTime, 1000);

        // Handle Notifications (Mock Example)
        document.addEventListener('DOMContentLoaded', () => {
            const notificationCount = 3; // Example count; replace dynamically
            const notificationBadge = document.getElementById('notificationCount');
            if (notificationCount > 0) {
                notificationBadge.style.display = 'inline';
                notificationBadge.innerText = notificationCount;
            }
        });
    </script>
</body>
</html>
