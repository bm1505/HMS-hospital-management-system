<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h1>Welcome, Admin!</h1>

        <div class="buttons">
            <button onclick="window.location.href='admin_dashboard.php'" class="btn">Admin Dashboard</button>
            <button onclick="window.location.href='../home.php'" class="btn">Home Page</button>
        </div>
    </div>

</body>
</html>
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f7f7f7;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 600px;
    margin: 100px auto;
    background-color: #fff;
    padding: 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
}

h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 40px;
}

/* Button container */
.buttons {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.btn {
    padding: 12px 20px;
    font-size: 16px;
    color: white;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 45%;
}

.btn:hover {
    background-color: #0056b3;
}

/* Responsive styles */
@media (max-width: 768px) {
    .buttons {
        flex-direction: column;
        gap: 15px;
    }

    .btn {
        width: 100%;
    }
}

</style>