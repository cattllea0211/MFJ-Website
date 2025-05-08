<?php

$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'mfj_db',
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

    
    if (isset($_POST['service_type'])) {
        $serviceType = filter_input(INPUT_POST, 'service_type', FILTER_SANITIZE_STRING);

        $stmt = $pdo->prepare("SELECT price FROM services WHERE service_type = :service_type LIMIT 1");
        $stmt->execute([':service_type' => $serviceType]);

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($service) {
            echo json_encode(['status' => 'success', 'price' => $service['price']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Service type not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
