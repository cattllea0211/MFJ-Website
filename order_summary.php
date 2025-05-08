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

$stmt->close();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    
   
    $sql = "INSERT INTO orders (user_id, total_price, payment_method, created_at, status) 
            VALUES (?, ?, ?, NOW(), 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $total_price, $payment_method);
    
    if ($stmt->execute()) {
       
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

       
        header("Location: /MFJ/order_confirmation.php"); 
    } else {
        echo "Error saving the order: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link rel="stylesheet" href="order_summary.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="/MFJ/logo.png" alt="Company Logo">
        MFJ Airconditioning Supply & Services
    </div>

    <ul class="menu">
            <li><a href="/MFJ/client_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="/MFJ/update_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
            <li><a href="/MFJ/view_transaction.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
            <li><a href="/MFJ/client_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/MFJ/submit_feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
           <li><a href="/MFJ/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

        </ul>
</div>

<div class="container">
    <h1 class="page-title">
        <i class="fas fa-shopping-cart"></i> Order Summary
    </h1>
    <?php if (!empty($cart_items)): ?>
        <table class="summary-table">
            <thead>
                <tr>
                    <th><i class="fas fa-box"></i> Product</th>
                    <th><i class="fas fa-tags"></i> Price</th>
                    <th><i class="fas fa-sort-numeric-up"></i> Quantity</th>
                    <th><i class="fas fa-calculator"></i> Total</th>
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
            <strong><i class="fas fa-coins"></i> Total Price:</strong> ₱<?php echo number_format($total_price, 2); ?>
        </div>
        
        <!-- Payment method selection -->
        <div class="payment-methods">
            <h3>Select Payment Method:</h3>
            <form id="paymentForm" method="POST">
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="bank_transfer" id="bank_transfer" required>
                    <label for="bank_transfer">
                        <i class="fas fa-university"></i> Bank Transfer
                    </label>
                </div>
               
                <p class="note"><strong>Note:</strong> COD is not available for orders with high prices.</p>
                <button type="button" class="checkout-btn" onclick="redirectToPayment()">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </button>
            </form>
        </div>

        <div class="actions">
            <button onclick="window.location.href='view_products.php'" class="continue-btn">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </button>
        </div>
    <?php else: ?>
        <p>
            <i class="fas fa-exclamation-circle"></i> Your cart is empty. 
            <a href="view_products.php"><i class="fas fa-arrow-left"></i> Go back to shop</a> 
        </p>
    <?php endif; ?>
</div>

<script>
function redirectToPayment() {
    var paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    if (paymentMethod === 'bank_transfer') {
        window.location.href = '/MFJ/bank_payment.php';
    } else if (paymentMethod === 'gcash') {
        window.location.href = '/MFJ/gcash_payment.php';
    }
}
</script>

</body>
</html>
