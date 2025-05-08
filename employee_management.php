<?php  
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "mfj_db"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['import_csv'])) {
    try {
       
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
        $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
        $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : '';
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $emergency_contact_person = isset($_POST['emergency_contact_person']) ? trim($_POST['emergency_contact_person']) : '';
        $emergency_contact_number = isset($_POST['emergency_contact_number']) ? trim($_POST['emergency_contact_number']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : null;  
        $rate_per_day = isset($_POST['rate_per_day']) ? (float)$_POST['rate_per_day'] : 0.0;
        $pagibig_no = isset($_POST['pagibig_no']) ? trim($_POST['pagibig_no']) : '';
        $philhealth_no = isset($_POST['philhealth_no']) ? trim($_POST['philhealth_no']) : '';
        $sss_no = isset($_POST['sss_no']) ? trim($_POST['sss_no']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';

       
        $picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; 

            if (!in_array($_FILES['picture']['type'], $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
            }

            if ($_FILES['picture']['size'] > $max_size) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }

            $upload_dir = "uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $unique_filename;

            if (!move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                throw new Exception("Failed to upload the image.");
            }

            $picture = $target_file;
        }

       
        $sql = "INSERT INTO employees (name, phone, email, birthdate, age, 
                emergency_contact_person, emergency_contact_number, role, 
                rate_per_day, pagibig_no, philhealth_no, sss_no, address, picture) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssisssdsssss", 
            $name, $phone, $email, $birthdate, $age,
            $emergency_contact_person, $emergency_contact_number, $role,
            $rate_per_day, $pagibig_no, $philhealth_no, $sss_no, $address, $picture
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting employee data: " . $stmt->error);
        }

        $stmt->close();

        $message = "Employee data has been saved successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <title>Employee Management</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --secondary: #f1f5f9;
            --accent: #0ea5e9;
            --text: #1e293b;
            --text-light: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --white: #ffffff;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.2s ease;
            --radius: 0.75rem;
        }      

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            display: flex;
            color: var(--text-color);
            line-height: 1.6;
        }
