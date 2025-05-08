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

$sql = "SELECT o.id, o.total_price, o.payment_method, o.account_name, o.reference_number, o.receipt_image, o.created_at, o.status
        FROM orders o
        WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gcash_account_name = $_POST['gcash_account_name'];
    $reference_number = $_POST['reference_number'];
    
    
    $receipt_image = $_FILES['receipt_image']['name'];
    $receipt_temp = $_FILES['receipt_image']['tmp_name'];
    $receipt_directory = "uploads/receipts/";

   
    if ($receipt_image) {
        move_uploaded_file($receipt_temp, $receipt_directory . $receipt_image);
    }

   
    $sql = "INSERT INTO orders (user_id, total_price, payment_method, account_name, reference_number, receipt_image) 
            VALUES (?, ?, 'GCash', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $order['total_price'], $gcash_account_name, $reference_number, $receipt_image);
    $stmt->execute();
    
   
    $order_id = $stmt->insert_id;

   
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
    <title>GCash Payment</title>
    <link rel="stylesheet" href="gcash_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>
<body>
    <style type="text/css">
         body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .order-details {
            margin-bottom: 30px;
        }
        .order-details p {
            font-size: 16px;
            color: #555;
        }
        .highlight {
            font-weight: bold;
            color: #e74c3c;
        }
        .gcash-qr {
            text-align: center;
            margin-bottom: 20px;
        }
        .gcash-qr img {
            width: 400px;
            height: 400px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #007bff;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }

        .navbar {
            background-color: #5a8aa6; 
            padding: 40px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .navbar .logo img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            border-radius: 50%;
            object-fit: cover;
        }

        .navbar .menu {
            display: flex;
            gap: 20px;
        }

        .navbar .menu li {
            list-style: none;
        }

        .navbar .menu li a {
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .navbar .menu li a:hover {
            background-color: #49758c; 
            transform: scale(1.05);
        }
    </style>

<div class="navbar">
    <div class="logo">
        <img src="/MFJ/logo.png" alt="Company Logo">
        MFJ Airconditioning Supply & Services
    </div>

    <ul class="menu">
        <li><a href="/MFJ/client_dashboard.php">Home</a></li>
        <li><a href="/MFJ/update_profile.php">Update Profile</a></li>
        <li><a href="/MFJ/view_transaction.php">Appointments</a></li>
        <li><a href="/MFJ/orders.php">Orders</a></li>
        <li><a href="/MFJ/submit_feedback.php">Feedback</a></li>
        <li><a href="/MFJ/homepage.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h1>GCash Payment</h1>

    <div class="gcash-info">
        <h3>GCash Payment QR Code</h3>
        <img src="/MFJ/gcash_qrcode.png" alt="GCash QR Code" class="qr-code">
    </div>

    <div class="order-summary">
        <h3>Previous Order Details</h3>
        <?php if ($order): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Account Name</th>
                    <th>Reference Number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                    <td><?php echo $order['payment_method']; ?></td>
                    <td><?php echo htmlspecialchars($order['account_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['reference_number']); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="receipt">
            <p><strong>Receipt:</strong> <a href="uploads/receipts/<?php echo $order['receipt_image']; ?>" target="_blank">View Receipt</a></p>
        </div>
        <?php else: ?>
            <p>No previous order found.</p>
        <?php endif; ?>
    </div>

    <div class="payment-form">
        <h3>Payment Details</h3>
        <form action="gcash_payment.php" method="POST" enctype="multipart/form-data">
            <label for="gcash_account_name">GCash Account Name:</label>
            <input type="text" name="gcash_account_name" id="gcash_account_name" required><br><br>

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
