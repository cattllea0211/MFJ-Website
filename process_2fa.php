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
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}


$userId = $_SESSION['user_id'];

$ga = new GoogleAuthenticator();


$action = $_POST['action'] ?? '';

switch ($action) {
    case 'enable_2fa':
       
        $verificationCode = $_POST['verification_code'] ?? '';

        if (empty($verificationCode)) {
            echo json_encode(['error' => 'Verification code is required']);
            exit();
        }

      
        $stmt = $conn->prepare("SELECT 2fa_secret FROM users WHERE id = ?");
        if ($stmt === false) {
            echo json_encode(['error' => 'Database query preparation failed']);
            exit();
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            echo json_encode(['error' => 'User not found']);
            exit();
        }

        
        $checkResult = $ga->verifyCode($user['2fa_secret'], $verificationCode, 2); 

        if ($checkResult) {
            // If the code is correct, enable 2FA for the user
            $stmt = $conn->prepare("UPDATE users SET 2fa_enabled = 1 WHERE id = ?");
            if ($stmt === false) {
                echo json_encode(['error' => 'Database query preparation failed']);
                exit();
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();

           
            echo json_encode(['success' => true, 'message' => '2FA Enabled Successfully']);
        } else {
           
            echo json_encode(['success' => false, 'message' => 'Invalid Verification Code']);
        }
        break;

    case 'disable_2fa':
        
        $stmt = $conn->prepare("UPDATE users SET 2fa_enabled = 0, 2fa_secret = NULL WHERE id = ?");
        if ($stmt === false) {
            echo json_encode(['error' => 'Database query preparation failed']);
            exit();
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();

      
        echo json_encode(['success' => true, 'message' => '2FA Disabled Successfully']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}


$stmt->close();
$conn->close();
?>
