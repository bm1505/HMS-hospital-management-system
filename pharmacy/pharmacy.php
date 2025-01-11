<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Module Dashboard</title>
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
                Pharmacy Module
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
        <!-- Inventory Management -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Inventory Management</h5>
                <p class="card-text">Track medications and supplies, including quantity, expiration, and suppliers.</p>
                <a href="inventory_management.php" class="btn btn-primary">Manage Inventory</a>
            </div>
        </div>

        <!-- Prescription Fulfillment -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Prescription Fulfillment</h5>
                <p class="card-text">Receive and process prescriptions from the Doctor module.</p>
                <a href="prescription_fulfillment.php" class="btn btn-primary">Fulfill Prescriptions</a>
            </div>
        </div>

        <!-- Billing and Payment -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Billing and Payments</h5>
                <p class="card-text">Calculate medication costs, generate invoices, and process payments.</p>
                <a href="billing_payment.php" class="btn btn-primary">Manage Billing</a>
            </div>
        </div>

        <!-- Medicine Order Requests -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Medicine Order Requests</h5>
                <p class="card-text">Reorder stock when quantities are low.</p>
                <a href="medicine_order_requests.php" class="btn btn-primary">Order Medicines</a>
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
