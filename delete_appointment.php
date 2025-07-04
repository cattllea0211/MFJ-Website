<?php

$host = 'localhost';
$dbname = 'mfjdb';
$username = 'root';
$password = '';

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    if (isset($_POST['id'])) {
        $appointmentId = $_POST['id'];

     
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
        $stmt->bindParam(':id', $appointmentId, PDO::PARAM_INT);

        $stmt->execute();

       
        echo json_encode(['success' => 'Appointment deleted successfully']);
    } else {
        echo json_encode(['error' => 'No appointment ID provided']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
