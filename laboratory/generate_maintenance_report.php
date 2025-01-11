<?php
// Include FPDF library
require('fpdf/fpdf.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch equipment maintenance records
$sql = "SELECT * FROM equipment_maintenance";
$result = mysqli_query($conn, $sql);

// Create instance of FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Set title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Equipment Maintenance Report', 0, 1, 'C');

// Line break
$pdf->Ln(10);

// Set table header fonts
$pdf->SetFont('Arial', 'B', 7);

// Column headers
$header = ['Record ID', 'Equipment Name', 'Maintenance Type', 'Service Date', 'Next Calibration Date', 'Technician', 'Status'];

// Calculate column widths dynamically based on content
$columnWidths = [];
foreach ($header as $col) {
    $columnWidths[] = max($pdf->GetStringWidth($col), 30); // Ensuring minimum width of 30 for each column
}

// Total width of all columns
$totalWidth = array_sum($columnWidths);
$pageWidth = 190; // PDF page width

// Scaling factor to fit the table within the page width
$scalingFactor = $pageWidth / $totalWidth;

// Print header with proper alignment
$pdf->SetX(10); // Start position for the first cell
for ($i = 0; $i < count($header); $i++) {
    $pdf->Cell($columnWidths[$i] * $scalingFactor, 10, $header[$i], 1, 0, 'C');
}
$pdf->Ln();

// Set font for table rows
$pdf->SetFont('Arial', '', 9);

// Output rows with proper alignment
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->SetX(10); // Reset X position for each row
    $pdf->Cell($columnWidths[0] * $scalingFactor, 10, $row['recordID'], 1, 0, 'C');
    $pdf->Cell($columnWidths[1] * $scalingFactor, 10, $row['equipmentName'], 1, 0, 'C');
    $pdf->Cell($columnWidths[2] * $scalingFactor, 10, $row['maintenanceType'], 1, 0, 'C');
    $pdf->Cell($columnWidths[3] * $scalingFactor, 10, $row['serviceDate'], 1, 0, 'C');
    $pdf->Cell($columnWidths[4] * $scalingFactor, 10, $row['nextCalibrationDate'], 1, 0, 'C');
    $pdf->Cell($columnWidths[5] * $scalingFactor, 10, $row['technician'], 1, 0, 'C');
    $pdf->Cell($columnWidths[6] * $scalingFactor, 10, $row['status'], 1, 1, 'C');
}

// Close the connection
mysqli_close($conn);

// Output the PDF
$pdf->Output();
?>
