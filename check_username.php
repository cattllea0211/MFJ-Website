<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    
   
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response = array();
    if ($result->num_rows > 0) {
        $response['available'] = false;
        $response['message'] = "Username already taken. Please choose another one.";
    } else {
        $response['available'] = true;
        $response['message'] = "Username is available!";
    }
    
    echo json_encode($response);
    $stmt->close();
}

$conn->close();
?>
