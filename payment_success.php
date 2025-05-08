<?php  
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit;
}


$payment_success = isset($_SESSION['payment_success']) ? $_SESSION['payment_success'] : false;


unset($_SESSION['payment_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="payment_success.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>
<body>

    <style type="text/css">
       
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h1, p {
            color: #333;
        }

        .navbar {
            background-color: #5a8aa6; 
            padding: 40px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .navbar .logo img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            border-radius: 50%;
            object-fit: cover;
        }

        .navbar .menu {
            display: flex;
            gap: 15px;
        }

        .navbar .menu li {
            list-style: none;
        }

        .navbar .menu li a {
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .navbar .menu li a:hover {
            background-color: #49758c; 
        }

     
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .success-message {
            font-size: 18px;
            color: #333;
        }

        .success-icon, .error-icon {
            font-size: 100px;
            color: #2ecc71;
            margin-bottom: 20px;
        }

        .error-icon {
            color: #e74c3c; 
        }

        h1 {
            font-size: 28px;
            color: #2ecc71;
        }

        .back-btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

    </style>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="/MFJ/logo.png" alt="Company Logo">
        MFJ Airconditioning Supply & Services
    </div>
    <ul class="menu">
        <li><a href="/MFJ/client_dashboard.php">Home</a></li>
        <li><a href="/MFJ/view_transaction.php">Appointments</a></li>
        <li><a href="/MFJ/">Orders</a></li>
        <li><a href="/MFJ/homepage.php">Logout</a></li>
    </ul>
</div>


<div class="container">
    <div class="success-message">
        <?php if ($payment_success): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Payment Successful!</h1>
            <p>Your payment has been successfully processed. Thank you for your purchase!</p>
            <p>Your order is being processed, and we will notify you once it's ready for delivery.</p>
        <?php else: ?>
            <i class="fas fa-times-circle error-icon"></i>
            <h1>Payment Failed</h1>
            <p>Something went wrong with your payment. Please try again later or contact customer support.</p>
        <?php endif; ?>
        <a href="/MFJ/client_dashboard.php" class="back-btn">Go to Dashboard</a>
    </div>
</div>


<div class="footer">
    <p>&copy; 2024 MFJ Airconditioning Supply & Services</p>
</div>

</body>
</html>

