<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Service ID is required']);
    exit;
}

// Sanitize input
$service_id = (int)$_GET['id'];

// Prepare SQL query to get service details
$sql = "SELECT 
    s.id, 
    s.service_type, 
    s.price, 
    s.duration, 
    s.created_at, 
    s.scheduled_date, 
    s.time_finished,
    s.status, 
    s.worker_count, 
    s.client_name, 
    s.client_address, 
    s.client_contact,
    s.description,
    s.proof_image,
    s.client_type,
    s.company_name,
    s.number_of_units, -- ✅ ADD THIS LINE
    GROUP_CONCAT(DISTINCT e.name ORDER BY e.name SEPARATOR ', ') AS employees
FROM services s
LEFT JOIN service_employees se ON s.id = se.service_id
LEFT JOIN employees e ON se.employee_id = e.id
WHERE s.id = ?
GROUP BY 
    s.id, s.service_type, s.price, s.duration, s.created_at, s.scheduled_date, 
    s.status, s.worker_count, s.client_name, s.client_address, s.client_contact, 
    s.client_type, s.company_name, s.number_of_units -- ✅ ADD THIS TOO
";


$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Service not found']);
    exit;
}

$service = $result->fetch_assoc();

// Format the proof image path if it exists
if (!empty($service['proof_image'])) {
    // Ensure the path is properly formatted for the web
    if (!preg_match('/^(http|https):\/\//', $service['proof_image']) && !preg_match('/^\//', $service['proof_image'])) {
        $service['proof_image'] = '/' . $service['proof_image'];
    }
}

// Return service details as JSON
header('Content-Type: application/json');
echo json_encode($service);

// Close connection
$stmt->close();
$conn->close();
?>