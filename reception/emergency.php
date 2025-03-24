<?php
// scheduleAppointment.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'st_norbert_hospital');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Sanitize input parameters
$searchFirst = isset($_GET['first_name']) ? $conn->real_escape_string($_GET['first_name']) : '';
$searchLast = isset($_GET['last_name']) ? $conn->real_escape_string($_GET['last_name']) : '';

// Build query with conditional search
$query = "SELECT patientID, dateOfBirth, gender, contactInfo, 
                 first_name, last_name, phone, email, address, 
                 insurance_number, emergency_contact, relationship 
          FROM patients";

$conditions = [];
if (!empty($searchFirst)) $conditions[] = "first_name LIKE '%$searchFirst%'";
if (!empty($searchLast)) $conditions[] = "last_name LIKE '%$searchLast%'";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Execute query
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Emergency Contact Information</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 2rem;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .search-form {
            margin-bottom: 2rem;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
        }

        .form-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        input[type="text"] {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .no-results {
            padding: 1rem;
            background: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Patient Emergency Contact Information</h1>
        
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="first_name" placeholder="First Name" 
                       value="<?= htmlspecialchars($searchFirst) ?>">
                <input type="text" name="last_name" placeholder="Last Name" 
                       value="<?= htmlspecialchars($searchLast) ?>">
                <button type="submit">Search</button>
            </div>
        </form>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Insurance</th>
                            <th>Emergency Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patientID']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?> <?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['dateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($row['gender']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['phone']) ?><br>
                                <?= htmlspecialchars($row['email']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['insurance_number']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['emergency_contact']) ?><br>
                                (<?= htmlspecialchars($row['relationship']) ?>)
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">No patient records found</div>
        <?php endif; ?>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>