<?php

session_start();


if (!isset($_SESSION['user_id'])) {
 
    echo json_encode(['error' => 'User not logged in']);
    exit();
}


$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'mfj_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$userId = $_SESSION['user_id'];


$sql = "SELECT 2fa_enabled FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare the SQL statement']);
    exit();
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($twofaEnabled);
    $stmt->fetch();
    
  
    echo json_encode(['enabled' => (bool)$twofaEnabled]);
} else {
    
    echo json_encode(['error' => 'User not found']);
}


$stmt->close();
$conn->close();
?>
