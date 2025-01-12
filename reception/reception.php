<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration Module Dashboard</title>
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
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Centered Title -->
            <span class="navbar-text mx-auto" style="font-weight: bold; color: white;">
                Patient Registration Module
            </span>

            <!-- Right-aligned navigation links -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Patient Registration -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Patient Registration</h5>
                <p class="card-text">Register new patients, capture details, and generate unique patient IDs.</p>
                <a href="patient_registration.php" class="btn btn-primary">Register Patient</a>
            </div>
        </div>

        <!-- Appointment Booking -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Appointment Booking</h5>
                <p class="card-text">Book appointments for patients with doctors either online or at the registration desk.</p>
                <a href="booking.php" class="btn btn-primary">Book Appointment</a>
            </div>
        </div>

        <!-- Patient Check-In and Check-Out -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Patient Check-In & Check-Out</h5>
                <p class="card-text">Manage patient check-in for appointments and check-out after treatment.</p>
                <a href="patient_checkin_checkout.php" class="btn btn-primary">Manage Check-In/Out</a>
            </div>
        </div>

        <!-- Emergency Contact Details -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Emergency Contact Details</h5>
                <p class="card-text">Store emergency contact information for each patient.</p>
                <a href="emergency.php" class="btn btn-primary">Manage Contacts</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Hospital Management System | All Rights Reserved</p>
    </footer>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>