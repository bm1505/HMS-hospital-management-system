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

// Expect only patientID and medicines from the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID'], $_POST['medicines'])) {
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
    
    // Fetch patient details from the patients table
    $sqlPatient = "SELECT first_name, last_name, dateofBirth, gender, phone FROM patients WHERE patientID = ?";
    $stmtPatient = mysqli_prepare($conn, $sqlPatient);
    if (!$stmtPatient) {
        echo json_encode(['success' => false, 'message' => 'Patient query preparation failed: ' . mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param($stmtPatient, "i", $patientID);
    mysqli_stmt_execute($stmtPatient);
    $resultPatient = mysqli_stmt_get_result($stmtPatient);
    if (!$resultPatient || mysqli_num_rows($resultPatient) == 0) {
        echo json_encode(['success' => false, 'message' => 'Patient not found.']);
        exit;
    }
    $patientData = mysqli_fetch_assoc($resultPatient);
    mysqli_stmt_close($stmtPatient);
    
    // Fetch the latest lab result (if available) from the laboratory_results table
    $sqlLab = "SELECT test_result FROM laboratory_results WHERE patientID = ? ORDER BY test_date DESC LIMIT 1";
    $stmtLab = mysqli_prepare($conn, $sqlLab);
    $lab_result = "N/A";
    if ($stmtLab) {
        mysqli_stmt_bind_param($stmtLab, "i", $patientID);
        mysqli_stmt_execute($stmtLab);
        $resultLab = mysqli_stmt_get_result($stmtLab);
        if ($resultLab && mysqli_num_rows($resultLab) > 0) {
            $labRow = mysqli_fetch_assoc($resultLab);
            $lab_result = $labRow['test_result'];
        }
        mysqli_stmt_close($stmtLab);
    }
    
    // Use the first medicine from the array as the header details for the medical_history record.
    $headerMedicine = $medicines[0];
    if (empty($headerMedicine['medicationName']) || empty($headerMedicine['dosage'])) {
        echo json_encode(['success' => false, 'message' => 'Header medicine details missing.']);
        exit;
    }
    $mh_medicationName = mysqli_real_escape_string($conn, $headerMedicine['medicationName']);
    $mh_dosages = mysqli_real_escape_string($conn, $headerMedicine['dosage']);
    
    // Insert prescription header into the prescriptions table
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
    
    // Insert into the medical_history table with all patient details
    $sqlHistory = "INSERT INTO medical_history (doctorID, patientID, prescriptionID, first_name, last_name, dateofBirth, gender, phone, lab_result, medicationName, dosages, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmtHistory = mysqli_prepare($conn, $sqlHistory);
    if (!$stmtHistory) {
        echo json_encode(['success' => false, 'message' => 'Medical history preparation failed: ' . mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param(
        $stmtHistory,
        "iiissssssss",
        $doctorID,
        $patientID,
        $prescriptionID,
        $patientData['first_name'],
        $patientData['last_name'],
        $patientData['dateofBirth'],
        $patientData['gender'],
        $patientData['phone'],
        $lab_result,
        $mh_medicationName,
        $mh_dosages
    );
    if (!mysqli_stmt_execute($stmtHistory)) {
        echo json_encode(['success' => false, 'message' => 'Error inserting medical history: ' . mysqli_stmt_error($stmtHistory)]);
        exit;
    }
    mysqli_stmt_close($stmtHistory);
    
    $allInserted = true;
    // Loop through all medicines and insert each into the prescription_medicines table
    foreach ($medicines as $medicine) {
        if (empty($medicine['medicationName']) || empty($medicine['quantity']) || empty($medicine['dosage']) || empty($medicine['instructions'])) {
            echo json_encode(['success' => false, 'message' => 'All medicine fields must be provided.']);
            exit;
        }
        $medicationNameMedicine = mysqli_real_escape_string($conn, $medicine['medicationName']);
        $quantities             = mysqli_real_escape_string($conn, $medicine['quantity']);
        $dosagesMedicine        = mysqli_real_escape_string($conn, $medicine['dosage']);
        $instructions           = mysqli_real_escape_string($conn, $medicine['instructions']);
        
        $sqlMedicine = "INSERT INTO prescription_medicines (prescriptionID, medicationName, quantities, dosages, instructions)
                        VALUES (?, ?, ?, ?, ?)";
        $stmtMedicine = mysqli_prepare($conn, $sqlMedicine);
        if (!$stmtMedicine) {
            echo json_encode(['success' => false, 'message' => 'Medicine preparation failed: ' . mysqli_error($conn)]);
            exit;
        }
        mysqli_stmt_bind_param($stmtMedicine, "isiss", $prescriptionID, $medicationNameMedicine, $quantities, $dosagesMedicine, $instructions);
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
