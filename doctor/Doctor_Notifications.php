<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "st_norbert_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check doctor authentication (you should implement proper authentication)
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit();
}

$doctorID = $_SESSION['doctor_id'];

// Handle notification reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendReply'])) {
    $notificationID = mysqli_real_escape_string($conn, $_POST['notification_id']);
    $replyMessage = mysqli_real_escape_string($conn, $_POST['reply_message']);
    
    // Insert reply
    $sql = "INSERT INTO notification_replies (notificationID, doctorID, message, replyDate) 
            VALUES ('$notificationID', '$doctorID', '$replyMessage', NOW())";
    mysqli_query($conn, $sql);
    
    // Update notification status to Read
    $updateSql = "UPDATE doctor_notifications SET status='Read' WHERE notificationID='$notificationID'";
    mysqli_query($conn, $updateSql);
}

// Fetch doctor's notifications
$notificationQuery = "SELECT * FROM doctor_notifications WHERE doctorID='$doctorID' ORDER BY dateSent DESC";
$notificationResult = mysqli_query($conn, $notificationQuery);
$notifications = mysqli_fetch_all($notificationResult, MYSQLI_ASSOC);

// Fetch doctor's details for display
$doctorQuery = "SELECT firstName, middleName, surname FROM doctors WHERE doctorID='$doctorID'";
$doctorResult = mysqli_query($conn, $doctorQuery);
$doctor = mysqli_fetch_assoc($doctorResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Notifications</title>
    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f6fa;
            color: #2d3436;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .notification-list {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .notification-item {
            border-bottom: 1px solid #eee;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .notification-item.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #2980b9;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .notification-sender {
            color: #2980b9;
            font-weight: 500;
        }

        .notification-date {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .notification-message {
            margin-bottom: 15px;
            color: #2d3436;
        }

        .reply-section {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .reply-form {
            display: none;
            margin-top: 10px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            resize: vertical;
        }

        .reply-list {
            margin-top: 15px;
            padding-left: 20px;
            border-left: 2px solid #2980b9;
        }

        .reply-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .reply-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #2980b9;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1f6391;
        }

        .status-indicator {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-unread {
            background-color: #e67e22;
            color: white;
        }

        .status-read {
            background-color: #27ae60;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, Dr. <?php echo $doctor['firstName'] . ' ' . $doctor['surname']; ?></h1>
            <a href="doctor_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>

        <div class="notification-list">
            <h2>Your Notifications</h2>
            
            <?php if (empty($notifications)): ?>
                <p>No notifications found.</p>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['status'] === 'Unread' ? 'unread' : ''; ?>">
                        <div class="notification-header">
                            <span class="notification-sender">
                                From: <?php echo $notification['sentBy']; ?>
                            </span>
                            <span class="notification-date">
                                <?php echo date('M j, Y H:i', strtotime($notification['dateSent'])); ?>
                            </span>
                        </div>
                        
                        <div class="notification-message">
                            <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                        </div>

                        <div class="status-indicator status-<?php echo strtolower($notification['status']); ?>">
                            <?php echo $notification['status']; ?>
                        </div>

                        <!-- Replies Section -->
                        <?php
                        $replyQuery = "SELECT * FROM notification_replies 
                                      WHERE notificationID='{$notification['notificationID']}'
                                      ORDER BY replyDate DESC";
                        $replyResult = mysqli_query($conn, $replyQuery);
                        $replies = mysqli_fetch_all($replyResult, MYSQLI_ASSOC);
                        ?>

                        <?php if (!empty($replies)): ?>
                            <div class="reply-list">
                                <?php foreach ($replies as $reply): ?>
                                    <div class="reply-item">
                                        <div class="reply-meta">
                                            <span>Your reply</span>
                                            <span><?php echo date('M j, Y H:i', strtotime($reply['replyDate'])); ?></span>
                                        </div>
                                        <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Reply Form -->
                        <div class="reply-section">
                            <button class="btn btn-primary toggle-reply">Reply</button>
                            
                            <form class="reply-form" method="POST" 
                                  action="doctor_notifications.php">
                                <input type="hidden" name="notification_id" 
                                       value="<?php echo $notification['notificationID']; ?>">
                                <textarea name="reply_message" rows="3" 
                                          placeholder="Type your reply..." required></textarea>
                                <button type="submit" name="sendReply" 
                                        class="btn btn-primary">Send Reply</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle reply form visibility
        document.querySelectorAll('.toggle-reply').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const form = e.target.closest('.reply-section').querySelector('.reply-form');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });
        });

        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>