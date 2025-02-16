<?php
session_start();
header('Content-Type: application/json');

$servername   = "localhost";
$db_username  = "root";
$db_password  = "";
$dbname       = "st_norbert_hospital";

// Create database connection
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID']) && isset($_POST['medicines'])) {
    if (!isset($_SESSION['doctorID'])) {
        echo json_encode(['success' => false, 'message' => 'Doctor is not logged in.']);
        exit;
    }
    $doctorID = $_SESSION['doctorID'];
    $patientID = mysqli_real_escape_string($conn, $_POST['patientID']);
    $medicines = json_decode($_POST['medicines'], true);
    
    if (empty($patientID) || !is_array($medicines) || count($medicines) === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }
    
    // Insert prescription header into prescriptions table
    $sql = "INSERT INTO prescriptions (doctorID, patientID, created_at) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ii", $doctorID, $patientID);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Error inserting prescription: ' . mysqli_stmt_error($stmt)]);
        exit;
    }
    $prescriptionID = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    $allInserted = true;
    foreach ($medicines as $medicine) {
        // Validate that all fields exist
        if (empty($medicine['medicationName']) || empty($medicine['quantity']) || empty($medicine['dosage']) || empty($medicine['instructions'])) {
            echo json_encode(['success' => false, 'message' => 'All medicine fields must be provided.']);
            exit;
        }
        $medicationName = mysqli_real_escape_string($conn, $medicine['medicationName']);
        // Use the database column names "quantities" and "dosages"
        $quantities = mysqli_real_escape_string($conn, $medicine['quantity']);
        $dosages = mysqli_real_escape_string($conn, $medicine['dosage']);
        $instructions = mysqli_real_escape_string($conn, $medicine['instructions']);
        
        $sqlMedicine = "INSERT INTO prescription_medicines (prescriptionID, medicationName, quantities, dosages, instructions)
                        VALUES (?, ?, ?, ?, ?)";
        $stmtMedicine = mysqli_prepare($conn, $sqlMedicine);
        if (!$stmtMedicine) {
            echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . mysqli_error($conn)]);
            exit;
        }
        // Bind types: prescriptionID (i), medicationName (s), quantities (i), dosages (s), instructions (s)
        mysqli_stmt_bind_param($stmtMedicine, "isiss", $prescriptionID, $medicationName, $quantities, $dosages, $instructions);
        if (!mysqli_stmt_execute($stmtMedicine)) {
            $allInserted = false;
            echo json_encode(['success' => false, 'message' => 'Error inserting medicine: ' . mysqli_stmt_error($stmtMedicine)]);
            exit;
        }
        mysqli_stmt_close($stmtMedicine);
    }
    
    if ($allInserted) {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

mysqli_close($conn);
?>
