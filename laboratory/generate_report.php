<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetching all result entries
$sql = "SELECT * FROM laboratory_results";
$result = mysqli_query($conn, $sql);

// Code to generate the PDF report
if (isset($_POST['generate_report'])) {
    require('fpdf/fpdf.php');
    
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Laboratory Test Results Report', 0, 1, 'C');
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        function ChapterTitle($title) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, $title, 0, 1, 'L');
            $this->Ln(4);
        }

        function ChapterBody($body) {
            $this->SetFont('Arial', '', 12);
            $this->MultiCell(0, 10, $body);
            $this->Ln();
        }
    }

    // Initialize PDF
    $pdf = new PDF();
    $pdf->AddPage();
    
    // Create the table
    $pdf->ChapterTitle('Test Results');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 10, 'Result ID', 1);
    $pdf->Cell(40, 10, 'Patient Name', 1);
    $pdf->Cell(40, 10, 'Test Name', 1);
    $pdf->Cell(40, 10, 'Result', 1);
    $pdf->Cell(40, 10, 'Date', 1);
    $pdf->Cell(40, 10, 'Status', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    while ($row = mysqli_fetch_assoc($result)) {
        $pdf->Cell(40, 10, $row['resultID'], 1);
        $pdf->Cell(40, 10, $row['patientName'], 1);
        $pdf->Cell(40, 10, $row['testName'], 1);
        $pdf->Cell(40, 10, $row['result'], 1);
        $pdf->Cell(40, 10, $row['date'], 1);
        $pdf->Cell(40, 10, $row['status'], 1);
        $pdf->Ln();
    }

    // Output the PDF to the browser
    $pdf->Output();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Generate Laboratory Test Results Report</h1>

    <form action="generate_report.php" method="POST">
        <button type="submit" name="generate_report">Generate Report</button>
    </form>

    <h2>All Test Results</h2>
    <table>
        <thead>
            <tr>
                <th>Result ID</th>
                <th>Patient Name</th>
                <th>Test Name</th>
                <th>Result</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['resultID']}</td>
                        <td>{$row['patientName']}</td>
                        <td>{$row['testName']}</td>
                        <td>{$row['result']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
mysqli_close($conn);
?>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

h2 {
    text-align: center;
    margin-top: 20px;
}

table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: #fff;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
}

td {
    font-size: 14px;
    color: #555;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Button Styling */
button {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #218838;
}

</style>