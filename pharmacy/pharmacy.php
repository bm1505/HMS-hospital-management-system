<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Module Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #e9f5fb; /* Light blue background for a calming effect */
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #005f73; /* Dark teal for a professional look */
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar .navbar-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
        }

        .navbar .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            margin-left: 1rem;
        }

        .navbar .nav-link:hover {
            color: #a8dadc !important; /* Light teal for hover effect */
        }

        /* Card Styles */
        .card {
            width: 18rem;
            margin: 1rem;
            text-align: center;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #005f73; /* Dark teal for headings */
            margin-bottom: 1rem;
        }

        .card-text {
            font-size: 0.95rem;
            color: #4a4a4a; /* Dark gray for text */
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: #0a9396; /* Medium teal for buttons */
            border: none;
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #005f73; /* Dark teal for hover */
        }

        /* Footer Styles */
        .footer {
            background-color: #005f73; /* Dark teal to match the navbar */
            color: #ffffff;
            padding: 1rem;
            text-align: center;
            margin-top: auto;
            font-size: 0.9rem;
        }

        .footer p {
            margin: 0;
        }

        /* Container Styles */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 2rem;
            flex-grow: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card {
                width: 100%;
                margin: 1rem 0;
            }

            .navbar .navbar-text {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <!-- Centered Title -->
            <span class="navbar-text mx-auto">
                Pharmacy Module
            </span>

            <!-- Right-aligned navigation links -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="pharmacy.php">Home</a>
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

        <!-- Payment Management -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Management</h5>
                <p class="card-text">Handle payments, track transactions, and manage billing efficiently.</p>
                <a href="payment.php" class="btn btn-primary">Manage Payments</a>
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

        <!-- Medicine Order Requests -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Medicine Order Requests</h5>
                <p class="card-text">Reorder stock when quantities are low.</p>
                <a href="medicine_order_requests.php" class="btn btn-primary">Order Medicines</a>
            </div>
        </div>

        <!-- Profit and Stock Price Module -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Profit & Stock Price</h5>
                <p class="card-text">View profit reports and stock prices.</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#profitStockModal">
                    View Reports
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Hospital Management System | All Rights Reserved</p>
    </footer>

    <!-- Profit and Stock Price Modal -->
    <div class="modal fade" id="profitStockModal" tabindex="-1" role="dialog" aria-labelledby="profitStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profitStockModalLabel">Select Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <a href="Profit.php" class="btn btn-primary btn-lg btn-block mb-3">Profit Report</a>
                        <a href="StockPrice.php" class="btn btn-primary btn-lg btn-block">Stock Price Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>