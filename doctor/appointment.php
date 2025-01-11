<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Scheduling</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <h4 class="navbar-text mx-auto" style="font-weight: bold; color: white;">
                Appointment Scheduling
            </h4>
        </div>
    </nav>

    <!-- Appointment Scheduling Form -->
    <div class="container mt-5">
        <h2>Schedule an Appointment</h2>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="patientName">Patient Name</label>
                <input type="text" class="form-control" id="patientName" name="patientName" required>
            </div>

            <div class="form-group">
                <label for="doctorID">Doctor</label>
                <select class="form-control" id="doctorID" name="doctorID" required>
                    <option value="">Select Doctor</option>
                    <option value="1">Dr. John Doe</option>
                    <option value="2">Dr. Jane Smith</option>
                    <!-- Add more doctor options as needed -->
                </select>
            </div>


            <div class="form-group">
                <label for="appointmentDate">Appointment Date</label>
                <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" required>
            </div>

            <div class="form-group">
                <label for="appointmentTime">Appointment Time</label>
                <input type="time" class="form-control" id="appointmentTime" name="appointmentTime" required>
            </div>

            <div class="form-group">
                <label for="reason">Reason for Appointment</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="contactNumber">Contact Number</label>
                <input type="text" class="form-control" id="contactNumber" name="contactNumber" required>
            </div>

            <!-- Buttons for submitting or going back -->
            <button type="submit" class="btn btn-primary">Schedule Appointment</button>
            <a href="javascript:history.back()" class="btn btn-secondary ml-3">Go Back</a>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2024 Hospital Management System</p>
    </footer>

    <!-- Optional JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
