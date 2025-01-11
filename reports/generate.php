<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Module Dashboard</title>
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
                Reports Module
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
        <!-- Financial Summary Report -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Financial Summary Report</h5>
                <p class="card-text">View the overall financial status of the hospital including revenue, expenses, and profits.</p>
                <a href="financial_summary_report.php" class="btn btn-primary">Generate Report</a>
            </div>
        </div>

        <!-- Patient Admission Report -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Patient Admission Report</h5>
                <p class="card-text">Generate reports on patient admissions, discharges, and their treatment details.</p>
                <a href="patient_admission_report.php" class="btn btn-primary">Generate Report</a>
            </div>
        </div>

        <!-- Billing Report -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Billing Report</h5>
                <p class="card-text">Generate detailed billing reports for all patient treatments and services.</p>
                <a href="billing_report.php" class="btn btn-primary">Generate Report</a>
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
