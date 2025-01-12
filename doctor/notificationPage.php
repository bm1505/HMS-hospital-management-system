<?php
// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "st_norbert_hospital";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch notifications
$sql = "SELECT * FROM notifications WHERE doctor_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$doctor_id = 1;  // Example doctor ID; replace this dynamically as needed
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $notification_id = $_POST['notification_id'];
    $reply = $_POST['reply_text'];

    $reply_sql = "INSERT INTO notification_replies (notification_id, doctor_id, reply_text, created_at) 
                  VALUES (?, ?, ?, NOW())";
    $reply_stmt = $conn->prepare($reply_sql);
    $reply_stmt->bind_param("iis", $notification_id, $doctor_id, $reply);
    $reply_stmt->execute();
    $reply_stmt->close();

    // Redirect to prevent resubmission of form
    header("Location: notificationPage.php");
    exit();
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor - Notification Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Hospital Management</a>
        <ul class="navbar-nav ml-auto">
        <li>
            <a class="nav-link" href="doctor.php">back</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Notification Page -->
    <div class="container mt-5">
        <h3>Notifications</h3>

        <?php if ($result->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <h5 class="mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="mb-2"><?php echo htmlspecialchars($row['message']); ?></p>
                        <p class="text-muted"><?php echo "Received on: " . $row['created_at']; ?></p>

                        <!-- Display replies if any -->
                        <?php
                        $notification_id = $row['id'];
                        $reply_sql = "SELECT * FROM notification_replies WHERE notification_id = ? ORDER BY created_at ASC";
                        $reply_stmt = $conn->prepare($reply_sql);
                        $reply_stmt->bind_param("i", $notification_id);
                        $reply_stmt->execute();
                        $reply_result = $reply_stmt->get_result();

                        if ($reply_result->num_rows > 0):
                        ?>
                            <div class="mt-3">
                                <strong>Replies:</strong>
                                <ul class="list-unstyled">
                                    <?php while ($reply = $reply_result->fetch_assoc()): ?>
                                        <li>
                                            <strong>Dr. <?php echo htmlspecialchars($reply['doctor_id']); ?>:</strong>
                                            <p><?php echo htmlspecialchars($reply['reply_text']); ?></p>
                                            <small class="text-muted"><?php echo $reply['created_at']; ?></small>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Reply form -->
                        <form method="POST" action="notificationPage.php" class="mt-3">
                            <input type="hidden" name="notification_id" value="<?php echo $row['id']; ?>">
                            <div class="form-group">
                                <label for="replyText">Reply</label>
                                <textarea name="reply_text" id="replyText" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="reply">Send Reply</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No notifications available.</p>
        <?php endif; ?>
    </div>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
