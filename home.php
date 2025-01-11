<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" href="assets/images/hospital-logo.png" type="image/icon">
    <style>
        body {
            background-image: url('assets/images/hospital-background.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            color: #333;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            color: black;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #c8e6c9;
            height: 100%;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .row.equal-height {
            display: flex;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        .module-icon {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <h2 class="navbar-text mx-auto" style="font-weight: 600; color: white;">
                Hospital Management System
            </h2>
        </div>
    </nav>

    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p class="text-muted">Choose a module to manage hospital operations.</p>
        </header>
        <hr>
        <div class="row equal-height mt-5">
            <!-- Doctor Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">👨‍⚕️</div>
                        <h5 class="card-title">Doctor</h5>
                        <p class="card-text">Add and manage doctor profiles, appointments, and schedules.</p>
                        <a href="doctor/doctor.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
            <!-- Pharmacy Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">💊</div>
                        <h5 class="card-title">Pharmacy</h5>
                        <p class="card-text">Manage inventory, prescriptions, and billing records.</p>
                        <a href="pharmacy/pharmacy.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
            <!-- Laboratory Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">🧪</div>
                        <h5 class="card-title">Laboratory</h5>
                        <p class="card-text">Handle lab tests, samples, and result reporting.</p>
                        <a href="laboratory/laboratory.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row equal-height mt-4">
            <!-- Patient Registration Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">🧑‍🤝‍🧑</div>
                        <h5 class="card-title">Patient Registration</h5>
                        <p class="card-text">Register new patients and manage check-ins and appointments.</p>
                        <a href="patient/patient.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
            <!-- Billing Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">💰</div>
                        <h5 class="card-title">Billing</h5>
                        <p class="card-text">Handle patient billing, insurance, and payment records.</p>
                        <a href="billing/billing.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
            <!-- Reports Module -->
            <div class="col-md-4 d-flex">
                <div class="card text-center w-100">
                    <div class="card-body">
                        <div class="module-icon">📊</div>
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">Generate and download various hospital reports.</p>
                        <a href="reports/generate.php" class="btn btn-primary">Access</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer text-center bg-dark">
        <hr>
        <p style="color: white;"><strong>Hospital Management System | &copy; Copyright 2024. All Rights Reserved.</strong></p>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
