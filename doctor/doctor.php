<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Module Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            width: 18rem;
            margin: 1rem;
            text-align: center;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            margin-top: auto;
        }
        /* Custom navbar height reduction */
        .navbar {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .navbar .navbar-text {
            font-size: 1.25rem; /* Slightly adjust font size if needed */
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Centered Title -->
            <span class="navbar-text mx-auto" style="font-weight: bold; color: white;">
                Doctor Module
            </span>

            <!-- Right-aligned navigation links -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Doctor Registration -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Doctor Registration</h5>
                <p class="card-text">Register new doctors and manage their information.</p>
                <a href="register_doctor.php" class="btn btn-primary">Register Doctor</a>
            </div>
        </div>

        <!-- Appointment Scheduling -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Appointment Scheduling</h5>
                <p class="card-text">Schedule appointments for doctors and patients based on availability.</p>
                <a href="scheduleAppointment.php" class="btn btn-primary">Schedule Appointment</a>
            </div>
        </div>

        <!-- Patient Diagnosis -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Patient Diagnosis</h5>
                <p class="card-text">Access medical history, record diagnoses, and prescribe treatments.</p>
                <a href="patient_diagnosis.php" class="btn btn-primary">Record Diagnosis</a>
            </div>
        </div>

        <!-- Electronic Medical Records (EMR) -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Electronic Medical Records</h5>
                <p class="card-text">View and update patient medical history and doctor's notes.</p>
                <a href="emr.php" class="btn btn-primary">View EMR</a>
            </div>
        </div>

    <!-- Footer -->
    <!-- <div class="footer text-center bg-dark">
        <p style="color: white;"><strong>Hospital Management System | &copy; Copyright 2024. All Rights Reserved.</strong></p>
    </div> -->

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
