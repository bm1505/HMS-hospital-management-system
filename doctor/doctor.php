<?php
session_start();

// Ensure the doctor is logged in
if (!isset($_SESSION['doctorID'])) {
    header("Location: index.php");
    exit;
}

$doctorID = $_SESSION['doctorID'];

// Database connection
$servername  = "localhost";
$username_db = "root";
$password_db = "";
$dbname      = "st_norbert_hospital";
$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to retrieve session token from the database for the given doctor
function getSessionTokenFromDatabase($conn, $doctorID) {
    $query = "SELECT session_token FROM doctors WHERE doctorID = '$doctorID'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['session_token'];
    }
    return null;
}




// Optional: generate a unique identifier for each tab if desired
if (!isset($_SESSION['tab_id'])) {
    $_SESSION['tab_id'] = uniqid();
}
$tab_id = $_SESSION['tab_id'];

// Fetch doctor's details and current status
$doctorQuery = "SELECT firstName, middleName, surname, status FROM doctors WHERE doctorID='$doctorID'";
$doctorResult = mysqli_query($conn, $doctorQuery);
if (!$doctorResult) {
    die("Error fetching doctor: " . mysqli_error($conn));
}
$doctorRow = mysqli_fetch_assoc($doctorResult);
$doctorName = trim($doctorRow['firstName'] . ' ' . $doctorRow['middleName'] . ' ' . $doctorRow['surname']);
$doctorStatus = $doctorRow['status'];

// Handle status toggle (e.g. In/Out)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_status'])) {
    $status = $_POST['status'];
    $update = "UPDATE doctors SET status='$status' WHERE doctorID='$doctorID'";
    mysqli_query($conn, $update);
    header("Location: doctor.php");
    exit;
}

// Fetch unread notifications
$notificationQuery = "SELECT * FROM doctor_notifications 
                      WHERE doctorID='$doctorID' 
                      AND status='unread'
                      ORDER BY dateSent DESC";
$notificationResult = mysqli_query($conn, $notificationQuery);
$notifications = mysqli_fetch_all($notificationResult, MYSQLI_ASSOC);
$notificationCount = count($notifications);

