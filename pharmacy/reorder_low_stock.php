<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to reorder low stock medicines
function reorderLowStockMedicines($conn) {
    $sql = "SELECT * FROM medicines";
    $result = mysqli_query($conn, $sql);

    while ($medicine = mysqli_fetch_assoc($result)) {
        $medicineID = $medicine['medicineID'];
        $medicineName = $medicine['medicineName'];
        $quantity = $medicine['quantity'];
        $reorderLevel = $medicine['reorderLevel'];

        if ($quantity < $reorderLevel) {
            $quantityRequested = $reorderLevel * 2 - $quantity;

            $checkSql = "SELECT * FROM medicine_order_requests 
                         WHERE medicineID = '$medicineID' AND status = 'Pending'";
            $checkResult = mysqli_query($conn, $checkSql);

            if (mysqli_num_rows($checkResult) == 0) {
                $insertSql = "INSERT INTO medicine_order_requests (medicineID, medicineName, quantityRequested) 
                              VALUES ('$medicineID', '$medicineName', '$quantityRequested')";
                mysqli_query($conn, $insertSql);
            }
        }
    }
}

reorderLowStockMedicines($conn);
mysqli_close($conn);
?>