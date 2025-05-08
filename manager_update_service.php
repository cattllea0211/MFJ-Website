<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $scheduled_date = $_POST['scheduled_date'];
    $scheduled_time = $_POST['scheduled_time'];
    $client_name = $_POST['client_name'];
    $company_name = $_POST['company_name'];
    $client_type = $_POST['client_type'];
    $client_address = $_POST['client_address'];
    $client_contact = $_POST['client_contact'];
    $number_of_units = $_POST['number_of_units'];
    $evaluation_status = $_POST['evaluation_status'];

    $stmt = $conn->prepare("UPDATE services SET 
        description = ?, 
        duration = ?, 
        price = ?, 
        status = ?, 
        scheduled_date = ?, 
        scheduled_time = ?, 
        client_name = ?, 
        company_name = ?, 
        client_type = ?, 
        client_address = ?, 
        client_contact = ?, 
        number_of_units = ?, 
        evaluation_status = ? 
        WHERE id = ?");

    $stmt->bind_param(
        "sssssssssssisi", 
        $description, 
        $duration, 
        $price, 
        $status, 
        $scheduled_date, 
        $scheduled_time, 
        $client_name, 
        $company_name, 
        $client_type, 
        $client_address, 
        $client_contact, 
        $number_of_units, 
        $evaluation_status,
        $id
    );

    if ($stmt->execute()) {
        header("Location: manager_dashboard.php");
        exit();
    } else {
        echo "Error updating service: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
