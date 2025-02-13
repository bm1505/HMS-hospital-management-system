<?php
session_start();

// Check if the user is logged in (assuming pharmacist or admin access)
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

// Handle form submission for discharge
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitDischarge'])) {
    $patientName = $_POST['patientName'];
    $dischargeDate = $_POST['dischargeDate'];
    $totalCost = $_POST['totalCost'];

    $stmt = $conn->prepare("INSERT INTO discharge (patientName, dischargeDate, totalCost) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $patientName, $dischargeDate, $totalCost);

    if ($stmt->execute()) {
        echo "<script>alert('Discharge record added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding discharge record: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Fetch prescriptions with patient names and aggregated medicine details
$query = "SELECT 
            p.prescriptionID, 
            p.patientID, 
            p.doctorID, 
            p.created_at,
            p.dateIssued, 
            p.status, 
            pt.first_name AS patientFirstName, 
            pt.last_name AS patientLastName,
            GROUP_CONCAT(COALESCE(pm.medicationName, 'N/A') SEPARATOR '|') AS medicationNames,
            GROUP_CONCAT(COALESCE(pm.quantity, 'N/A') SEPARATOR '|') AS quantity,
            GROUP_CONCAT(COALESCE(pm.dosage, 'N/A') SEPARATOR '|') AS dosage,
            GROUP_CONCAT(COALESCE(pm.instructions, 'N/A') SEPARATOR '|') AS instructions
          FROM prescriptions p
          JOIN patients pt ON p.patientID = pt.patientID
          LEFT JOIN prescription_medicines pm ON p.prescriptionID = pm.prescriptionID
          WHERE p.status = 'Pending'
          GROUP BY p.prescriptionID
          ORDER BY p.dateIssued DESC";


$result = $conn->query($query);
if (!$result) {
    die("Error fetching prescriptions: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prescription Fulfillment</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 2rem; }
    .table thead th { background-color: #007bff; color: white; }
    .table tbody tr:hover { background-color: #f1f1f1; }
    .status-pending { color: #dc3545; font-weight: bold; }
    .done-text { color: #28a745; font-weight: bold; }
    .medicine-modal { max-width: 800px; }
    .total-cost { font-size: 18px; font-weight: bold; margin-top: 10px; }
  </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Prescription Fulfillment</h2>
        <!-- Prescription Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Date Issued</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>View Medicine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['prescriptionID']; ?>">
                                <td><?php echo htmlspecialchars($row['patientFirstName'] . ' ' . $row['patientLastName']); ?></td>
                                <td><?php echo htmlspecialchars($row['dateIssued']); ?></td>
                                <td>
                                    <?php if (strtolower($row['status']) == 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="done-text">Done</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="update_prescription_status.php" style="display:inline;" class="fulfillForm">
                                        <input type="hidden" name="prescriptionID" value="<?php echo $row['prescriptionID']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Mark as Fulfilled</button>
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm viewMedicineBtn" 
                                        data-id="<?php echo $row['prescriptionID']; ?>"
                                        data-medicines="<?php echo htmlspecialchars($row['medicationNames']); ?>"
                                        data-quantity="<?php echo htmlspecialchars($row['quantity']); ?>"
                                        data-dosage="<?php echo htmlspecialchars($row['dosage']); ?>"
                                        data-instructions="<?php echo htmlspecialchars($row['instructions']); ?>"
                                        data-patientname="<?php echo htmlspecialchars($row['patientFirstName'] . ' ' . $row['patientLastName']); ?>"
                                    >View Medicine</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No pending prescriptions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Medicine Details Modal -->
    <div class="modal fade" id="medicineModal" tabindex="-1" aria-labelledby="medicineModalLabel" aria-hidden="true">
        <div class="modal-dialog medicine-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="medicineModalLabel">Medicine Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Input to add new medicine -->
                    <div class="form-group">
                        <label for="newMedicine">Add New Medicine:</label>
                        <input type="text" class="form-control" id="newMedicine" placeholder="Enter medicine name">
                        <button class="btn btn-secondary btn-sm mt-2" id="addMedicineBtn">Add Medicine</button>
                    </div>
                    <hr>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Quantity</th>
                                <th>Dosage</th>
                                <th>Instructions</th>
                                <th>Price (Tsh)</th>
                            </tr>
                        </thead>
                        <tbody id="medicineDetails">
                            <!-- Medicine details will be inserted here dynamically -->
                        </tbody>
                    </table>
                    <div class="total-cost">
                        Total Cost: <span id="totalCost">0</span> Tsh
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitPriceBtn">Submit Price</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Discharge Form Modal -->
    <div class="modal fade" id="dischargeModal" tabindex="-1" aria-labelledby="dischargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dischargeModalLabel">Discharge Patient</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="patientName">Patient Name</label>
                            <input type="text" class="form-control" id="patientName" name="patientName" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="dischargeDate">Discharge Date</label>
                            <input type="date" class="form-control" id="dischargeDate" name="dischargeDate" required>
                        </div>
                        <div class="form-group">
                            <label for="totalCost">Total Cost</label>
                            <input type="number" class="form-control" id="totalCostInput" name="totalCost" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submitDischarge">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        let totalCost = 0;
        
        // Function to calculate total cost
        function calculateTotalCost() {
            totalCost = 0;
            $(".price-input").each(function() {
                const price = parseFloat($(this).val()) || 0;
                totalCost += price;
            });
            $("#totalCost").text(totalCost.toFixed(2));
            $("#totalCostInput").val(totalCost.toFixed(2));
        }

        // Handle "View Medicine" button click using event delegation
        $(document).on("click", ".viewMedicineBtn", function() {
            // Retrieve data from data attributes and ensure they are strings before splitting
            let medData    = $(this).data("medicines") || "";
            let qtyData    = $(this).data("quantity") || "";
            let dosageData = $(this).data("dosages") || "";
            let instrData  = $(this).data("instructions") || "";
            let patientName = $(this).data("patientname") || "";

            // Convert data to arrays; if data is empty, you'll get an empty array.
            let medicines    = (typeof medData === "string" && medData.length > 0)    ? medData.split("|") : [];
            let quantities   = (typeof qtyData === "string" && qtyData.length > 0)    ? qtyData.split("|") : [];
            let dosages      = (typeof dosageData === "string" && dosageData.length > 0) ? dosageData.split("|") : [];
            let instructions = (typeof instrData === "string" && instrData.length > 0)  ? instrData.split("|") : [];

            // Build table rows for each medicine
            let tableContent = "";
            for (let i = 0; i < medicines.length; i++) {
                tableContent += "<tr>";
                tableContent += "<td>" + medicines[i] + "</td>";
                tableContent += "<td>" + (quantities[i] || "") + "</td>";
                tableContent += "<td>" + (dosages[i] || "") + "</td>";
                tableContent += "<td>" + (instructions[i] || "") + "</td>";
                tableContent += '<td><input type="number" class="form-control price-input" placeholder="Enter price"></td>';
                tableContent += "</tr>";
            }
            $("#medicineDetails").html(tableContent);
            calculateTotalCost();
            // Set the patient name in the discharge form
            $("#patientName").val(patientName);
            // Show the medicine modal
            $("#medicineModal").modal("show");
        });

        // Handle "Add Medicine" button click
        $("#addMedicineBtn").click(function() {
            const newMedicine = $("#newMedicine").val().trim();
            if (newMedicine) {
                const newRow = `
                    <tr>
                        <td>${newMedicine}</td>
                        <td>1</td>
                        <td>-</td>
                        <td>-</td>
                        <td><input type="number" class="form-control price-input" placeholder="Enter price"></td>
                    </tr>`;
                $("#medicineDetails").append(newRow);
                $("#newMedicine").val("");
            }
        });

        // Recalculate total cost on price input change
        $(document).on("input", ".price-input", function() {
            calculateTotalCost();
        });

        // When "Submit Price" is clicked, show the discharge modal
        $("#submitPriceBtn").click(function() {
            $("#dischargeModal").modal("show");
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
