<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "mfj_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID parameter exists
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        
        // Return employee data as JSON
        header('Content-Type: application/json');
        echo json_encode($employee);
    } else {
        // No employee found with that ID
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Employee not found']);
    }
    
    $stmt->close();
} else {
    // No ID provided
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No employee ID provided']);
}

$conn->close();
?>