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

// Process Add Medication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addMedication'])) {
    $medicationName   = $_POST['medicationName'];
    $quantityInStock  = $_POST['quantityInStock'];
    $expirationDate   = $_POST['expirationDate'];
    $supplierName     = $_POST['supplierName'];
    $supplierContact  = $_POST['supplierContact'];
    $pricePerUnit     = $_POST['pricePerUnit'];
    $reorderThreshold = $_POST['reorderThreshold'];
    $category         = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO inventory (medicationName, quantityInStock, expirationDate, supplierName, supplierContact, pricePerUnit, reorderThreshold, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssdis", $medicationName, $quantityInStock, $expirationDate, $supplierName, $supplierContact, $pricePerUnit, $reorderThreshold, $category);

    if ($stmt->execute()) {
        echo "<script>alert('Medication added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding medication: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Process Edit Medication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editMedication'])) {
    $medicationID     = $_POST['medicationID'];
    $medicationName   = $_POST['medicationName'];
    $quantityInStock  = $_POST['quantityInStock'];
    $expirationDate   = $_POST['expirationDate'];
    $supplierName     = $_POST['supplierName'];
    $supplierContact  = $_POST['supplierContact'];
    $pricePerUnit     = $_POST['pricePerUnit'];
    $reorderThreshold = $_POST['reorderThreshold'];
    $category         = $_POST['category'];

    $stmt = $conn->prepare("UPDATE inventory SET medicationName=?, quantityInStock=?, expirationDate=?, supplierName=?, supplierContact=?, pricePerUnit=?, reorderThreshold=?, category=? WHERE medicationID=?");
    $stmt->bind_param("sisssdisi", $medicationName, $quantityInStock, $expirationDate, $supplierName, $supplierContact, $pricePerUnit, $reorderThreshold, $category, $medicationID);
    if ($stmt->execute()) {
        echo "<script>alert('Medication updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating medication: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Process Delete Medication
if (isset($_GET['delete'])) {
    $medicationID = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE medicationID=?");
    $stmt->bind_param("i", $medicationID);
    if ($stmt->execute()) {
        echo "<script>alert('Medication deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting medication: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    // Redirect to avoid re-submission of deletion on page refresh
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch all medications
$query = "SELECT * FROM inventory";
$result = $conn->query($query);
if (!$result) {
    die("Error fetching medications: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medication Management</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container-fluid { padding: 20px; }
    .form-container, .table-container { padding: 20px; }
    .form-container { background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .table-container { background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .table thead th { background-color: #007bff; color: white; }
    .table tbody tr:hover { background-color: #f1f1f1; }
    .btn-print { margin-bottom: 20px; }
  </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Medication Table -->
        <div class="col-md-12 table-container">
            <h2 class="text-center mb-4">Current Medications</h2>
            <button class="btn btn-success btn-print" data-toggle="modal" data-target="#reportModal">Print Report</button>
            <!-- Button to trigger the add medication modal -->
            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addMedicationModal">Add New Medication</button>
            <button style="background-color: #007bff; border: none; padding: 7px 20px; border-radius: 5px; cursor: pointer;">
    <a href="pharmacy.php" style="text-decoration: none; color: white; font-size: 16px; font-weight: bold;">⬅️ Back</a>
</button>

            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Medication Name</th>
                            <th>Quantity in Stock</th>
                            <th>Expiration Date</th>
                            <th>Supplier</th>
                            <th>Price per Unit</th>
                            <th>Reorder Threshold</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['medicationName']); ?></td>
                                    <td><?= htmlspecialchars($row['quantityInStock']); ?></td>
                                    <td><?= htmlspecialchars($row['expirationDate']); ?></td>
                                    <td><?= htmlspecialchars($row['supplierName']); ?></td>
                                    <td><?= htmlspecialchars($row['pricePerUnit']); ?></td>
                                    <td><?= htmlspecialchars($row['reorderThreshold']); ?></td>
                                    <td><?= htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <!-- Edit button: passes row data using data-* attributes -->
                                        <button class="btn btn-warning btn-sm edit-btn" 
                                            data-id="<?= $row['medicationID']; ?>"
                                            data-medicationname="<?= htmlspecialchars($row['medicationName']); ?>"
                                            data-quantity="<?= $row['quantityInStock']; ?>"
                                            data-expiration="<?= $row['expirationDate']; ?>"
                                            data-supplier="<?= htmlspecialchars($row['supplierName']); ?>"
                                            data-contact="<?= htmlspecialchars($row['supplierContact']); ?>"
                                            data-price="<?= $row['pricePerUnit']; ?>"
                                            data-reorder="<?= $row['reorderThreshold']; ?>"
                                            data-category="<?= htmlspecialchars($row['category']); ?>"
                                            data-toggle="modal" data-target="#editMedicationModal">
                                            Edit
                                        </button>
                                        <!-- Delete button with confirmation -->
                                        <a href="?delete=<?= $row['medicationID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this medication?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No medications found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Medication Modal -->
<div class="modal fade" id="addMedicationModal" tabindex="-1" aria-labelledby="addMedicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMedicationModalLabel">Add New Medication</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                
                <div class="modal-body">
                    <!-- Form fields for adding medication -->
                    <div class="form-group">
                        <label for="medicationName">Medication Name</label>
                        <input type="text" class="form-control" id="medicationName" name="medicationName" required>
                    </div>
                    <div class="form-group">
                        <label for="quantityInStock">Quantity in Stock</label>
                        <input type="number" class="form-control" id="quantityInStock" name="quantityInStock" required>
                    </div>
                    <div class="form-group">
                        <label for="expirationDate">Expiration Date</label>
                        <input type="date" class="form-control" id="expirationDate" name="expirationDate" required>
                    </div>
                    <div class="form-group">
                        <label for="supplierName">Supplier Name</label>
                        <input type="text" class="form-control" id="supplierName" name="supplierName" required>
                    </div>
                    <div class="form-group">
                        <label for="supplierContact">Supplier Contact</label>
                        <input type="text" class="form-control" id="supplierContact" name="supplierContact" required>
                    </div>
                    <div class="form-group">
                        <label for="pricePerUnit">Price per Unit</label>
                        <input type="number" step="0.01" class="form-control" id="pricePerUnit" name="pricePerUnit" required>
                    </div>
                    <div class="form-group">
                        <label for="reorderThreshold">Reorder Threshold</label>
                        <input type="number" class="form-control" id="reorderThreshold" name="reorderThreshold" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="addMedication">Add Medication</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Medication Modal -->
<div class="modal fade" id="editMedicationModal" tabindex="-1" aria-labelledby="editMedicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMedicationModalLabel">Edit Medication</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden field for Medication ID -->
                    <input type="hidden" id="editMedicationID" name="medicationID">
                    <div class="form-group">
                        <label for="editMedicationName">Medication Name</label>
                        <input type="text" class="form-control" id="editMedicationName" name="medicationName" required>
                    </div>
                    <div class="form-group">
                        <label for="editQuantityInStock">Quantity in Stock</label>
                        <input type="number" class="form-control" id="editQuantityInStock" name="quantityInStock" required>
                    </div>
                    <div class="form-group">
                        <label for="editExpirationDate">Expiration Date</label>
                        <input type="date" class="form-control" id="editExpirationDate" name="expirationDate" required>
                    </div>
                    <div class="form-group">
                        <label for="editSupplierName">Supplier Name</label>
                        <input type="text" class="form-control" id="editSupplierName" name="supplierName" required>
                    </div>
                    <div class="form-group">
                        <label for="editSupplierContact">Supplier Contact</label>
                        <input type="text" class="form-control" id="editSupplierContact" name="supplierContact" required>
                    </div>
                    <div class="form-group">
                        <label for="editPricePerUnit">Price per Unit</label>
                        <input type="number" step="0.01" class="form-control" id="editPricePerUnit" name="pricePerUnit" required>
                    </div>
                    <div class="form-group">
                        <label for="editReorderThreshold">Reorder Threshold</label>
                        <input type="number" class="form-control" id="editReorderThreshold" name="reorderThreshold" required>
                    </div>
                    <div class="form-group">
                        <label for="editCategory">Category</label>
                        <input type="text" class="form-control" id="editCategory" name="category" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="editMedication">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Modal (existing code) -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reportForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Select Report Criteria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Report selection fields -->
                    <div class="form-group">
                        <label for="reportType">Report Type</label>
                        <select class="form-control" id="reportType" name="reportType" required>
                            <option value="">Select Report Type</option>
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="form-group" id="reportDateGroup" style="display:none;">
                        <label for="reportDate">Report Date</label>
                        <input type="date" class="form-control" id="reportDate" name="reportDate">
                    </div>
                    <div class="form-group" id="reportMonthGroup" style="display:none;">
                        <label for="reportMonth">Report Month</label>
                        <input type="month" class="form-control" id="reportMonth" name="reportMonth">
                    </div>
                    <div class="form-group" id="reportCustomGroup" style="display:none;">
                        <label for="reportFrom">From Date</label>
                        <input type="date" class="form-control" id="reportFrom" name="reportFrom">
                        <label for="reportTo" class="mt-2">To Date</label>
                        <input type="date" class="form-control" id="reportTo" name="reportTo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Print Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery, Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    // Toggle report fields based on report type selection
    $('#reportType').change(function(){
        var type = $(this).val();
        $('#reportDateGroup, #reportMonthGroup, #reportCustomGroup').hide();
        if(type === 'daily'){
            $('#reportDateGroup').show();
        } else if(type === 'monthly'){
            $('#reportMonthGroup').show();
        } else if(type === 'custom'){
            $('#reportCustomGroup').show();
        }
    });

    // Handle report form submission
    $('#reportForm').submit(function(e){
        e.preventDefault();
        var reportData = $(this).serializeArray();
        var message = "Report Criteria:\n";
        $.each(reportData, function(i, field){
            message += field.name + ": " + field.value + "\n";
        });
        alert(message);
        $('#reportModal').modal('hide');
    });

    // Populate the Edit Medication Modal with data from the clicked row
    $('.edit-btn').click(function(){
        var medID = $(this).data('id');
        var medName = $(this).data('medicationname');
        var quantity = $(this).data('quantity');
        var expiration = $(this).data('expiration');
        var supplier = $(this).data('supplier');
        var contact = $(this).data('contact');
        var price = $(this).data('price');
        var reorder = $(this).data('reorder');
        var category = $(this).data('category');

        $('#editMedicationID').val(medID);
        $('#editMedicationName').val(medName);
        $('#editQuantityInStock').val(quantity);
        $('#editExpirationDate').val(expiration);
        $('#editSupplierName').val(supplier);
        $('#editSupplierContact').val(contact);
        $('#editPricePerUnit').val(price);
        $('#editReorderThreshold').val(reorder);
        $('#editCategory').val(category);
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
