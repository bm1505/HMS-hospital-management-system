<?php
// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create tables (same as before)
// ... [table creation code remains identical] ...

// Function to fetch all discharge records
function getDischarges($conn) {
    $sql = "SELECT * FROM discharge";
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        die("Error executing query: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Handle form submission for marking payment as paid
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_paid'])) {
        $patientName = mysqli_real_escape_string($conn, $_POST['patientName']);
        $sql = "UPDATE discharge SET paymentStatus = 'Paid' WHERE patientName = '$patientName'";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Payment marked as Paid!');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Fetch discharge records
$discharges = getDischarges($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment, Transaction, and Billing Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .desc {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        .table-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th, .table-container td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table-container th {
            background-color: #007bff;
            color: #fff;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .table-container td {
            font-size: 14px;
            color: #555;
        }
        .table-container tr:hover {
            background-color: #f1f1f1;
        }
        .payment-status.paid {
            color: #28a745;
        }
        .payment-status.pending {
            color: #dc3545;
        }
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            background-color: #28a745;
            color: white;
            border: none;
        }
        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .btn:hover:not(:disabled) {
            background-color: #218838;
        }
        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .modal-footer {
            border-radius: 0 0 10px 10px;
        }
        /* Print Styles */
        @media print {
            /* Hide all non-printable elements */
            body * {
                visibility: hidden;
            }
            /* Make the print area visible */
            #printArea, #printArea * {
                visibility: visible;
            }
            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
            }
            /* Hide header, footer, and buttons in the print view */
            .no-print {
                display: none !important;
            }
            /* Style for the PAID stamp */
            .paid-stamp {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 40px;
                color: red;
                opacity: 0.7;
                border: 3px solid red;
                border-radius: 50%;
                width: 100px;
                height: 100px;
                line-height: 100px;
                text-align: center;
            }
            /* Signature and date line */
            .signature-line {
                margin-top: 20px;
                border-top: 1px solid #000;
                width: 50%;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <a href="pharmacy.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> 
        </a>

        <h1>Payment, Transaction, and Billing Management</h1>
        <p class="desc">Handle payments, track transactions, and manage billing efficiently.</p>

        <!-- Search Bar -->
        <div class="form-group mb-3">
            <input type="text" id="searchPatient" class="form-control" placeholder="Search patient by name...">
        </div>

        <!-- Discharge (Bills) Table -->
        <div class="table-container">
            <table id="patientTable">
                <thead>
                    <tr>
                        <th>Patient Name</th>                      
                        <th>Total Cost</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discharges as $discharge): ?>
                        <tr data-payment-status="<?= strtolower($discharge['paymentStatus']) ?>">
                            <td><?= htmlspecialchars($discharge['patientName']) ?></td>
                            <td>Tsh<?= number_format($discharge['totalCost'], 2) ?></td>
                            <td class="payment-status <?= strtolower($discharge['paymentStatus']) ?>">
                                <?= $discharge['paymentStatus'] ?>
                            </td>
                            <td>
                                <!-- Open pop-up form for payment verification -->
                                <?php if ($discharge['paymentStatus'] === 'Pending'): ?>
                                    <button class="btn btn-primary btn-sm verify-payment-btn" 
                                        data-toggle="modal" 
                                        data-target="#paymentModal"
                                        data-patientname="<?= htmlspecialchars($discharge['patientName']) ?>">
                                        Verify Payment
                                    </button>
                                <?php endif; ?>
                                <!-- Print bill button -->
                                <button class="btn btn-secondary btn-sm print-btn" 
                                    data-patientname="<?= htmlspecialchars($discharge['patientName']) ?>"
                                    data-total="<?= number_format($discharge['totalCost'], 2) ?>"
                                    data-payment="<?= $discharge['paymentStatus'] ?>"
                                    data-dischargedate="<?= $discharge['dischargeDate'] ?>">
                                    Print Bill
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Verification Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header no-print">
                    <h5 class="modal-title" id="paymentModalLabel">Verify Payment</h5>
                    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="patientName" id="modalPatientName">
                        <div class="form-group">
                            <label for="paymentStatus">Payment Status</label>
                            <select class="form-control" id="paymentStatus" name="paymentStatus">
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <button type="submit" name="mark_paid" class="btn btn-primary no-print">Mark as Paid</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Bill Modal -->
    <div class="modal fade" id="printBillModal" tabindex="-1" aria-labelledby="printBillModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="printArea">
                <div class="modal-header no-print">
                    <h5 class="modal-title" id="printBillModalLabel">Payment Bill</h5>
                    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- These details will be the only ones printed -->
                    <p><strong>Patient Name:</strong> <span id="printPatientName"></span></p>
                    <p><strong>Discharge Date:</strong> <span id="printDischargeDate"></span></p>
                    <p><strong>Total Cost:</strong> Tsh<span id="printTotalCost"></span></p>
                    <p><strong>Payment Status:</strong> <span id="printPaymentStatus"></span></p>
                    <!-- Stamp container for the PAID stamp -->
                    <div id="stampContainer"></div>

                    <!-- Signature and Date Section -->
                    <div style="margin-top: 30px;">
                        <p><strong>Patient Signature:</strong></p>
                        <div class="signature-line"></div>
                        <p style="margin-top: 10px;"><strong>Date:</strong></p>
                        <div class="signature-line"></div>
                    </div>
                </div>
                <div class="modal-footer no-print">
                    <button type="button" class="btn btn-primary" onclick="printBill()">Print Bill</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function(){
        // Search functionality
        $('#searchPatient').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#patientTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // When Verify Payment button is clicked, populate the modal field
        $('.verify-payment-btn').click(function(){
            var patientName = $(this).data('patientname');
            $('#modalPatientName').val(patientName);
        });

        // When Print Bill button is clicked, populate and show the print modal
        $('.print-btn').click(function(){
            var patientName = $(this).data('patientname');
            var total = $(this).data('total');
            var payment = $(this).data('payment');
            var dischargeDate = $(this).data('dischargedate');

            $('#printPatientName').text(patientName);
            $('#printTotalCost').text(total);
            $('#printPaymentStatus').text(payment);
            $('#printDischargeDate').text(dischargeDate);

            // If payment is Paid, add a red round stamp overlay
            if(payment.toLowerCase() === 'paid'){
                $('#stampContainer').html('<div class="paid-stamp">PAID</div>');
            } else {
                $('#stampContainer').html('');
            }
            
            $('#printBillModal').modal('show');
        });

        // Auto-hide paid patients after 24 hours
        $('tr[data-payment-status="paid"]').each(function() {
            var $row = $(this);
            setTimeout(function() {
                $row.fadeOut(1000, function() {
                    $row.remove();
                });
            }, 24 * 60 * 60 * 1000); // 24 hours
        });
    });

    // Function to trigger the browser print dialog
    function printBill(){
        window.print();
    }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>