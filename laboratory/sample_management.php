<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to generate unique Sample ID
function generateSampleID($conn) {
    $year = date('Y');
    $sql = "SELECT MAX(sampleID) AS last_id FROM laboratory_samples";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['last_id']) {
        $last_number = (int)substr($row['last_id'], -5);
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }
    
    return "SMPL" . $year . str_pad($new_number, 5, '0', STR_PAD_LEFT);
}

// Function to generate tracking number
function generateTrackingNumber() {
    return 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 10));
}

// Retrieve patient details
$patientID = $_GET['patientID'] ?? $_SESSION['patientID'] ?? '';
$patientName = '';
$testRequested = '';
$sampleType = '';

if (!empty($patientID)) {
    // Fetch patient name
    $sql = "SELECT first_name, last_name FROM patients WHERE patientID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $patientID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $patientName = $row['first_name'] . " " . $row['last_name'];
    } else {
        $patientID = '';
        echo "<script>alert('Error: Invalid Patient ID.');</script>";
    }

    // Fetch test details (sample type) from lab_requests table
    $sql = "SELECT test_details FROM lab_requests WHERE patientID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $patientID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $sampleType = $row['test_details'];
    } else {
        echo "<script>alert('Error: No test request found for this patient.');</script>";
    }
}

// Handle sample submission
if (isset($_POST['submit'])) {
    $patientID = $_POST['patientID'];
    $sampleID = generateSampleID($conn);
    $trackingNumber = generateTrackingNumber();
    
    // Get form data
    $sampleType = mysqli_real_escape_string($conn, $_POST['sampleType']);
    $dateReceived = mysqli_real_escape_string($conn, $_POST['dateReceived']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $collectedBy = mysqli_real_escape_string($conn, $_POST['collectedBy']);
    $dateCollected = mysqli_real_escape_string($conn, $_POST['dateCollected']);
    $labeledBy = mysqli_real_escape_string($conn, $_POST['labeledBy']);

    // Validate patient exists
    $sql = "SELECT patientID FROM patients WHERE patientID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $patientID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $insertSql = "INSERT INTO laboratory_samples (
            sampleID, patientID, test_details, dateReceived, status,
            collectedBy, dateCollected, labeledBy, trackingNumber
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($stmt, "sssssssss", 
            $sampleID,
            $patientID,
            $sampleType,
            $dateReceived,
            $status,
            $collectedBy,
            $dateCollected,
            $labeledBy,
            $trackingNumber
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                alert('Sample added successfully!\\nSample ID: $sampleID\\nTracking Number: $trackingNumber');
                window.location='sample_management.php';
            </script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Error: Invalid Patient ID.');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
            width: 100%;
            font-size: 28px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="date"], select, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus, input[type="date"]:focus, 
        select:focus, textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .info {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 2px solid #3498db;
            width: 100%;
        }
        .info p {
            margin: 0;
            font-size: 16px;
            color: #2c3e50;
            line-height: 1.6;
        }
        .tracking-number {
            background-color: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 2px dashed #3498db;
            text-align: center;
            font-size: 20px;
            color: #2c3e50;
            width: 100%;
        }
        .form-column {
            flex: 1;
            padding: 15px;
            min-width: 300px;
        }
        .form-column:first-child {
            border-right: 2px solid #f0f0f0;
        }
        .sample-type-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            line-height: 1.8;
            font-size: 15px;
            white-space: pre-wrap;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .form-column {
                flex: 100%;
                padding: 10px;
            }
            .form-column:first-child {
                border-right: none;
                border-bottom: 2px solid #f0f0f0;
                padding-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Laboratory Sample Management</h2>
        
        <!-- Display Patient Info -->
        <div class="info">
            <p><strong>Patient ID:</strong> <?php echo htmlspecialchars($patientID); ?></p>
            <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($patientName); ?></p>
        </div>

        <!-- Display Tracking Number -->
        <div class="tracking-number">
            <strong>Generated Tracking Number:</strong> <?php echo generateTrackingNumber(); ?>
        </div>

        <!-- Sample Submission Form -->
        <form action="" method="post">
            <input type="hidden" name="patientID" value="<?php echo htmlspecialchars($patientID); ?>">
            <input type="hidden" name="sampleType" value="<?php echo htmlspecialchars($sampleType); ?>">
            
            <div class="form-column">
                <label for="sampleType">Test Details:</label>
                <div class="sample-type-list"><?php
                    if (!empty($sampleType)) {
                        $sampleTypes = explode(',', $sampleType);
                        foreach ($sampleTypes as $index => $type) {
                            echo ($index + 1) . ". " . trim($type) . "\n";
                        }
                    }
                ?></div>
                
                <label for="dateReceived">Date Received:</label>
                <input type="date" id="dateReceived" name="dateReceived" required>
                
                <label for="status">Analysis Status:</label>
                <select id="status" name="status" required>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            
            <div class="form-column">
                <label for="collectedBy">Collected By:</label>
                <input type="text" id="collectedBy" name="collectedBy" placeholder="Enter collector's name" required>
                
                <label for="dateCollected">Collection Date:</label>
                <input type="date" id="dateCollected" name="dateCollected" required>
                
                <label for="labeledBy">Labeled By:</label>
                <input type="text" id="labeledBy" name="labeledBy" placeholder="Enter labeler's name" required>
            </div>
            
            <input type="submit" name="submit" value="Submit Sample">
        </form>
    </div>
</body>
</html>