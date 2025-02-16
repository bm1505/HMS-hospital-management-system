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

// Handle form submission for adding a new request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_request'])) {
    $medicineName = $_POST['medicineName'];
    $quantityRequested = $_POST['quantityRequested'];
    $requestDate = date('Y-m-d H:i:s');

    $sql = "INSERT INTO medicine_request (medicineName, quantityRequested, requestDate, status) 
            VALUES ('$medicineName', '$quantityRequested', '$requestDate', 'Pending')";
    if ($conn->query($sql) ){
        echo "<script>alert('Request added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding request: " . $conn->error . "');</script>";
    }
}

// Handle form submission for editing a request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_request'])) {
    $requestID = $_POST['requestID'];
    $medicineName = $_POST['medicineName'];
    $quantityRequested = $_POST['quantityRequested'];

    $sql = "UPDATE medicine_request 
            SET medicineName='$medicineName', quantityRequested='$quantityRequested' 
            WHERE requestID='$requestID'";
    if ($conn->query($sql)) {
        echo "<script>alert('Request updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating request: " . $conn->error . "');</script>";
    }
}

// Handle request deletion
if (isset($_GET['delete_request'])) {
    $requestID = $_GET['delete_request'];
    $sql = "DELETE FROM medicine_request WHERE requestID='$requestID'";
    if ($conn->query($sql)) {
        echo "<script>alert('Request deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting request: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Order Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 25px;
            color: var(--text-color);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .container {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        table {
            flex: 1;
            min-width: 400px;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        table:hover {
            transform: translateY(-2px);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: linear-gradient(135deg, var(--secondary-color), #1e88e5);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            position: relative;
            animation: modalOpen 0.3s ease;
        }

        @keyframes modalOpen {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #95a5a6;
        }

        .close:hover {
            color: #7f8c8d;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input {
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }

        .status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <a href="pharmacy.php" class="btn btn-primary">
            <span>⬅️ Back</span>
        </a>
        <h1>Medicine Management</h1>
        <button class="btn btn-primary" onclick="openAddRequestModal()">
            <span>➕ Add Request</span>
        </button>
    </div>

    <!-- Search Input -->
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search medicine by name..." oninput="searchMedicine()">
    </div>

    <div class="container">
        <!-- Current Medicine Stock Table -->
        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Medicine Name</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Unit Price</th>
                    <th>Supplier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM medicine_stock";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['stockID']}</td>
                            <td>{$row['medicineName']}</td>
                            <td>{$row['currentStock']}</td>
                            <td>{$row['reorderLevel']}</td>
                            <td>{$row['unitPrice']}</td>
                            <td>{$row['supplier']}</td>
                            <td>{$row['status']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Medicine Requests Table -->
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Medicine Name</th>
                    <th>Quantity Requested</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM medicine_request";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['requestID']}</td>
                            <td>{$row['medicineName']}</td>
                            <td>{$row['quantityRequested']}</td>
                            <td>{$row['requestDate']}</td>
                            <td>{$row['status']}</td>
                            <td>
                                <button onclick='openEditRequestModal({$row['requestID']}, \"{$row['medicineName']}\", {$row['quantityRequested']})'>Edit</button>
                                <button onclick='deleteRequest({$row['requestID']})'>Remove</button>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Request Modal -->
    <div id="addRequestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddRequestModal()">&times;</span>
            <h2>Add Medicine Request</h2>
            <form method="POST" action="">
                <label for="medicineName">Medicine Name:</label>
                <input type="text" id="medicineName" name="medicineName" required><br><br>
                <label for="quantityRequested">Quantity Requested:</label>
                <input type="number" id="quantityRequested" name="quantityRequested" required><br><br>
                <button type="submit" name="add_request">Submit</button>
            </form>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div id="editRequestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditRequestModal()">&times;</span>
            <h2>Edit Medicine Request</h2>
            <form method="POST" action="">
                <input type="hidden" id="editRequestID" name="requestID">
                <label for="editMedicineName">Medicine Name:</label>
                <input type="text" id="editMedicineName" name="medicineName" required><br><br>
                <label for="editQuantityRequested">Quantity Requested:</label>
                <input type="number" id="editQuantityRequested" name="quantityRequested" required><br><br>
                <button type="submit" name="edit_request">Update</button>
            </form>
        </div>
    </div>

    <!-- Medicine Details Modal -->
    <div id="medicineDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMedicineDetailsModal()">&times;</span>
            <h2>Medicine Details</h2>
            <div id="medicineDetailsContent"></div>
            <button class="btn btn-primary" onclick="openAddRequestModal()">Request</button>
            <button class="btn btn-secondary" onclick="openEditRequestModal()">Edit</button>
        </div>
    </div>

    <script>
        // Search Medicine Functionality
        function searchMedicine() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const medicineName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (medicineName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal functions
        function openAddRequestModal() {
            document.getElementById('addRequestModal').style.display = 'block';
        }
        function closeAddRequestModal() {
            document.getElementById('addRequestModal').style.display = 'none';
        }
        function openEditRequestModal(requestID, medicineName, quantityRequested) {
            document.getElementById('editRequestID').value = requestID;
            document.getElementById('editMedicineName').value = medicineName;
            document.getElementById('editQuantityRequested').value = quantityRequested;
            document.getElementById('editRequestModal').style.display = 'block';
        }
        function closeEditRequestModal() {
            document.getElementById('editRequestModal').style.display = 'none';
        }
        function deleteRequest(requestID) {
            if (confirm('Are you sure you want to delete this request?')) {
                window.location.href = `medicine_order_requests.php?delete_request=${requestID}`;
            }
        }
        function openMedicineDetailsModal(medicineName) {
            document.getElementById('medicineDetailsContent').innerHTML = `
                <p><strong>Medicine Name:</strong> ${medicineName}</p>
                <p><strong>Current Stock:</strong> 100</p>
                <p><strong>Reorder Level:</strong> 50</p>
                <p><strong>Unit Price:</strong> $10.00</p>
            `;
            document.getElementById('medicineDetailsModal').style.display = 'block';
        }
        function closeMedicineDetailsModal() {
            document.getElementById('medicineDetailsModal').style.display = 'none';
        }
    </script>
</body>
</html>