<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db"; 


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "DELETE FROM users WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: manage_users.php"); 
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
?>
