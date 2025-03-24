<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px auto;
            max-width: 1200px;
            padding: 20px;
        }
        .module-card {
            background: #ffffff;
            border: 1px solid #e3e3e3;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .module-icon {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 10px;
        }
        .module-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .module-description {
            color: #555;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .module-btn {
            text-decoration: none;
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .module-btn:hover {
            background: #0056b3;
        }
        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Hospital Admin Dashboard</a>
        </div>
    </nav>

    <div class="container dashboard-container">
        <div class="module-card">
            <div class="module-icon">👨‍⚕️</div>
            <div class="module-title">Doctor Registration</div>
            <div class="module-description">Register and manage doctors.</div>
            <a href="register_doctor.php" class="module-btn">Go to Module</a>
        </div>

        <div class="module-card">
            <div class="module-icon">👷‍♂️</div>
            <div class="module-title">Worker Registration</div>
            <div class="module-description">Add and manage hospital workers.</div>
            <a href="worker_registration.php" class="module-btn">Go to Module</a>
        </div>

        <div class="module-card">
            <div class="module-icon">💊</div>
            <div class="module-title">Pharmacy</div>
            <div class="module-description">Manage medicines and pharmacy records.</div>
            <a href="../pharmacy/pharmacy.php" class="module-btn">Go to Module</a>
        </div>
        <div class="module-card">
            <div class="module-icon">🧪</div>
            <div class="module-title">Laboratory</div>
            <div class="module-description">Handle laboratory tests and records.</div>
            <a href="../laboratory/laboratory.php" class="module-btn">Go to Module</a>
        </div>

        <div class="module-card">
            <div class="module-icon">🧪</div>
            <div class="module-title">Reception</div>
            <div class="module-description">Handle laboratory tests and records.</div>
            <a href="../laboratory/laboratory.php" class="module-btn">Go to Module</a>
        </div>

        <div class="module-card">
            <div class="module-icon">🧪</div>
            <div class="module-title">Nurse</div>
            <div class="module-description">Handle laboratory tests and records.</div>
            <a href="../nurse/nurse_dashboard.php" class="module-btn">Go to Module</a>
        </div>

       
        <div class="module-card">
            <div class="module-icon">🧪</div>
            <div class="module-title">Laboratory</div>
            <div class="module-description">Handle laboratory tests and records.</div>
            <a href="../laboratory/laboratory.php" class="module-btn">Go to Module</a>
        </div>

    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
