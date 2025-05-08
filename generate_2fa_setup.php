<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}


require_once 'vendor/autoload.php';  
use PHPGangsta_GoogleAuthenticator as GoogleAuthenticator;


$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'mfj_db';


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$userId = $_SESSION['user_id'];


$ga = new GoogleAuthenticator();


$secret = $ga->createSecret();

$username = 'user_' . $userId;  
$qrCodeUrl = $ga->getQRCodeGoogleUrl($username, $secret);


$sql = "UPDATE users SET 2fa_secret = ?, 2fa_enabled = 0 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $secret, $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
   
    echo json_encode(['qr_code_url' => $qrCodeUrl]);
} else {
   
    echo json_encode(['error' => 'Failed to save 2FA secret']);
}


$stmt->close();
$conn->close();
?>
