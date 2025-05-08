<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'mfjdb',
    'username' => 'root',
    'password' => ''
];

try {
   
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", 
        $dbConfig['username'], 
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id AND username = :username");
        $stmt->execute([
            'id' => $_GET['id'],
            'username' => $_SESSION['username']
        ]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            die("Appointment not found or access denied.");
        }
    } else {
        die("No appointment ID provided.");
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container bg-white p-4 mt-5 rounded shadow">
        <h1>Appointment Confirmation</h1>
        <p><strong>Client Name:</strong> <?php echo htmlspecialchars($appointment['full_name']); ?></p>
        <p><strong>Service Type:</strong> <?php echo htmlspecialchars($appointment['service_type']); ?></p>
        <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></p>
        <p><strong>Appointment Time:</strong> <?php echo htmlspecialchars($appointment['appointment_time']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($appointment['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['phone']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['email']); ?></p>
        <p><strong>AC Units:</strong> <?php echo htmlspecialchars($appointment['airconditioning_units']); ?></p>
    </div>
</body>
</html>