// Handle reply submission for notifications
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    $id = mysqli_real_escape_string($conn, $_POST['notification_id']);
    
    // Insert reply
    $insertReply = "INSERT INTO notification_replies (notification_id, doctor_id, reply) 
                   VALUES ('$id', '$doctorID', '$reply')";
    mysqli_query($conn, $insertReply);
    
    // Mark notification as read
    $update = "UPDATE doctor_notifications SET status='read' WHERE id='$id'";
    mysqli_query($conn, $update);
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Module Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <span class="navbar-text">
                <strong><?php echo htmlspecialchars($doctorName); ?></strong>
            </span>
            <span class="navbar-text ml-auto" id="currentDateTime"></span>
            <form method="POST" action="" class="ml-3">
                <input type="hidden" name="status" value="<?php echo $doctorStatus === 'In' ? 'Out' : 'In'; ?>">
                <button type="submit" name="toggle_status" class="status-btn">
                    <?php echo $doctorStatus === 'In' ? 'Out' : 'In'; ?>
                </button>
            </form>
            <form action="../logout.php" method="POST" class="ml-3">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <!-- Dashboard Modules -->
    <div class="dashboard-container container mt-4">
        <div class="row">
            <?php 
            $modules = [
                ["Appointment Scheduling", "fas fa-calendar-check", "Schedule appointments for doctors and patients.", "scheduleAppointment.php"],
                ["Patient Diagnosis", "fas fa-user-md", "Access medical history, record diagnoses, and prescribe treatments.", "patient_diagnosis.php"],
                ["Electronic Medical Records", "fas fa-file-medical", "View and update patient medical history and doctor's notes.", "emr.php"],
                ["Prescription Management", "fas fa-prescription", "Create and send prescriptions to the pharmacy.", "prescription.php"],
                ["Reports", "fas fa-file-alt", "Generate a technical report and review medical reports.", "report.php"],
                ["Notifications", "fas fa-bell", "Check the latest notifications and reply to sender.", "#"]
            ];
            foreach ($modules as $module): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon mb-3"><i class="<?php echo $module[1]; ?>"></i></div>
                        <h5 class="card-title"><?php echo $module[0]; ?></h5>
                        <p class="card-text"><?php echo $module[2]; ?></p>
                        
                        <?php if($module[0] == 'Notifications'): ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#notificationModal">
                                View Notifications
                                <?php if($notificationCount > 0): ?>
                                    <span class="notification-badge"><?php echo $notificationCount; ?></span>
                                <?php endif; ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo $module[3]; ?>" class="btn btn-primary">Explore</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade notification-modal" id="notificationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notifications</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if(empty($notifications)): ?>
                        <div class="text-center">No new notifications</div>
                    <?php else: ?>
                        <?php foreach($notifications as $notification): ?>
                        <div class="notification-item" data-id="<?php echo $notification['notificationID']; ?>">
                            <h6><?php echo htmlspecialchars($notification['sentBy']); ?></h6>
                            <p><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                            <small><?php echo date('M j, Y H:i', strtotime($notification['dateSent'])); ?></small>
                            
                            <form class="reply-form">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['notificationID']; ?>">
                                <textarea class="form-control mb-2" name="reply" placeholder="Type your reply..." required></textarea>
                                <button type="submit" class="btn btn-sm btn-primary">Send Reply</button>
                            </form>
                            
                            <button class="btn btn-sm btn-link toggle-reply">Reply</button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Hospital Management System &copy; 2024. All Rights Reserved.</p>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Update current date/time display
            function updateDateTime() {
                const now = new Date();
                $('#currentDateTime').text(now.toLocaleString());
            }
            setInterval(updateDateTime, 1000);

            // Toggle reply form visibility
            $('.notification-item').on('click', '.toggle-reply', function() {
                $(this).siblings('.reply-form').toggle();
            });

            // Handle reply submission via AJAX
            $('.reply-form').submit(function(e) {
                e.preventDefault();
                const form = $(this);
                const notificationItem = form.closest('.notification-item');
                
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: form.serialize() + '&reply=1',
                    success: function() {
                        notificationItem.fadeOut(300, function() {
                            $(this).remove();
                            updateNotificationCount();
                        });
                    }
                });
            });

            // Update notification badge count
            function updateNotificationCount() {
                $.get(window.location.href, function(data) {
                    const newCount = $(data).find('.notification-badge').text();
                    $('.notification-badge').text(newCount);
                    if(parseInt(newCount) === 0) {
                        $('.notification-badge').hide();
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
<!-- Inline CSS -->
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --background-color: rgb(95, 172, 250);
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    body, html {
        height: 100%;
        background-color: var(--background-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        display: flex;
        flex-direction: column;
    }
    /* Navbar */
    .navbar {
        background-color: rgb(187, 243, 201);
        box-shadow: var(--card-shadow);
        padding: 1rem 2rem;
    }
    .navbar-text {
        font-size: 1rem;
        color: var(--primary-color);
        font-weight: 500;
    }
    .logout-btn {
        background-color: var(--accent-color);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 5px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .logout-btn:hover {
        background-color: #c82333;
    }
    .status-btn {
        background-color: var(--secondary-color);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 5px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-left: 1rem;
    }
    .status-btn:hover {
        background-color: #2980b9;
    }
    /* Dashboard Modules */
    .dashboard-container {
        flex: 1;
        padding: 1rem;
    }
    .row > .col-md-4 {
        margin-bottom: 1.5rem;
    }
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: white;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    .icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--secondary-color);
    }
    .card-text {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 1.5rem;
    }
    .btn-primary {
        background-color: var(--secondary-color);
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 5px;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #2980b9;
    }
    /* Footer */
    .footer {
        background-color: var(--primary-color);
        color: white;
        padding: 1rem;
        text-align: center;
        font-size: 0.9rem;
    }
    #currentDateTime {
        font-size: 0.9rem;
        color: var(--primary-color);
        font-weight: 500;
    }
</style>
