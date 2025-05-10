<?php
// update_service.php

// Show PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $service_type = trim($_POST['service_type']);
    $client_type = trim($_POST['client_type']);
    $number_of_units = isset($_POST['number_of_units']) ? (int)$_POST['number_of_units'] : 0;
    $price = trim($_POST['price']);
    $duration = trim($_POST['duration']);
    $scheduled_date = !empty($_POST['scheduled_date']) ? $_POST['scheduled_date'] : null;
    $time_finished = !empty($_POST['time_finished']) ? $_POST['time_finished'] : null;
    $status = trim($_POST['status']);
    $worker_count = isset($_POST['worker_count']) ? (int)$_POST['worker_count'] : 0;
    $client_name = trim($_POST['client_name']);
    $client_address = trim($_POST['client_address']);
    $client_contact = trim($_POST['client_contact']);
    $description = trim($_POST['description']);
    $evaluation_status = isset($_POST['evaluation_status']) ? trim($_POST['evaluation_status']) : 'For Evaluation'; // Default if missing

    // Automatically update status to 'Completed' if evaluation_status is 'Evaluated'
if (strtolower($evaluation_status) === 'evaluated') {
    $status = 'Completed';
}


    // Handle proof image upload
    $proof_image_path = null;
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $tmp_name = $_FILES['proof_image']['tmp_name'];
        $filename = basename($_FILES['proof_image']['name']);
        $target_file = $upload_dir . uniqid() . '_' . $filename;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $proof_image_path = $target_file;
        }
    }

    // Build the update query
  $query = "UPDATE services SET 
    service_type = ?, 
    price = ?, 
    duration = ?, 
    scheduled_date = ?, 
    time_finished = ?, 
    status = ?, 
    worker_count = ?, 
    client_name = ?, 
    client_address = ?, 
    client_contact = ?, 
    description = ?, 
    evaluation_status = ?, 
    client_type = ?, 
    number_of_units = ?";


    if ($proof_image_path) {
        $query .= ", proof_image = ?";
    }

    $query .= " WHERE id = ?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
if ($proof_image_path) {
    $stmt->bind_param(
        "sdssssissssssssi", 
        $service_type, 
        $price, 
        $duration, 
        $scheduled_date, 
        $time_finished, 
        $status, 
        $worker_count, 
        $client_name, 
        $client_address, 
        $client_contact, 
        $description,
        $evaluation_status,
        $client_type,
        $number_of_units,
        $proof_image_path,
        $id
    );
} else {
    $stmt->bind_param(
        "sdssssisssssssi", 
        $service_type, 
        $price, 
        $duration, 
        $scheduled_date, 
        $time_finished, 
        $status, 
        $worker_count, 
        $client_name, 
        $client_address, 
        $client_contact, 
        $description,
        $evaluation_status,
        $client_type,
        $number_of_units,
        $id
    );
}

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