.sidebar {
            width: 280px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            z-index: 10;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            padding: 0 24px 24px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-section {
            padding: 12px 24px 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            font-weight: 600;
        }

        .nav-item {
            margin: 4px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            font-weight: 500;
            border-radius: 0 8px 8px 0;
            margin-right: 8px;
        }

        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
            border-left: 3px solid var(--primary);
            font-weight: 600;
        }

        .nav-icon {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        /* Improved main content area */
        .container {
            width: calc(100% - 300px);
            margin-left: 320px;
            padding: 30px;
            background-color: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            border-radius: 50px;
        }

        .back-btn {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            width: 150px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #1e40af;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px 0;
            border-bottom: 2px solid #eaeaea;
        }

        .form-header h1 {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Card-based form layout */
        .form-card {
            background-color: #f9fafb;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 25px;
        }

        .card-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
            color: #3498db;
            font-size: 18px;
            font-weight: 600;
        }

        .card-header i {
            margin-right: 8px;
        }

        /* Grid layout for the form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* File input styling */
        .file-input-container {
            position: relative;
            width: 100%;
        }

        .file-input-label {
            display: block;
            padding: 12px;
            background-color: #f0f4f8;
            border: 1px dashed #cbd5e0;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background-color: #e2e8f0;
            border-color: #a0aec0;
        }

        .file-input-label i {
            display: block;
            font-size: 24px;
            margin-bottom: 8px;
            color: #3498db;
        }

        input[type="file"] {
            display: none;
        }

        /* Image preview */
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 6px;
            display: none;
        }

        /* Submit button */
        .submit-btn {
            grid-column: span 2;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            margin-top: 20px;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .submit-btn i {
            margin-right: 8px;
        }

        /* Messages */
        .message, .error {
            text-align: center;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .container {
                width: 100%;
                margin-left: 0;
                padding: 20px;
                border-radius: 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .submit-btn, .message, .error {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

       <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">M</div>
                    <span class="logo-text">MFJ Admin</span>
                </div>
            </div>
            <ul class="nav-menu">
                <li class="nav-section">Main</li>
                <li class="nav-item">
                    <a href="/MFJ/admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-section">Management</li>
                <li class="nav-item">
                    <a href="/MFJ/manage_products.php" class="nav-link">
                       <i class="fas fa-box nav-icon"></i>
                        <span class="nav-text">Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/MFJ/manage_services.php" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/MFJ/admin_calendar.php" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Calendar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/MFJ/manage_employee.php" class="nav-link">
                        <i class="fas fa-id-card nav-icon"></i>
                        <span class="nav-text">Employees</span>
                    </a>
                </li>
                
                <li class="nav-item" style="margin-top: auto;">
                    <a href="/MFJ/index.php?logout=true" class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

    <div class="container">
        <button class="back-btn" onclick="window.history.back();">
            <i class="fas fa-arrow-left"></i> Back
        </button>

        <div class="form-header">
            <h1><i class="bi bi-person-fill"></i> Employee Management Form</h1>
        </div>
        
        <?php if(!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="/MFJ/employee_management.php" method="POST" enctype="multipart/form-data">
            <!-- Personal Information -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required placeholder="Enter full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" required placeholder="Enter phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required placeholder="Enter email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Birthdate:</label>
                        <input type="date" id="birthdate" name="birthdate" required onchange="calculateAge()">
                    </div>
                    
                    <div class="form-group">
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="age" required readonly>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" required placeholder="Enter address"></textarea>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-phone-alt"></i> Emergency Contact
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="emergency_contact_person">Emergency Contact Person:</label>
                        <input type="text" id="emergency_contact_person" name="emergency_contact_person" required placeholder="Enter emergency contact person">
                    </div>
                    
                    <div class="form-group">
                        <label for="emergency_contact_number">Emergency Contact Number:</label>
                        <input type="text" id="emergency_contact_number" name="emergency_contact_number" required placeholder="Enter emergency contact number">
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-briefcase"></i> Employment Information
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="role">Position:</label>
                        <input type="text" id="role" name="role" required placeholder="Enter position">
                    </div>
                    
                    <div class="form-group">
                        <label for="rate_per_day">Rate per Day:</label>
                        <input type="number" id="rate_per_day" name="rate_per_day" required placeholder="Enter rate per day">
                    </div>
                </div>
            </div>

            <!-- Government IDs -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-id-card"></i> Government IDs
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pagibig_no">Pag-IBIG Number:</label>
                        <input type="text" id="pagibig_no" name="pagibig_no" required placeholder="Enter Pag-IBIG number">
                    </div>
                    
                    <div class="form-group">
                        <label for="philhealth_no">PhilHealth Number:</label>
                        <input type="text" id="philhealth_no" name="philhealth_no" required placeholder="Enter PhilHealth number">
                    </div>
                    
                    <div class="form-group">
                        <label for="sss_no">SSS Number:</label>
                        <input type="text" id="sss_no" name="sss_no" required placeholder="Enter SSS number">
                    </div>
                </div>
            </div>

            <!-- Profile Picture -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-camera"></i> Profile Picture
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <div class="file-input-container">
                            <label for="picture" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload profile picture</span>
                                <small>(JPG, PNG or GIF, Max 5MB)</small>
                            </label>
                            <input type="file" id="picture" name="picture" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <img id="imagePreview" class="image-preview" alt="Image preview">
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Submit Employee Information
            </button>
        </form>
    </div>

    <script>
        function calculateAge() {
            const birthdate = document.getElementById('birthdate').value;
            if (birthdate) {
                const birthDateObj = new Date(birthdate);
                const today = new Date();
                let age = today.getFullYear() - birthDateObj.getFullYear();
                const monthDifference = today.getMonth() - birthDateObj.getMonth();
                if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDateObj.getDate())) {
                    age--;
                }
                document.getElementById('age').value = age;
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>