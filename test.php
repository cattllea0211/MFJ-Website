<?php
// Sample Data (In real case, fetch from the database)
$totalProducts = 50;      // Example data from your database
$totalServices = 25;      // Example data from your database
$pendingOrders = 10;      // Example data from your database
$feedbackCount = 8;       // Example data from your database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .dashboard {
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
            height: 100vh;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .main-content h1 {
            color: #333;
        }

        .main-content .cards {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 200px;
            text-align: center;
        }

        .card h3 {
            margin-bottom: 15px;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Manage Products</a></li>
                <li><a href="#">Manage Services</a></li>
                <li><a href="#">Manage Orders</a></li>
                <li><a href="#">Customer Feedback</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="#">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Welcome, Admin</h1>
            <p>Here is a summary of your website's performance:</p>
            
            <div class="cards">
                <div class="card">
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
                <div class="card">
                    <h3>Total Services</h3>
                    <p><?php echo $totalServices; ?></p>
                </div>
                <div class="card">
                    <h3>Pending Orders</h3>
                    <p><?php echo $pendingOrders; ?></p>
                </div>
                <div class="card">
                    <h3>Customer Feedback</h3>
                    <p><?php echo $feedbackCount; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
