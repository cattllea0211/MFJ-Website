<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apiKey'])) {
    $apiKey = mysqli_real_escape_string($conn, $_POST['apiKey']);
    
    // You can modify the table name and column based on your actual database structure
    $sql = "INSERT INTO api_keys (api_key) VALUES ('$apiKey')"; // Assuming you have a table 'api_keys'
    
    if ($conn->query($sql) === TRUE) {
        echo "API Key saved successfully!";
    } else {
        echo "Error saving API Key: " . $conn->error;
    }
    
    $conn->close();
} else {
    echo "No API Key provided.";
}
?>
