<?php
// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search and display patients' medical history
if (isset($_GET['patient_name']) && !empty($_GET['patient_name'])) {
    $patient_name = $_GET['patient_name'];

    // Search for patients by first name or last name
    $query = $conn->prepare("SELECT patientID, first_name, last_name FROM patients WHERE first_name LIKE ? OR last_name LIKE ?");
    $search_param = "%" . $patient_name . "%";
    $query->bind_param("ss", $search_param, $search_param);
    $query->execute();
    $result = $query->get_result();

    // Check if the patient exists
    if ($result->num_rows > 0) {
        while ($patient = $result->fetch_assoc()) {
            $patient_id = $patient['patientID'];

            // Fetch medical history for the patient
            $history_result = $conn->query("SELECT * FROM patient_diagnosis WHERE patient_id = $patient_id");
            $medical_history = [];
            while ($row = $history_result->fetch_assoc()) {
                $medical_history[] = $row;
            }

            // Display patient's info and medical history in a table
            echo "<h5>Medical History for Patient: {$patient['first_name']} {$patient['last_name']}</h5>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>Diagnosis</th><th>Medications</th><th>Lab Tests</th><th>Date</th></tr></thead>";
            echo "<tbody>";
            foreach ($medical_history as $history) {
                echo "<tr>";
                echo "<td>{$history['diagnosis']}</td>";
                echo "<td>{$history['medications']}</td>";
                echo "<td>{$history['lab_tests']}</td>";
                echo "<td>{$history['created_at']}</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    } else {
        echo "<div class='alert alert-warning'>No patients found with the name '{$patient_name}'.</div>";
    }
} else {
    // If no search term, display a prompt
    echo "<p>Please enter a patient's name to search.</p>";
}

// Handle dynamic patient search (AJAX request)
if (isset($_GET['patient_name']) && !empty($_GET['patient_name'])) {
    $patient_name = $_GET['patient_name'];
    $stmt = $conn->prepare("SELECT patientID, first_name, last_name FROM patients WHERE first_name LIKE ? OR last_name LIKE ?");
    $search_param = "%" . $patient_name . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and return results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='patient-item' onclick='selectPatient(" . $row['patientID'] . ", \"" . $row['first_name'] . " " . $row['last_name'] . "\")'>" . $row['first_name'] . " " . $row['last_name'] . "</div>";
        }
    } else {
        echo "<p>No patients found.</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<style>
body {
    background-color: #f4f7f6;
    font-family: Arial, sans-serif;
}
.container {
    max-width: 1200px;
    margin: 0 auto;
}
h5 {
    font-size: 24px;
    font-weight: bold;
}
.table {
    margin-top: 30px;
    border-collapse: collapse;
}
.table th, .table td {
    padding: 12px;
    text-align: left;
    font-size: 16px;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}
.table-bordered th, .table-bordered td {
    border: 1px solid #ddd;
}
.alert-warning {
    background-color: #f0ad4e;
    color: #fff;
    padding: 10px;
    border-radius: 4px;
}
.patient-item {
    cursor: pointer;
    padding: 8px;
    background-color: #f1f1f1;
    margin: 5px 0;
    border-radius: 4px;
}
.patient-item:hover {
    background-color: #ddd;
}
</style>

<script>
function selectPatient(id, name) {
    alert("Patient selected: " + name);
    // Implement functionality to use the selected patient data
}
</script>
