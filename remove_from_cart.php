<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['remove_from_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
   
    $remove_sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
    $remove_stmt = $conn->prepare($remove_sql);
    $remove_stmt->bind_param("ii", $user_id, $product_id);
    $remove_stmt->execute();
    
    // Get updated cart count
    $count_sql = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total_items'] ?? 0;
    
  
    echo json_encode([
        'success' => true, 
        'cart_count' => $total_items
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>