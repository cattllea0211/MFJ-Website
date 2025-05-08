<?php

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'mfj_db';


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$employeeId = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;


$sql = "SELECT rate_per_day FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['rate_per_day' => $row['rate_per_day']]);
} else {
    echo json_encode(['rate_per_day' => 0]);
}

$stmt->close();
$conn->close();
?>
