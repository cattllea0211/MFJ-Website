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
    echo "Please log in to view your cart.";
    exit;
}

$user_id = $_SESSION['user_id'];

$cart_sql = "
    SELECT ci.product_id, p.model, p.price, ci.quantity, p.image_url 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.user_id = ?";

$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cart</title>
 
</head>
<body>
   <div class="cart-modal" id="cartModal">
    <div class="cart-modal-content">
        <span class="close-modal" onclick="closeCartModal()">&times;</span>
        <h2>Your Shopping Cart</h2>
        <?php if ($cart_result->num_rows > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    while ($item = $cart_result->fetch_assoc()): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="Product Image" 
                                     style="width: 60px; height: 60px; border-radius: 8px;">
                            </td>
                            <td><?= htmlspecialchars($item['model']) ?></td>
                            <td>₱<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input 
                                    type="number" 
                                    class="quantity-input"
                                    min="1" 
                                    value="<?= $item['quantity'] ?>" 
                                    data-product-id="<?= $item['product_id'] ?>" 
                                    onchange="updateQuantity(<?= $item['product_id'] ?>, this.value)">
                            </td>
                            <td>₱<?= number_format($item_total, 2) ?></td>
                            <td>
                                <button 
                                    class="remove-item-btn" 
                                    data-product-id="<?= $item['product_id'] ?>" 
                                    onclick="removeItem(<?= $item['product_id'] ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <p>Total: ₱<?= number_format($total, 2) ?></p>
                <button id="checkout-btn" class="save-order-btn" onclick="saveOrder()">Checkout</button>

            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</div>


   <script>
   
    function updateQuantity(productId, newQuantity) {
        console.log(`Update product ${productId} to quantity ${newQuantity}`);
      
    }

  
    function saveOrder() {
        console.log("Proceeding to checkout...");
        window.location.href = '/MFJ/checkout.php'; 
</script>

</body>
</html>
<?php
$conn->close();
?>
<style type="text/css">

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #27ae60;
            --light-bg: #f9f9f9;
            --hover-bg: #1e8449;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
        }

        .cart-icon {
            position: fixed;
            top: 170px;
            right: 60px;
            z-index: 1100;
            background-color: var(--secondary-color);
            padding: 10px;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .cart-icon:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .cart-icon i {
            color: white;
            font-size: 2rem;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
        }

        
        .add-to-cart-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s;
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .add-to-cart-btn:hover {
            background-color: var(--hover-bg);
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

       
        .cart-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1200;
            animation: fadeIn 0.3s ease-in-out;
        }

        .cart-modal-content {
            background: linear-gradient(to bottom, #ffffff, #f3f3f3);
            border-radius: 12px;
            padding: 30px;
            width: 40%;
            max-height: 85%;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.2s;
        }

        .close-modal:hover {
            color: #e74c3c;
            transform: rotate(90deg);
        }

     
        .cart-modal h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 24px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .cart-table th, .cart-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .cart-table th {
            background-color: var(--light-bg);
            font-weight: bold;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .cart-summary {
            margin-top: 30px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .save-order-btn {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s;
            margin-top: 10px;
        }

        .save-order-btn:hover {
            background-color: var(--hover-bg);
            transform: scale(1.05);
        }

</style>

</body>
</html>
