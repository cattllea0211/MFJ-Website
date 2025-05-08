<?php   
session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT ci.product_id, ci.quantity, p.model, p.price, (ci.quantity * p.price) as total 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['total'];
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_name = $_POST['account_name'];
    $reference_number = $_POST['reference_number'];
    
 
    $receipt_image = $_FILES['receipt_image']['name'];
    $receipt_temp = $_FILES['receipt_image']['tmp_name'];
    $receipt_directory = "uploads/receipts/";

   
    if ($receipt_image) {
        move_uploaded_file($receipt_temp, $receipt_directory . $receipt_image);
    }

   
    $sql = "INSERT INTO orders (user_id, total_price, payment_method, account_name, reference_number, receipt_image) 
            VALUES (?, ?, 'Bank Transfer', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $total_price, $account_name, $reference_number, $receipt_image);
    $stmt->execute();
    
    
    $order_id = $stmt->insert_id;

  
    foreach ($cart_items as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
        $stmt->execute();
    }

    
    $sql = "DELETE FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

   
    header("Location: client_dashboard.php");
    exit; 

    $stmt->close(); 
}

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Payment</title>
    <link rel="stylesheet" href="bank_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="/MFJ/logo.png" alt="Company Logo">
        MFJ Airconditioning Supply & Services
    </div>

    <ul class="menu">
        <li><a href="/MFJ/client_dashboard.php">Home</a></li>
        <li><a href="/MFJ/update_profile.php">Update Profile</a></li>
        <li><a href="/MFJ/view_transaction.php">Appointments</a></li>
        <li><a href="/MFJ/">Orders</a></li>
        <li><a href="/MFJ/submit_feedback.php">Feedback</a></li>
        <li><a href="/MFJ/homepage.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h1>Bank Transfer Payment</h1>

    <!-- Bank Account QR Code -->
    <div class="bank-info">
        <h3>Bank Account QR Code</h3>
        <img src="/MFJ/bpiqrcode.png" alt="Bank Account QR Code" class="qr-code">
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['model']); ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-price">
            <strong>Total Price: </strong><span class="highlight">₱<?php echo number_format($total_price, 2); ?></span>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="payment-form">
        <h3>Payment Details</h3>
        <form action="bank_payment.php" method="POST" enctype="multipart/form-data">
            <label for="account_name">Bank Account Name:</label>
            <input type="text" name="account_name" id="account_name" required><br><br>

            <label for="reference_number">Reference Number:</label>
            <input type="text" name="reference_number" id="reference_number" required><br><br>

            <label for="receipt_image">Upload Receipt Image:</label>
            <input type="file" name="receipt_image" id="receipt_image" accept="image/*" required><br><br>

            <button type="submit" class="checkout-btn">
                <i class="fas fa-credit-card"></i> Submit Payment
            </button>
        </form>
    </div>
</div>

</body>
</html>
