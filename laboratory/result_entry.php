<?php
// Database connection details
$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

// Create database connection
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$message = "";

// Handle form submission for result entry
if (isset($_POST['submit_result'])) {
    $sampleID = mysqli_real_escape_string($conn, $_POST['sampleID']);
    $testResult = mysqli_real_escape_string($conn, $_POST['testResult']);
    $technicianName = isset($_POST['technician_name']) ? mysqli_real_escape_string($conn, $_POST['technician_name']) : '';
    $firstName = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, $_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, $_POST['last_name']) : '';

    // Get patientID from laboratory_samples
    $patientQuery = "SELECT patientID FROM laboratory_samples WHERE sampleID = '$sampleID'";
    $patientResult = mysqli_query($conn, $patientQuery);

    if ($patientResult && mysqli_num_rows($patientResult) > 0) {
        $patientRow = mysqli_fetch_assoc($patientResult);
        $patientID = $patientRow['patientID'];

        // Get doctorID from lab_requests for this patient
        $doctorQuery = "SELECT doctorID FROM lab_requests WHERE patientID = '$patientID' LIMIT 1";
        $doctorResult = mysqli_query($conn, $doctorQuery);

        if ($doctorResult && mysqli_num_rows($doctorResult) > 0) {
            $doctorRow = mysqli_fetch_assoc($doctorResult);
            $doctorID = $doctorRow['doctorID'];

            // Update laboratory sample (only update status)
            $update_query = "UPDATE laboratory_samples SET status = 'Completed' WHERE sampleID = '$sampleID'";

            // Insert into laboratory_results including doctorID
            $insert_query = "INSERT INTO laboratory_results (sampleID, patientID, first_name, last_name, test_details, test_result, technician_name, test_date, status, doctorID)
                            SELECT ls.sampleID, ls.patientID, '$firstName', '$lastName', ls.test_details, '$testResult', '$technicianName', NOW(), 'Completed', '$doctorID'
                            FROM laboratory_samples ls
                            WHERE ls.sampleID = '$sampleID'";

            if (mysqli_query($conn, $update_query) && mysqli_query($conn, $insert_query)) {
                $message = "Result entered successfully!";
            } else {
                $message = "Error inserting results: " . mysqli_error($conn);
            }
        } else {
            $message = "Error: No doctor found for this patient in lab_requests.";
        }
    } else {
        $message = "Error: Patient not found for the given sampleID.";
    }
}

// Fetch all laboratory samples
$query = "SELECT ls.*, p.first_name, p.last_name
          FROM laboratory_samples ls
          LEFT JOIN patients p ON ls.patientID = p.patientID
          WHERE ls.status != 'Completed'
          ORDER BY ls.dateReceived DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Samples Management</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f4f8;
            color: #2c3e50;
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        h1 {
            margin-top: 1rem;
            font-size: 2.5rem;
            color: #3498db;
        }

        /* Pop-Up Form Styles */
        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            z-index: 1000;
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-popup.active {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #3498db;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #dfe6e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52,152,219,0.2);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input[type="submit"] {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 0.3s ease;
        }

        .form-group input[type="submit"]:hover {
            background: #2980b9;
        }

        /* Table Styles */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .results-table th,
        .results-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .results-table th {
            background: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .results-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .results-table tr:hover {
            background: #f1f8ff;
        }

        .action-button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .action-button:hover {
            background: #219a52;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .results-table td, 
            .results-table th {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
            
            .form-popup {
                padding: 1.5rem;
            }
        }

        /* Test Details Styling */
        .test-details {
            white-space: pre-line; /* Preserve line breaks */
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #dfe6e9;
        }

        /* Roman Numerals Styling */
        .roman-list {
            list-style-type: upper-roman;
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1>Laboratory Samples Management</h1>

    <!-- Laboratory Samples Table -->
    <table class="results-table">
        <thead>
            <tr>
                <th>Sample ID</th>
                <th>Patient Name</th>
                
                <th>Date Received</th>
                <th>Status</th>
                <th>Collected By</th>
                <th>Date Collected</th>
                <th>Labeled By</th>
                <th>Tracking #</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
if (mysqli_num_rows($result) > 0) 
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if test_details is not empty
        $testDetails = isset($row['test_details']) ? $row['test_details'] : ''; 
        if (!empty($row['test_details'])) {
            $sampleTypes = explode(',', $row['test_details']); // Ensure correct delimiter
          
            echo"<tr>
                            <td>{$row['sampleID']}</td>
                            <td>{$row['first_name']} {$row['last_name']}</td>
                           
                            <td>{$row['dateReceived']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['collectedBy']}</td>
                            <td>{$row['dateCollected']}</td>
                            <td>{$row['labeledBy']}</td>
                            <td>{$row['trackingNumber']}</td>
                            <td>
                                <button class='action-button' 
                                        onclick=\"openForm(
                                            '{$row['sampleID']}', 
                                            '{$row['first_name']}', 
                                            '{$row['last_name']}'
                                        )\">
                                    Add Result
                                </button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='10' style='text-align:center;'>No pending samples found</td></tr>";
            }
            ?>
        </tbody>
        <a href="laboratory.php">⬅️</a>
    </table>

    <!-- Result Entry Pop-Up Form -->
    <div class="overlay" id="overlay"></div>
    <div class="form-popup" id="resultForm">
        <h2>Enter Test Results</h2>
        <?php if (!empty($message)) : ?>
            <div class="message <?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        <form method="POST" onsubmit="closeForm()">
            <div class="form-group">
                <label>Sample ID:</label>
                <input type="text" id="sampleID" name="sampleID" readonly>
            </div>
            
            <div class="form-group">
                <label>Patient Name:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" readonly>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Test Result:</label>
                <textarea name="testResult" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Technician Name:</label>
                <input type="text" name="technician_name" required>
            </div>

            <div class="form-group">
                <input type="submit" name="submit_result" value="Submit Result">
            </div>
        </form>
    </div>

    <script>
        // Form control functions
        function openForm(sampleID, firstName, lastName) {
            document.getElementById('sampleID').value = sampleID;
            document.getElementById('first_name').value = firstName;
            document.getElementById('last_name').value = lastName;
            document.getElementById('resultForm').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }

        function closeForm() {
            document.getElementById('resultForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }

        // Close form when clicking outside
        document.getElementById('overlay').addEventListener('click', closeForm);
    </script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>