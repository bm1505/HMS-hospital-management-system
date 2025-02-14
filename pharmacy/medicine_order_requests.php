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

// Create table: medicine_stock
$sql = "CREATE TABLE IF NOT EXISTS medicine_stock (
    stockID INT AUTO_INCREMENT PRIMARY KEY,
    medicineName VARCHAR(255) NOT NULL,
    currentStock INT NOT NULL,
    reorderLevel INT NOT NULL,
    unitPrice DECIMAL(10,2) NOT NULL,
    supplier VARCHAR(255) NOT NULL,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating medicine_stock table: " . mysqli_error($conn));
}

// Create table: medicine_request
$sql = "CREATE TABLE IF NOT EXISTS medicine_request (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    medicineName VARCHAR(255) NOT NULL,
    quantityRequested INT NOT NULL,
    requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating medicine_request table: " . mysqli_error($conn));
}

// Create table: low_stock
$sql = "CREATE TABLE IF NOT EXISTS low_stock (
    lowStockID INT AUTO_INCREMENT PRIMARY KEY,
    medicineName VARCHAR(255) NOT NULL,
    currentStock INT NOT NULL,
    reorderLevel INT NOT NULL,
    recordedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating low_stock table: " . mysqli_error($conn));
}

/* --------- Function Definitions --------- */
function getMedicineStock($conn) {
    $sql = "SELECT * FROM medicine_stock";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getMedicineRequests($conn) {
    $sql = "SELECT * FROM medicine_request ORDER BY requestDate DESC";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getLowStock($conn) {
    $sql = "SELECT * FROM low_stock";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Record low-stock medicines from medicine_stock into low_stock table
function recordLowStock($conn) {
    $sql = "SELECT * FROM medicine_stock";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['currentStock'] < $row['reorderLevel']) {
            $medicineName = $row['medicineName'];
            // Only record if not already present in low_stock
            $checkExist = "SELECT * FROM low_stock WHERE medicineName = '$medicineName'";
            $existResult = mysqli_query($conn, $checkExist);
            if (mysqli_num_rows($existResult) == 0) {
                $sql_low = "INSERT INTO low_stock (medicineName, currentStock, reorderLevel) 
                            VALUES ('$medicineName', '".$row['currentStock']."', '".$row['reorderLevel']."')";
                mysqli_query($conn, $sql_low);
            }
        }
    }
}

// Auto-fulfill requests: if the requested medicine is now in stock (e.g. currentStock >= reorderLevel),
// then remove its request (adjust this condition as needed)
function autoFulfillRequests($conn) {
    $sql = "SELECT * FROM medicine_request";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($req = mysqli_fetch_assoc($result)) {
            $medicine = $req['medicineName'];
            $sqlStock = "SELECT * FROM medicine_stock WHERE medicineName='$medicine'";
            $stockRes = mysqli_query($conn, $sqlStock);
            if ($stockRes && mysqli_num_rows($stockRes) > 0) {
                $stockRow = mysqli_fetch_assoc($stockRes);
                if ($stockRow['currentStock'] >= $stockRow['reorderLevel']) {
                    mysqli_query($conn, "DELETE FROM medicine_request WHERE requestID='".$req['requestID']."'");
                }
            }
        }
    }
}

