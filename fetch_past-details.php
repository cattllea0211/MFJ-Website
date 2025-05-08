<?php

$servername = "localhost"; 
$username = "root"; 
$password = "";
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

session_start();
$email = $_SESSION['email'] ?? ''; 

if ($email) {
   
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      
        $sql_units = "SELECT * FROM units WHERE user_id = ?";
        $stmt_units = $pdo->prepare($sql_units);
        $stmt_units->execute([$user['id']]);
        $units = $stmt_units->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'name' => $user['name'],
            'contact_number' => $user['contact_number'],
            'email' => $user['email'],
            'house_number' => $user['house_number'],
            'street' => $user['street'],
            'subdivision' => $user['subdivision'],
            'barangay' => $user['barangay'],
            'city' => $user['city'],
            'nearest_landmark' => $user['nearest_landmark'],
            'service_type' => $user['service_type'],
            'appointment_date' => $user['appointment_date'],
            'appointment_time' => $user['appointment_time'],
            'units' => $units
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No user logged in.']);
}
?>
