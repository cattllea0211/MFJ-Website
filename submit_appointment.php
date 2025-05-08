<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'mfj_db',
    'username' => 'root',
    'password' => ''
];

try {
    // Establish database connection
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Process the POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $clientType = filter_input(INPUT_POST, 'client_type', FILTER_SANITIZE_STRING);
        $serviceType = filter_input(INPUT_POST, 'service_type', FILTER_SANITIZE_STRING);
        $appointmentTime = filter_input(INPUT_POST, 'appointment_time', FILTER_SANITIZE_STRING);
        $houseNumber = filter_input(INPUT_POST, 'house_number', FILTER_SANITIZE_STRING);
        $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        $subdivision = filter_input(INPUT_POST, 'subdivision', FILTER_SANITIZE_STRING);
        $barangay = filter_input(INPUT_POST, 'barangay', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $nearestLandmark = filter_input(INPUT_POST, 'nearest_landmark', FILTER_SANITIZE_STRING);
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        // Fetch service fee based on service type
        $stmt = $pdo->prepare("SELECT price FROM services WHERE service_type = :service_type LIMIT 1");
        $stmt->execute([':service_type' => $serviceType]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        $serviceFee = $service ? $service['price'] : 0;

        // Compile full address
        $fullAddress = implode(', ', [
            $houseNumber,
            $street,
            $subdivision,
            $barangay,
            $city,
            "Nearest landmark: {$nearestLandmark}"
        ]);

        // Insert appointment data into the database
        $sql = "INSERT INTO appointments (
            full_name, client_type, email, phone, appointment_time, 
            service_type, house_number, street, subdivision, barangay, 
            city, nearest_landmark, address, username, product_id, service_fee
        ) VALUES (
            :full_name, :client_type, :email, :phone, :appointment_time, 
            :service_type, :house_number, :street, :subdivision, :barangay, 
            :city, :nearest_landmark, :full_address, :username, :product_id, :service_fee
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $fullName,
            ':client_type' => $clientType,
            ':email' => $email,
            ':phone' => $phone,
            ':appointment_time' => $appointmentTime,
            ':service_type' => $serviceType,
            ':house_number' => $houseNumber,
            ':street' => $street,
            ':subdivision' => $subdivision,
            ':barangay' => $barangay,
            ':city' => $city,
            ':nearest_landmark' => $nearestLandmark,
            ':full_address' => $fullAddress,
            ':username' => $_SESSION['username'],
            ':product_id' => $productId,
            ':service_fee' => $serviceFee
        ]);

        // Send success response
        echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit();
    }
} catch (PDOException $e) {
    // Handle database errors
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to book the appointment. Please try again later.']);
    exit();
}
?>