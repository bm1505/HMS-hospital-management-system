<?php
// Start session
session_start();

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all patients from the database
$sql = "SELECT * FROM patients ORDER BY registration_date DESC";
$result = $conn->query($sql);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registered Patients</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #e9f7ef;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .table {
            margin-top: 20px;
        }

        .table thead {
            background-color: #3498db;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: #3498db;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registered Patients</h1>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search by patient name..." class="form-control">
        </div>

        <!-- Patients Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Insurance Number</th>
                        <th>Emergency Contact</th>
                        <th>Relationship</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody id="patientTable">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['patientID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['dateOfBirth']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['insurance_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['emergency_contact']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['relationship']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['registration_date']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='13' class='text-center'>No patients found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchQuery = this.value.toLowerCase();
            const rows = document.querySelectorAll('#patientTable tr');

            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase() + " " +
                             row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                if (name.includes(searchQuery)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>