<?php
require_once('tcpdf/tcpdf.php');

// Initialize TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Inventory Report');
$pdf->SetSubject('Inventory Report');

// Set header and footer
$pdf->setHeaderData('', 0, 'Inventory Report', 'Generated on: ' . date('Y-m-d H:i:s'));
$pdf->setFooterData('', array(0,64,0), array(0,64,128));

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add a page
$pdf->AddPage();

// Fetch inventory data from the database
$servername = "localhost";
$usernameDB = "root";
$passwordDB = "";
$dbname = "st_norbert_hospital";
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$query = "SELECT medicationName, quantityInStock, expirationDate, supplierName, pricePerUnit, reorderThreshold, category FROM inventory";
$result = $conn->query($query);
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$conn->close();

// Construct HTML table
$html = '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Medication Name</th>
                    <th>Quantity in Stock</th>
                    <th>Expiration Date</th>
                    <th>Supplier</th>
                    <th>Price per Unit</th>
                    <th>Reorder Threshold</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>';
foreach ($data as $row) {
    $html .= '<tr>
                <td>' . $row['medicationName'] . '</td>
                <td>' . $row['quantityInStock'] . '</td>
                <td>' . $row['expirationDate'] . '</td>
                <td>' . $row['supplierName'] . '</td>
                <td>' . $row['pricePerUnit'] . '</td>
                <td>' . $row['reorderThreshold'] . '</td>
                <td>' . $row['category'] . '</td>
              </tr>';
}
$html .= '</tbody>
        </table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, false, false, '');

// Output PDF
$pdf->Output('inventory_report.pdf', 'I');
?>
