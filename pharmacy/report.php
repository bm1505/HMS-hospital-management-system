<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit;
}

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the sum of medicine quantities in stock
$query = "SELECT medicineName, SUM(currentStock) AS totalStock FROM medicine_stock GROUP BY medicineName";
$result = $conn->query($query);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medicine Stock Report</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 2rem; }
    .table thead th { background-color: #007bff; color: white; }
    .table tbody tr:hover { background-color: #f1f1f1; }
    .print-button { text-align: center; margin-top: 20px; }
    .print-button button { padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    .print-button button:hover { background-color: #0056b3; }
    @media print {
      .print-button { display: none; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="text-center mb-4">Medicine Stock Report</h2>
    <p class="text-center">Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>

    <!-- Medicine Stock Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Medication Name</th>
            <th>Total Quantity in Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data)): ?>
            <?php foreach ($data as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['medicineName']); ?></td>
                <td><?php echo htmlspecialchars($row['totalStock']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="2" class="text-center">No data found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Print Button -->
    <div class="print-button">
      <button onclick="window.print()">Print Report</button>
    </div>
  </div>

  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>