<?php        

$servername = "localhost";
$username = 'mfj_user';
$password = 'StrongPassword123!';
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add the new columns to the services table if they don't exist
$alter_table_queries = [
    "ALTER TABLE services ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE services ADD COLUMN IF NOT EXISTS time_finished TIME",
    "ALTER TABLE services ADD COLUMN IF NOT EXISTS proof_image VARCHAR(255)",
    "ALTER TABLE services ADD COLUMN IF NOT EXISTS scheduled_time TIME"
];

foreach ($alter_table_queries as $query) {
    if (!$conn->query($query)) {
        echo "Error updating database structure: " . $conn->error;
    }
}

$employee_sql = "SELECT id, name FROM employees"; 
$employee_result = $conn->query($employee_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $service_type = $_POST['service_type'];
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    $employee_id = $_POST['employee_id']; 
    $status = $_POST['status'];
    $worker_count = count($employee_id);
    $scheduled_date = $_POST['scheduled_date'];
    $scheduled_time = $_POST['scheduled_time'];
    $description = $_POST['description'];
    $time_finished = !empty($_POST['time_finished']) ? $_POST['time_finished'] : NULL;
    $client_name = $_POST['client_name'];
    $client_address = $_POST['client_address'];
    $client_contact = $_POST['client_contact'];
    $client_type = $_POST['client_type'];
    $company_name = !empty($_POST['company_name']) ? $_POST['company_name'] : NULL;
    $number_of_units = $_POST['number_of_units'];
    $created_at = date('Y-m-d H:i:s'); // Replaces NOW()

    // Handle file upload for proof image
    $proof_image = '';
    if(isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['proof_image']['name']);
        $target_path = $upload_dir . $file_name;
        if(move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_path)) {
            $proof_image = $target_path;
        }
    }

    $stmt = $conn->prepare("INSERT INTO services (
        client_name, client_address, client_contact, client_type, company_name,
        service_type, price, duration, created_at, scheduled_date, scheduled_time,
        description, time_finished, proof_image, status, worker_count, number_of_units
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssdisssssssis", 
        $client_name, $client_address, $client_contact, $client_type, $company_name,
        $service_type, $price, $duration, $created_at, $scheduled_date, $scheduled_time,
        $description, $time_finished, $proof_image, $status, $worker_count, $number_of_units
    );

    if ($stmt->execute()) {
        $service_id = $stmt->insert_id;

        foreach ($employee_id as $employee) {
            $stmt_employee = $conn->prepare("INSERT INTO service_employees (service_id, employee_id) VALUES (?, ?)");
            $stmt_employee->bind_param("ii", $service_id, $employee);
            $stmt_employee->execute();
            $stmt_employee->close();
        }
        echo "<div class='alert alert-success'>New service added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_service'])) {
    $service_id = (int)$_POST['delete_ids'][0];

    // Delete related image file if exists
    $img_query = "SELECT proof_image FROM services WHERE id = ?";
    $stmt = $conn->prepare($img_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->bind_result($img_path);
    $stmt->fetch();
    $stmt->close();
    
    if (!empty($img_path) && file_exists($img_path)) {
        unlink($img_path);
    }

    $stmt = $conn->prepare("DELETE FROM service_employees WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->close();

    header("Location: /MFJ/manage_services.php");
    exit;
}

if (isset($_POST['delete_ids'])) {
    foreach ($_POST['delete_ids'] as $id) {
        $service_id = (int)$id;

        // Delete related image file if exists
        $img_query = "SELECT proof_image FROM services WHERE id = ?";
        $stmt = $conn->prepare($img_query);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $stmt->bind_result($img_path);
        $stmt->fetch();
        $stmt->close();
        
        if (!empty($img_path) && file_exists($img_path)) {
            unlink($img_path);
        }

        $stmt = $conn->prepare("DELETE FROM service_employees WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $stmt->close();
    }

    exit;
}

if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=services.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Service Type', 'Price', 'Duration', 'Added Date', 'Scheduled Date', 'Scheduled Time', 'Description', 'Time Finished', 'Status', 'Employee Count')); 

    $sql = "SELECT service_type, price, duration, created_at, scheduled_date, scheduled_time, description, time_finished, status, worker_count FROM services"; 
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    fgetcsv($handle); // Skip header row

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $service_type = $data[0];
        $price = (float)$data[1];
        $duration = (int)$data[2];
        $scheduled_date = $data[3];
        $scheduled_time = isset($data[4]) ? $data[4] : NULL;
        $description = isset($data[5]) ? $data[5] : NULL;
        $time_finished = isset($data[6]) ? $data[6] : NULL;
        $status = isset($data[7]) ? $data[7] : 'Pending';
        $worker_count = isset($data[8]) ? (int)$data[8] : 1; 

        $stmt = $conn->prepare("INSERT INTO services (service_type, price, duration, created_at, scheduled_date, scheduled_time, description, time_finished, status, worker_count) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdissssi", $service_type, $price, $duration, $scheduled_date, $scheduled_time, $description, $time_finished, $status, $worker_count);

        $stmt->execute();
        $stmt->close();
    }

    fclose($handle);
    echo "<div class='alert alert-success'>Services imported successfully!</div>";
}

$sql = "
    SELECT s.id, s.service_type, s.price, s.duration, s.created_at, s.scheduled_date, s.scheduled_time, 
    s.description, s.time_finished, s.proof_image, s.status, s.worker_count, 
    GROUP_CONCAT(e.name SEPARATOR ', ') AS employees
    FROM services s
    LEFT JOIN service_employees se ON s.id = se.service_id
    LEFT JOIN employees e ON se.employee_id = e.id
    GROUP BY s.id
";

$result = $conn->query($sql);

if (!$result) {
    echo "Error executing query: " . $conn->error;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Service List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Same style as in original file */
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            display: flex;
            background-color: #f1f5f9;
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
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

        /* Main content area */
        .main-content {
            flex: 1;
            margin-left:270px;
            padding: 2rem;
            max-width: 2200px;
        }

        /* Header section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
        }

        .page-title-icon {
            margin-right: 12px;
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Form styling */
        .card {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.925rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.925rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        select[multiple].form-control {
            background-image: none;
            min-height: 120px;
        }

        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.925rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #0d9f6e;
        }

        .btn-icon {
            margin-right: 8px;
        }

        /* File input styling */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }

        .custom-file-upload {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            width: 100%;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            background-color: white;
            transition: all 0.2s ease;
        }

        .custom-file-upload:hover {
            background-color: #f8fafc;
            border-color: var(--primary-light);
        }

        .custom-file-upload i {
            margin-right: 8px;
            color: var(--primary);
        }

        .file-name {
            margin-left: 8px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Back button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-weight: 500;
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #f8fafc;
            border-color: var(--primary-light);
        }

        .back-btn-icon {
            margin-right: 8px;
        }

        /* Alert styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--radius);
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d1fae5;
            border-color: var(--success);
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            border-color: var(--danger);
            color: #b91c1c;
        }

        /* Hidden field */
        .hidden {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn {
                width: 100%;
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

    <div class="main-content">
        <button class="back-btn" onclick="window.history.back();">
            <i class="fas fa-arrow-left back-btn-icon"></i> Back
        </button>

        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tools page-title-icon"></i>
                Manage Services
            </h1>
            <div class="header-actions">
                <form action="/MFJ/manage_services.php" method="post" enctype="multipart/form-data" class="file-input-wrapper">
                    <button type="button" class="btn btn-secondary">
                        <i class="fas fa-file-import btn-icon"></i> Choose File
                    </button>
                    <input type="file" name="csv_file" accept=".csv" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload btn-icon"></i> Import
                    </button>
                </form>
                <a href="/MFJ/manage_services.php?export=1" class="btn btn-success">
                    <i class="fas fa-file-export btn-icon"></i> Export
                </a>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Add New Service</h2>
            <form id="addServiceForm" method="post" enctype="multipart/form-data">
                <!-- Service Details Section -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Service Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="service_type" class="form-label">Service Type</label>
                            <select name="service_type" class="form-control" required>
                                <option value="">Select Service Type</option>
                                <option value="Repair">Repair</option>
                                <option value="Installation">Installation</option>
                                <option value="Preventive Maintenance">Preventive Maintenance</option>
                                <option value="Cleaning">Cleaning</option>
                                <option value="Check-up & Service">Check-up & Service</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="client_type" class="form-label">Client Type</label>
                            <select name="client_type" id="client_type" class="form-control" required onchange="toggleCompanyField()">
                                <option value="">Select Type</option>
                                <option value="Household">Household</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>

                        <!-- Company Name field - initially hidden -->
                        <div class="form-group hidden" id="company_name_field">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" name="company_name" id="company_name" class="form-control">
                        </div>

                        <!-- Added Number of Units dropdown -->
                        <div class="form-group">
                            <label for="number_of_units" class="form-label">Number of Units</label>
                            <select name="number_of_units" class="form-control" required>
                                <option value="">Select Number of Units</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10+">10+</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <input type="number" name="price" class="form-control" required step="0.01">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="duration" class="form-label">Duration (hours)</label>
                            <input type="number" name="duration" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control" placeholder="Enter service description"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-calendar-alt"></i> Scheduling Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" name="scheduled_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="scheduled_time" class="form-label">Scheduled Time</label>
                            <input type="time" name="scheduled_time" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="time_finished" class="form-label">Time Finished</label>
                            <input type="time" name="time_finished" class="form-control">
                            <small class="text-muted">Leave blank for scheduled services</small>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Client Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-user"></i> Client Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="client_name" class="form-label">Client Name</label>
                            <input type="text" name="client_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="client_address" class="form-label">Client Address</label>
                            <input type="text" name="client_address" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="client_contact" class="form-label">Client Contact</label>
                            <input type="text" name="client_contact" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Assignment Section -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-users"></i> Worker Assignment</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="employee_id" class="form-label">Assigned Workers</label>
                         <select name="assigned_worker[]" id="assigned_worker" class="form-control" multiple>
  <?php
    $sql = "SELECT * FROM employees WHERE roles = 'Employee'";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
      echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
    }
  ?>
</select>

                            <small class="text-muted">Hold Ctrl/Cmd to select multiple workers</small>
                        </div>
                    </div>
                </div>

                <!-- Documentation Section -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-file-alt"></i> Documentation</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="proof_image" class="form-label">Proof/Picture</label>
                            <div class="custom-file-input">
                                <label for="proof_image" class="custom-file-upload">
                                    <i class="fas fa-camera"></i> Choose Image
                                    <span class="file-name" id="file-name">No file selected</span>
                                </label>
                                <input type="file" id="proof_image" name="proof_image" accept="image/*">
                            </div>
                            <small class="text-muted">Upload before/after pictures or documentation</small>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <button type="submit" name="add_service" class="btn btn-primary" style="padding: 0.875rem 1.75rem;">
                        <i class="fas fa-plus btn-icon"></i> Add Service
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Display selected file name
        document.getElementById('proof_image').addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });

        // Function to toggle company name field visibility
        function toggleCompanyField() {
            const clientType = document.getElementById('client_type').value;
            const companyField = document.getElementById('company_name_field');
            
            if (clientType === 'Company') {
                companyField.classList.remove('hidden');
            } else {
                companyField.classList.add('hidden');
                // Clear the input when hidden
                document.getElementById('company_name').value = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCompanyField();
        });
    </script>
</body>
</html>
