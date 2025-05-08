<?php
session_start();


if (!isset($_SESSION['username']) || !isset($_SESSION['appointment_data'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'mfj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE username = :username ORDER BY id DESC LIMIT 1");
    $stmt->execute(['username' => $_SESSION['username']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .confirmation-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .confirmation-header {
            color: #007BFF;
            text-align: center;
            margin-bottom: 20px;
        }
        .appointment-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1 class="confirmation-header">Appointment Confirmed</h1>
        
        <div class="appointment-details">
            <h2>Appointment Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['full_name']); ?></p>
            <p><strong>Service Type:</strong> <?php echo htmlspecialchars($appointment['service_type']); ?></p>
            <p><strong>Appointment Time:</strong> <?php echo htmlspecialchars($appointment['appointment_time']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($appointment['address']); ?></p>
        </div>

        <a href="dashboard.php" class="btn">Return to Dashboard</a>
    </div>

    <?php
   
    unset($_SESSION['appointment_data']);
    ?>
</body>
</html>