/* --------- Process Form Submissions (for non-AJAX requests) --------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['ajax_submit'])) {
    // Remove a medicine request
    if (isset($_POST['delete_request'])) {
        $requestID = intval($_POST['requestID']);
        mysqli_query($conn, "DELETE FROM medicine_request WHERE requestID = '$requestID'");
    }
    // Update (edit) a medicine request
    if (isset($_POST['update_request'])) {
        $requestID = intval($_POST['requestID']);
        $medicineName = $conn->real_escape_string($_POST['edit_medicineName']);
        $quantityRequested = intval($_POST['edit_quantityRequested']);
        $sql_update = "UPDATE medicine_request SET medicineName = '$medicineName', quantityRequested = '$quantityRequested' WHERE requestID = '$requestID'";
        mysqli_query($conn, $sql_update);
    }
}

// Process AJAX submissions from the modal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_submit'])) {
    // Check the type of request
    if (isset($_POST['request_medicine'])) {
        $request_type = $_POST['request_type'];
        if ($request_type == 'all') {
            $lowStocks = getLowStock($conn);
            foreach ($lowStocks as $low) {
                $medicineName = $low['medicineName'];
                $quantityRequested = $low['reorderLevel'] * 2 - $low['currentStock'];
                $sql_req = "INSERT INTO medicine_request (medicineName, quantityRequested) VALUES ('$medicineName', '$quantityRequested')";
                mysqli_query($conn, $sql_req);
            }
            mysqli_query($conn, "DELETE FROM low_stock");
        } elseif ($request_type == 'selected' && isset($_POST['selected_medicines'])) {
            foreach ($_POST['selected_medicines'] as $lowStockID) {
                $sql_low = "SELECT * FROM low_stock WHERE lowStockID = '$lowStockID'";
                $result_low = mysqli_query($conn, $sql_low);
                if ($result_low && $row = mysqli_fetch_assoc($result_low)) {
                    $medicineName = $row['medicineName'];
                    $quantityRequested = $row['reorderLevel'] * 2 - $row['currentStock'];
                    $sql_req = "INSERT INTO medicine_request (medicineName, quantityRequested) VALUES ('$medicineName', '$quantityRequested')";
                    mysqli_query($conn, $sql_req);
                    mysqli_query($conn, "DELETE FROM low_stock WHERE lowStockID = '$lowStockID'");
                }
            }
        }
    }
    // Manual request addition
    if (isset($_POST['manual_request'])) {
        $medicineName = (trim($_POST['manual_medicineName_alt']) !== '')
                        ? $conn->real_escape_string(trim($_POST['manual_medicineName_alt']))
                        : $conn->real_escape_string($_POST['manual_medicineName']);
        $quantityRequested = intval($_POST['manual_quantityRequested']);
        $sql_manual = "INSERT INTO medicine_request (medicineName, quantityRequested) VALUES ('$medicineName', '$quantityRequested')";
        mysqli_query($conn, $sql_manual);
    }
    // Send a JSON response and exit so that no further HTML is output.
    echo json_encode(["status" => "success"]);
    exit;
}

// When "Make Medicine Request" is clicked (non-AJAX), record low stock items so the modal can use them.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reorder_stock'])) {
    recordLowStock($conn);
    // We do not do a full page submission here; the modal will be opened via AJAX.
}

// Auto-fulfill any requests that are now met
autoFulfillRequests($conn);

// Retrieve data for display
$medicineStock    = getMedicineStock($conn);
$medicineRequests = getMedicineRequests($conn);
$lowStocks        = getLowStock($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medicine Store Inventory</title>
  <!-- Bootstrap CSS -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts & Font Awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Roboto', sans-serif; background-color: #f5f5f5; }
    h1, h2 { color: #333; text-align: center; margin-bottom: 20px; }
    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .table-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 6px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
    .table-container table { width: 100%; border-collapse: collapse; }
    .table-container th, .table-container td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    .table-container th { background-color: #007bff; color: #fff; font-size: 14px; text-transform: uppercase; font-weight: bold; }
    .table-container td { font-size: 14px; color: #555; }
    .table-container tr:hover { background-color: #f1f1f1; }
    .btn { padding: 8px 12px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; background-color: #28a745; color: white; border: none; }
    .btn:hover { background-color: #218838; }
    #searchResults { margin-top: 20px; }
    .action-btn { margin-right: 5px; }
  </style>
</head>
<body>
<div class="container">
  <h1>Medicine Store Inventory</h1>
  
  <!-- Search Input -->
  <div class="mb-4">
    <div class="input-group">
      <input type="text" id="medicineSearch" class="form-control" placeholder="Enter medicine name to search">
      <div class="input-group-append">
        <button class="btn btn-primary" id="searchBtn">Search</button>
      </div>
    </div>
  </div>
  <div id="searchResults"></div>
  
  <!-- Make Medicine Request and Back Buttons -->
  <div class="text-center mb-4">
    <form method="POST" style="display: inline-block;">
      <!-- The "reorder_stock" POST triggers recording of low stock items -->
      <button type="submit" name="reorder_stock" class="btn">Make Medicine Request</button>
    </form>
    <button style="background-color: #007bff; border: none; padding: 7px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
      <a href="pharmacy.php" style="text-decoration: none; color: white; font-size: 16px; font-weight: bold;">⬅️ Back</a>
    </button>
  </div>
  
  <!-- Inventory Tables -->
  <div class="row">
    <!-- Current Medicine Stock -->
    <div class="col-md-6">
      <div class="table-container">
        <h2>Current Medicine Stock</h2>
        <table>
          <thead>
            <tr>
              <th>Medicine Name</th>
              <th>Current Stock</th>
              <th>Reorder Level</th>
              <th>Unit Price</th>
              <th>Supplier</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($medicineStock as $stock): ?>
              <tr>
                <td><?= htmlspecialchars($stock['medicineName']) ?></td>
                <td><?= htmlspecialchars($stock['currentStock']) ?></td>
                <td><?= htmlspecialchars($stock['reorderLevel']) ?></td>
                <td><?= htmlspecialchars($stock['unitPrice']) ?></td>
                <td><?= htmlspecialchars($stock['supplier']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Medicine Requests -->
    <div class="col-md-6">
      <div class="table-container">
        <h2>Medicine Requests</h2>
        <table>
          <thead>
            <tr>
              <th>Medicine Name</th>
              <th>Quantity Requested</th>
              <th>Request Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($medicineRequests as $request): ?>
              <tr>
                <td><?= htmlspecialchars($request['medicineName']) ?></td>
                <td><?= htmlspecialchars($request['quantityRequested']) ?></td>
                <td><?= htmlspecialchars($request['requestDate']) ?></td>
                <td>
                  <!-- Remove Button (appears before Edit) -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="requestID" value="<?= $request['requestID'] ?>">
                    <button type="submit" name="delete_request" class="btn btn-danger btn-sm action-btn remove-btn">Remove</button>
                  </form>
                  <!-- Edit Button -->
                  <button class="btn btn-warning btn-sm action-btn edit-btn" 
                          data-requestid="<?= $request['requestID'] ?>"
                          data-medicine="<?= htmlspecialchars($request['medicineName']) ?>"
                          data-quantity="<?= $request['quantityRequested'] ?>">Edit</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Medicine Request Form with Tabs -->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="requestForm">
      <!-- Include a hidden field to indicate an AJAX submission -->
      <input type="hidden" name="ajax_submit" value="1">
      <input type="hidden" name="request_type" id="request_type" value="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="requestModalLabel">Medicine Request Form</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Nav tabs -->
          <ul class="nav nav-tabs" id="requestTab" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="select-tab" data-toggle="tab" href="#selectLowStock" role="tab">Select Low Stock</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="manual-tab" data-toggle="tab" href="#manualEntry" role="tab">Manual Entry</a>
            </li>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content mt-3">
            <!-- Tab 1: Select from Low Stock -->
            <div class="tab-pane fade show active" id="selectLowStock" role="tabpanel">
              <?php if(count($lowStocks) > 0): ?>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Select</th>
                    <th>Medicine Name</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Quantity to Request</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($lowStocks as $low): 
                    $qtyRequested = $low['reorderLevel'] * 2 - $low['currentStock']; ?>
                  <tr>
                    <td><input type="checkbox" name="selected_medicines[]" value="<?= $low['lowStockID'] ?>"></td>
                    <td><?= htmlspecialchars($low['medicineName']) ?></td>
                    <td><?= htmlspecialchars($low['currentStock']) ?></td>
                    <td><?= htmlspecialchars($low['reorderLevel']) ?></td>
                    <td><?= $qtyRequested ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php else: ?>
                <p>No low stock medicines recorded.</p>
              <?php endif; ?>
            </div>
            <!-- Tab 2: Manual Entry -->
            <div class="tab-pane fade" id="manualEntry" role="tabpanel">
              <div class="form-group">
                <label for="manual_medicineName">Medicine Name</label>
                <select class="form-control" name="manual_medicineName" id="manual_medicineName">
                  <option value="">-- Select Medicine --</option>
                  <?php 
                  $medNames = array_unique(array_column($medicineStock, 'medicineName'));
                  foreach($medNames as $mName): ?>
                    <option value="<?= htmlspecialchars($mName) ?>"><?= htmlspecialchars($mName) ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Or type the medicine name manually below.</small>
                <input type="text" class="form-control mt-2" name="manual_medicineName_alt" placeholder="Enter medicine name if not in list">
              </div>
              <div class="form-group">
                <label for="manual_quantityRequested">Quantity Requested</label>
                <input type="number" class="form-control" name="manual_quantityRequested" id="manual_quantityRequested" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <!-- Buttons for Select Low Stock -->
          <div id="selectActions">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="requestSelected" class="btn btn-primary">Request Selected</button>
            <button type="button" id="requestAll" class="btn btn-success">Request All</button>
          </div>
          <!-- Buttons for Manual Entry -->
          <div id="manualAction" style="display:none;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="addManualRequest" class="btn btn-primary">Add Manual Request</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Medicine Request -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" id="editForm">
      <input type="hidden" name="requestID" id="edit_requestID">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Medicine Request</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_medicineName">Medicine Name</label>
            <input type="text" class="form-control" name="edit_medicineName" id="edit_medicineName" required>
          </div>
          <div class="form-group">
            <label for="edit_quantityRequested">Quantity Requested</label>
            <input type="number" class="form-control" name="edit_quantityRequested" id="edit_quantityRequested" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="update_request" class="btn btn-primary">Update Request</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
  // Switch modal action buttons based on active tab
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href");
    if(target == "#selectLowStock"){
      $("#selectActions").show();
      $("#manualAction").hide();
    } else if(target == "#manualEntry"){
      $("#selectActions").hide();
      $("#manualAction").show();
    }
  });
  
  // If low stock items were recorded (via a non-AJAX POST), open the modal
  <?php if(isset($_POST['reorder_stock'])): ?>
    $('#requestModal').modal('show');
  <?php endif; ?>

  // AJAX submission for "Request Selected"
  $('#requestSelected').click(function(){
    $('#request_type').val('selected');
    $.post('<?= $_SERVER['PHP_SELF'] ?>', $('#requestForm').serialize(), function(response){
      var res = JSON.parse(response);
      if(res.status === "success"){
        $('#requestModal').modal('hide');
        location.reload();
      }
    });
  });

  // AJAX submission for "Request All"
  $('#requestAll').click(function(){
    $('#request_type').val('all');
    $.post('<?= $_SERVER['PHP_SELF'] ?>', $('#requestForm').serialize(), function(response){
      var res = JSON.parse(response);
      if(res.status === "success"){
        $('#requestModal').modal('hide');
        location.reload();
      }
    });
  });

  // AJAX submission for "Add Manual Request"
  $('#addManualRequest').click(function(){
    // If alternate manual name is provided, override the dropdown
    if($('#manual_medicineName_alt').val().trim() !== ''){
      $('#manual_medicineName').val($('#manual_medicineName_alt').val());
    }
    // Set a flag so the server knows it's a manual request
    var formData = $('#requestForm').serialize() + "&manual_request=1";
    $.post('<?= $_SERVER['PHP_SELF'] ?>', formData, function(response){
      var res = JSON.parse(response);
      if(res.status === "success"){
        $('#requestModal').modal('hide');
        location.reload();
      }
    });
  });
  
  // AJAX search functionality for medicine_stock
  $('#searchBtn').click(function(){
    var searchVal = $('#medicineSearch').val();
    if(searchVal.trim() == ""){
      alert("Please enter a medicine name to search.");
      return;
    }
    $.ajax({
      url: 'search_medicine.php',
      type: 'GET',
      data: { search: searchVal },
      dataType: 'json',
      success: function(response) {
        var html = '<h2>Search Results</h2>';
        if(response.length > 0){
          html += '<table class="table table-bordered table-hover"><thead><tr><th>Medicine Name</th><th>Current Stock</th><th>Reorder Level</th><th>Unit Price</th><th>Supplier</th></tr></thead><tbody>';
          $.each(response, function(i, med){
            html += '<tr>';
            html += '<td>' + med.medicineName + '</td>';
            html += '<td>' + med.currentStock + '</td>';
            html += '<td>' + med.reorderLevel + '</td>';
            html += '<td>' + med.unitPrice + '</td>';
            html += '<td>' + med.supplier + '</td>';
            html += '</tr>';
          });
          html += '</tbody></table>';
        } else {
          html += '<p>No matching medicine found.</p>';
        }
        $('#searchResults').html(html);
      },
      error: function(){
        alert("Error occurred while searching.");
      }
    });
  });
  
  // Edit button: open edit modal and populate fields
  $('.edit-btn').click(function(){
    var requestID = $(this).data('requestid');
    var medicine = $(this).data('medicine');
    var quantity = $(this).data('quantity');
    $('#edit_requestID').val(requestID);
    $('#edit_medicineName').val(medicine);
    $('#edit_quantityRequested').val(quantity);
    $('#editModal').modal('show');
  });
  
  // Remove button: default form submission will remove the record
  $('.remove-btn').click(function(){
    // You can add extra processing here if needed.
  });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
