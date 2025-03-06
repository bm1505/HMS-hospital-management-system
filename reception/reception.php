<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration Module Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body,
    html {
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color:rgb(248, 250, 250);
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
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 20px;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .footer {
        background-color: #343a40;
        color: #ffffff;
        padding: 10px;
        text-align: center;
        margin-top: auto;
    }

    /* Adjusted navbar style */
    .navbar {
        background-color: #3498db; /* Blue background */
        padding: 0.25rem 1rem; /* Reduced padding to reduce navbar size */
    }

    .navbar .navbar-text {
        font-size: 1rem; /* Reduced font size */
    }

    /* Style for buttons in navbar */
    .btn-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding: 10px;
    }

    .btn-container a {
        padding: 8px 16px; /* Reduced padding for smaller buttons */
        border-radius: 8px;
        font-size: 13px; /* Slightly smaller font size */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
        text-decoration: none;
    }

    .btn-container .btn-primary {
        background-color: #3498db;
    }

    .btn-container .btn-danger {
        background-color: #e74c3c;
    }
</style>

</head>

<body>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Centered Title -->
            <span class="navbar-text mx-auto" style="font-weight: bold; color: white;">
    <i class="fas fa-hospital"></i> RECEPTION
</span>

            <!-- Right-aligned navigation links -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                <div class="btn-container">
        <a href="../index.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Back and Logout Buttons -->
    

    <!-- Main Content -->
    <div class="container">
        <!-- Patient Registration -->
        <div class="card">
            <div class="card-body">
                <i class="fas fa-user-plus fa-2x"></i>
                <h5 class="card-title">Patient Registration</h5>
                <p class="card-text">Register new patients, capture details, and generate unique patient IDs.</p>
                <a href="patient_registration.php" class="btn btn-primary">Register Patient</a>
            </div>
        </div>

        <!-- Appointment Booking -->
        <div class="card">
            <div class="card-body">
                <i class="fas fa-calendar-check fa-2x"></i>
                <h5 class="card-title">Appointment Booking</h5>
                <p class="card-text">Book appointments for patients with doctors either online or at the registration desk.</p>
                <a href="booking.php" class="btn btn-primary">Book Appointment</a>
            </div>
        </div>

        <!-- Patient Check-In and Check-Out -->
        <div class="card">
            <div class="card-body">
                <i class="fas fa-hospital-user fa-2x"></i>
                <h5 class="card-title">Patient Check-In & Check-Out</h5>
                <p class="card-text">Manage patient check-in for appointments and check-out after treatment.</p>
                <a href="patient_checkin_checkout.php" class="btn btn-primary">Manage Check-In/Out</a>
            </div>
        </div>

        <!-- Emergency Contact Details -->
        <div class="card">
            <div class="card-body">
                <i class="fas fa-phone-alt fa-2x"></i>
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
