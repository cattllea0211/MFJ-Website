<?php

$host = 'localhost';
$dbname = 'mfjdb';
$username = 'root';
$password = '';

try {
 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    if (isset($_GET['id'])) {
        $appointmentId = $_GET['id'];

     
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id");
        $stmt->bindParam(':id', $appointmentId, PDO::PARAM_INT);
        $stmt->execute();
        
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        
        if ($appointment) {
            echo json_encode($appointment);
        } else {
            echo json_encode(['error' => 'Appointment not found']);
        }
    } else {
        echo json_encode(['error' => 'No appointment ID provided']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
