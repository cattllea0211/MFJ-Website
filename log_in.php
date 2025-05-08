<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'mfj_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, password FROM employees WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $stored_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $stored_password)) {
        $_SESSION['user_id'] = $user_id; 
        header('Location: attendance_history.php'); 
        exit();
    } else {
        echo "Invalid credentials";
    }

    $stmt->close();
    $conn->close();
}
?>
