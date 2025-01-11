<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Module Dashboard</title>
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
                Laboratory Module
            </span>

            <!-- Right-aligned navigation links -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="../home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Lab Test Catalog -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Lab Test Catalog</h5>
                <p class="card-text">View available lab tests, costs, preparation instructions, and turnaround times.</p>
                <a href="lab_test_catalog.php" class="btn btn-primary">View Catalog</a>
            </div>
        </div>

        <!-- Lab Test Requests -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Lab Test Requests</h5>
                <p class="card-text">Allows doctors to order lab tests for patients and track request status.</p>
                <a href="lab_test_requests.php" class="btn btn-primary">Manage Requests</a>
            </div>
        </div>

        <!-- Sample Collection and Management -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sample Collection & Management</h5>
                <p class="card-text">Manage collection, labeling, and tracking of patient samples.</p>
                <a href="sample_management.php" class="btn btn-primary">Manage Samples</a>
            </div>
        </div>

        <!-- Result Entry and Reporting -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Result Entry & Reporting</h5>
                <p class="card-text">Enable lab technicians to enter results and generate reports.</p>
                <a href="result_entry.php" class="btn btn-primary">Enter Results</a>
            </div>
        </div>

        <!-- Doctor Notification -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Doctor Notification</h5>
                <p class="card-text">Send notifications to doctors when lab results are available.</p>
                <a href="doctor_notification.php" class="btn btn-primary">Notify Doctors</a>
            </div>
        </div>

        <!-- Lab Equipment Maintenance -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Lab Equipment Maintenance</h5>
                <p class="card-text">Manage records related to equipment maintenance, calibration, and service schedules.</p>
                <a href="equipment_maintenance.php" class="btn btn-primary">Maintain Equipment</a>
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
