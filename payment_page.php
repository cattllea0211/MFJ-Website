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
$conn->set_charset("utf8mb4"); 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


try {
   
    $user_stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
    if (!$user_stmt) {
        die("Error preparing user details query: " . $conn->error);
    }
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    $appt_stmt = $conn->prepare("
    SELECT 
        a.id AS appointment_id, 
        a.appointment_time, 
        a.service_fee AS price, 
        a.service_type AS service_name,
        a.gcash_reference_number,
        a.gcash_proof_of_payment
    FROM 
        appointments a
    WHERE 
        a.username = ? 
        AND a.status = 'pending'
    ORDER BY 
        a.appointment_time DESC
    LIMIT 1
");
    if (!$appt_stmt) {
        die("Error preparing appointment query: " . $conn->error);
    }
    $appt_stmt->bind_param("s", $_SESSION['username']);
    $appt_stmt->execute();
    $appt_result = $appt_stmt->get_result();
    $appointment = $appt_result->fetch_assoc();
    $appt_stmt->close();

  
    if (!$appointment) {
        header("Location: appointments.php");
        exit();
    }

   
    $appointment_date = date("Y-m-d", strtotime($appointment['appointment_time']));
    $appointment_time = date("H:i:s", strtotime($appointment['appointment_time']));

} catch (Exception $e) {
 
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while retrieving appointment details.");
}

$conn->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gcash_payment'])) {
    $gcash_reference_number = $_POST['referenceNumber'];
    $gcash_payment_proof = $_FILES['proofOfPayment']['name'];
    $gcash_payment_proof_tmp = $_FILES['proofOfPayment']['tmp_name'];
    $upload_dir = "uploads/"; 
    $gcash_payment_proof_path = $upload_dir . basename($gcash_payment_proof);

   
    if (move_uploaded_file($gcash_payment_proof_tmp, $gcash_payment_proof_path)) {
       
        $conn = new mysqli($servername, $username, $password, $dbname);
        $update_stmt = $conn->prepare("UPDATE appointments SET gcash_reference_number = ?, gcash_proof_of_payment = ? WHERE id = ?");
        if (!$update_stmt) {
            die("Error preparing update query: " . $conn->error);
        }
        $update_stmt->bind_param("ssi", $gcash_reference_number, $gcash_payment_proof_path, $appointment['appointment_id']);
        if ($update_stmt->execute()) {
            echo "Payment details updated successfully!";
        } else {
            echo "Error updating payment details.";
        }
        $update_stmt->close();
        $conn->close();
    } else {
        echo "Error uploading payment proof.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Payment</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f4f6f9;
            --text-color: #333;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #5a8aa6;
            line-height: 1.6;
            color: var(--text-color);
        }

        .payment-container {
            max-width: 900px;
            margin: 40px auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 100px;
        }

        .payment-header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .payment-header i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .appointment-details {
            padding: 25px;
            background-color: #f9f9f9;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .detail-row label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .detail-row .value {
            text-align: right;
        }

        .payment-methods {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .payment-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            gap: 10px;
        }

        .payment-btn-cod {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .payment-btn-gcash {
            background-color: #10c165;
            color: var(--white);
        }

        .payment-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--primary-color);
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn-accept {
            background-color: #28a745;
            color: var(--white);
        }

        .modal-btn-decline {
            background-color: #dc3545;
            color: var(--white);
        }

        .gcash-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            max-width: 750px;
        }

        .gcash-qr {
            max-width: 450px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Back Button Style */
        .back-button-container {
            margin: 20px 0;
            text-align: center;
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #f39c12;
            color: var(--white);
            font-size: 1rem;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 100px;
            margin-right: 1300px;
        }

        .back-btn:hover {
            background-color: #e67e22;
        }

        .navbar {
            background-color:white; 
            padding: 40px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--primary-color) ;
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
            color: var(--primary-color);
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
            <li><a href="/MFJ/homepage.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

            <!-- Back Button -->
<div class="back-button-container">
    <button class="back-btn" onclick="window.history.back();">Back</button>
</div>
    <div class="payment-container">



        <div class="payment-header">
            <i class="fas fa-credit-card"></i>
            <h1>Appointment Payment</h1>
        </div>

        <div class="appointment-details">
            <div class="detail-row">
                <label>Service</label>
                <div class="value"><?php echo htmlspecialchars($appointment['service_name']); ?></div>
            </div>
            <div class="detail-row">
                <label>Date</label>
                <div class="value"><?php echo htmlspecialchars($appointment_date); ?></div>
            </div>
            <div class="detail-row">
                <label>Time</label>
                <div class="value"><?php echo htmlspecialchars($appointment_time); ?></div>
            </div>
            <div class="detail-row">
                <label>Full Name</label>
                <div class="value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>
            <div class="detail-row">
                <label>Email</label>
                <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="detail-row">
                <label>Total Amount</label>
                <div class="value">₱<?php echo number_format($appointment['price'], 2, '.', ''); ?></div>
            </div>

            <div class="payment-methods">
                <button class="payment-btn payment-btn-cod" onclick="selectPayment('cod')">
                    <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                </button>
                <button class="payment-btn payment-btn-gcash" onclick="selectPayment('gcash')">
                    <i class="fab fa-creative-commons-nc"></i> GCash
                </button>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Payment Method</h2>
            </div>
            <p>Please confirm your payment method for the upcoming service.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-accept" onclick="acceptPayment()">Proceed</button>
                <button class="modal-btn modal-btn-decline" onclick="declinePayment()">Cancel</button>
            </div>
        </div>
    </div>

   
    <div id="codModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cash on Delivery Payment</h2>
            </div>
            <p>Your service will be paid in cash upon delivery.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-accept" onclick="printReceipt()">Print Receipt</button>
                <button class="modal-btn modal-btn-decline" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

  
    <div id="gcashModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>GCash Payment</h2>
            </div>
            <p>Total Amount: ₱<span id="amountToPay"></span></p>
            <img src="/MFJ/qrcode.png" alt="GCash QR Code" class="gcash-qr">
            <h3>Enter Reference Number:</h3>
            <form method="POST" enctype="multipart/form-data" class="gcash-form">

                <input type="text" name="referenceNumber" placeholder="GCash Reference Number" required>
                <h3>Proof of Payment:</h3>
                <input type="file" name="proofOfPayment" required>
                <div class="modal-buttons">
                    <button class="modal-btn modal-btn-accept" type="submit" name="gcash_payment">Submit Payment</button>
                    <button class="modal-btn modal-btn-decline" type="button" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

<script>

    window.onload = function() {
        document.getElementById('paymentModal').style.display = 'flex';
    };

   
    function acceptPayment() {
        document.getElementById('paymentModal').style.display = 'none';
    }

   
    function declinePayment() {
        window.location.href = "/MFJ/client_dashboard.php"; 
    }
    function selectPayment(paymentMethod) {
    if (paymentMethod === 'cod') {
        document.getElementById('codModal').style.display = 'flex';
    } else if (paymentMethod === 'gcash') {
      
        const amountToPay = '<?php echo number_format($appointment['price'], 2, '.', ''); ?>';
        document.getElementById('amountToPay').textContent = amountToPay;
        
    
        document.getElementById('gcashModal').style.display = 'flex';
    }
}


    function printReceipt() {
    const service = '<?php echo htmlspecialchars($appointment['service_name']); ?>';
    const appointmentDate = '<?php echo htmlspecialchars($appointment_date); ?>';
    const amountPaid = '<?php echo number_format($appointment['price'], 2, '.', ''); ?>';
    const paymentMethod = '<?php echo isset($gcash_reference_number) ? "GCash" : "Cash on Delivery"; ?>';
    
    const receiptContent = `
        <h1>Receipt</h1>
        <p><strong>Service:</strong> ${service}</p>
        <p><strong>Date:</strong> ${appointmentDate}</p>
        <p><strong>Amount Paid:</strong> ₱${amountPaid}</p>
        <p><strong>Payment Method:</strong> ${paymentMethod}</p>
    `;
    
    const printWindow = window.open('', '', 'height=400,width=600');
    printWindow.document.write(receiptContent);
    printWindow.document.close();
    printWindow.print();
}


    function submitGCashPayment() {
        const referenceNumber = document.getElementById('referenceNumber').value;
        const proofOfPayment = document.getElementById('proofOfPayment').files[0];
        if (referenceNumber && proofOfPayment) {
            // Handle GCash payment submission
            alert('Payment details submitted successfully!');
            closeModal();
        } else {
            alert('Please fill out all fields.');
        }
    }

    function closeModal() {
        document.getElementById('codModal').style.display = 'none';
        document.getElementById('gcashModal').style.display = 'none';
    }
</script>

</body>
</html>
