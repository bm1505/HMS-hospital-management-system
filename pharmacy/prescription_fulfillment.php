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

    // Insert discharge record and update the submission time
    $stmt = $conn->prepare("INSERT INTO discharge (patientName, dischargeDate, totalCost, submission_time) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssd", $patientName, $dischargeDate, $totalCost);

    if ($stmt->execute()) {
        echo "<script>alert('Discharge record added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding discharge record: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Fetch prescriptions with patient names only, excluding those submitted more than 5 minutes ago
$query = "SELECT 
            p.prescriptionID, 
            p.dateIssued, 
            p.status, 
            pt.first_name AS patientFirstName, 
            pt.last_name AS patientLastName
          FROM prescriptions p
          JOIN patients pt ON p.patientID = pt.patientID
          LEFT JOIN discharge d ON CONCAT(pt.first_name, ' ', pt.last_name) = d.patientName
          WHERE p.status = 'Pending'
            AND (d.submission_time IS NULL OR d.submission_time >= NOW() - INTERVAL 5 MINUTE)
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
            <a href="pharmacy.php" class="back-btn">⬅️ Back</a>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Date Issued</th>
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
                                    <form method="POST" action="update_prescription_status.php" style="display:inline;" class="fulfillForm">
                                        <input type="hidden" name="prescriptionID" value="<?php echo $row['prescriptionID']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Mark as Fulfilled</button>
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm viewMedicineBtn" 
                                        data-id="<?php echo $row['prescriptionID']; ?>"
                                        data-patientname="<?php echo htmlspecialchars($row['patientFirstName'] . ' ' . $row['patientLastName']); ?>"
                                    >View Medicine</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No pending prescriptions found.</td>
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
                    <!-- Medicine details table with Price column -->
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
                            <label for="totalCostInput">Total Cost</label>
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
    $(document).ready(function(){
        // When clicking on a View Medicine button
        $('.viewMedicineBtn').click(function(){
            var prescriptionID = $(this).data('id');
            var patientName = $(this).data('patientname');
            // Load medicine details using AJAX from fetch_medicines.php
            $.ajax({
                url: 'fetch_medicines.php',
                type: 'GET',
                data: { prescriptionID: prescriptionID },
                dataType: 'json',
                success: function(response){
                    var tableContent = "";
                    if(response.error) {
                        tableContent = "<tr><td colspan='5'>"+response.error+"</td></tr>";
                    } else {
                        $.each(response, function(index, medicine){
                            tableContent += "<tr>";
                            tableContent += "<td>" + medicine.medicationName + "</td>";
                            tableContent += "<td>" + medicine.quantities + "</td>";
                            tableContent += "<td>" + medicine.dosages + "</td>";
                            tableContent += "<td>" + medicine.instructions + "</td>";
                            tableContent += "<td><input type='number' class='form-control price-input' name='price[]' placeholder='Enter price'></td>";
                            tableContent += "</tr>";
                        });
                    }
                    $("#medicineDetails").html(tableContent);
                    // Reset total cost
                    $("#totalCost").text("0");
                    $("#totalCostInput").val("");
                    // Set the patient name in the discharge form
                    $("#patientName").val(patientName);
                    // Show the medicine modal
                    $("#medicineModal").modal("show");
                },
                error: function(){
                    $("#medicineDetails").html("<tr><td colspan='5'>Error fetching medicines.</td></tr>");
                    $("#medicineModal").modal("show");
                }
            });
        });
        
        // Calculate total cost whenever a price input changes
        $(document).on("input", ".price-input", function(){
            var total = 0;
            $(".price-input").each(function(){
                total += parseFloat($(this).val()) || 0;
            });
            $("#totalCost").text(total.toFixed(2));
            $("#totalCostInput").val(total.toFixed(2));
        });
        
        // When "Submit Price" is clicked, show the discharge modal
        $("#submitPriceBtn").click(function(){
            $("#medicineModal").modal("hide");
            $("#dischargeModal").modal("show");
        });
    });
    </script>
    <script>
$(document).ready(function(){
    $(".viewMedicineBtn").click(function(){
        var prescriptionID = $(this).data('id');
        var patientName = $(this).data('patientname');

        $.ajax({
            url: 'fetch_medicines.php',
            type: 'GET',
            data: { prescriptionID: prescriptionID },
            dataType: 'json',
            success: function(response){
                var tableContent = "";
                if(response.error) {
                    tableContent = "<tr><td colspan='6'>" + response.error + "</td></tr>";
                } else {
                    $.each(response, function(index, medicine){
                        tableContent += "<tr>";
                        tableContent += "<td>" + medicine.medicationName + "</td>";
                        tableContent += "<td>" + medicine.quantities + "</td>";
                        tableContent += "<td>" + medicine.dosages + "</td>";
                        tableContent += "<td>" + medicine.instructions + "</td>";
                        tableContent += "<td><input type='number' class='form-control price-input' name='price[]' placeholder='Selling Price'></td>";
                        tableContent += "<td><input type='number' class='form-control cost-input' name='cost[]' placeholder='Cost Price'></td>";
                        tableContent += "</tr>";
                    });
                }
                $("#medicineDetails").html(tableContent);
                $("#totalCost").text("0");
                $("#totalCostInput").val("");
                $("#patientName").val(patientName);
                $("#medicineModal").modal("show");
            },
            error: function(){
                $("#medicineDetails").html("<tr><td colspan='6'>Error fetching medicines.</td></tr>");
                $("#medicineModal").modal("show");
            }
        });
    });

    // Calculate total cost whenever input changes
    $(document).on("input", ".price-input, .cost-input", function(){
        var total = 0;
        $(".price-input").each(function(){
            total += parseFloat($(this).val()) || 0;
        });
        $("#totalCost").text(total.toFixed(2));
        $("#totalCostInput").val(total.toFixed(2));
    });

    // Send medicine sale data to server
    $("#submitPriceBtn").click(function(){
        var medicines = [];
        $("#medicineDetails tr").each(function(){
            var medicineName = $(this).find("td:eq(0)").text();
            var quantitySold = $(this).find("td:eq(1)").text();
            var sellingPrice = $(this).find(".price-input").val();
            var costPrice = $(this).find(".cost-input").val();
            var saleDate = new Date().toISOString().slice(0, 10);

            if(medicineName && sellingPrice && costPrice) {
                medicines.push({
                    medicine_name: medicineName,
                    quantity_sold: quantitySold,
                    selling_price: sellingPrice,
                    cost_price: costPrice,
                    sale_date: saleDate
                });
            }
        });

        if(medicines.length > 0){
            $.ajax({
                url: 'save_medicine_sale.php',
                type: 'POST',
                data: { medicines: JSON.stringify(medicines) },
                success: function(response){
                    alert(response);
                    $("#medicineModal").modal("hide");
                    $("#dischargeModal").modal("show");
                },
                error: function(){
                    alert("Error submitting sales data.");
                }
            });
        } else {
            alert("Please enter valid prices before submitting.");
        }
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>