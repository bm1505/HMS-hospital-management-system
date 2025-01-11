<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to get list of all inventory items
function getInventoryList($conn) {
    $sql = "SELECT * FROM inventory";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

// Add new medication to the inventory
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicationName = $_POST['medicationName'];
    $quantityInStock = $_POST['quantityInStock'];
    $expirationDate = $_POST['expirationDate'];
    $supplierName = $_POST['supplierName'];
    $supplierContact = $_POST['supplierContact'];
    $pricePerUnit = $_POST['pricePerUnit'];
    $reorderThreshold = $_POST['reorderThreshold'];
    $category = $_POST['category'];

    $sql = "INSERT INTO inventory (medicationName, quantityInStock, expirationDate, supplierName, supplierContact, pricePerUnit, reorderThreshold, category) 
            VALUES ('$medicationName', '$quantityInStock', '$expirationDate', '$supplierName', '$supplierContact', '$pricePerUnit', '$reorderThreshold', '$category')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Medication added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Delete medication item
if (isset($_GET['delete'])) {
    $medicationID = $_GET['delete'];
    $sql = "DELETE FROM inventory WHERE medicationID = '$medicationID'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Medication deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch inventory list
$inventoryList = getInventoryList($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Inventory Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Pharmacy Inventory Management</h2>

    <!-- Add Medication Form -->
    <form method="POST" action="inventory_management.php">
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
            <input type="date" class="form-control" id="expirationDate" name="expirationDate">
        </div>

        <div class="form-group">
            <label for="supplierName">Supplier Name</label>
            <input type="text" class="form-control" id="supplierName" name="supplierName">
        </div>

        <div class="form-group">
            <label for="supplierContact">Supplier Contact</label>
            <input type="text" class="form-control" id="supplierContact" name="supplierContact">
        </div>

        <div class="form-group">
            <label for="pricePerUnit">Price per Unit</label>
            <input type="number" class="form-control" id="pricePerUnit" name="pricePerUnit" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="reorderThreshold">Reorder Threshold</label>
            <input type="number" class="form-control" id="reorderThreshold" name="reorderThreshold" value="10" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" class="form-control" id="category" name="category">
        </div>

        <button type="submit" class="btn btn-primary">Add Medication</button>
    </form>

    <hr>

    <h3>Current Inventory</h3>
    <table class="table table-striped">
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
            <?php if (!empty($inventoryList)): ?>
                <?php foreach ($inventoryList as $item): ?>
                    <tr>
                        <td><?php echo $item['medicationName']; ?></td>
                        <td><?php echo $item['quantityInStock']; ?></td>
                        <td><?php echo $item['expirationDate']; ?></td>
                        <td><?php echo $item['supplierName']; ?></td>
                        <td><?php echo $item['pricePerUnit']; ?></td>
                        <td><?php echo $item['reorderThreshold']; ?></td>
                        <td><?php echo $item['category']; ?></td>
                        <td>
                            <a href="inventory_management.php?delete=<?php echo $item['medicationID']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No inventory items available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<footer class="footer bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2024 Pharmacy Management System</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
