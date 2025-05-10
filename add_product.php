<?php    

$servername = "localhost";
$username = 'mfj_user';
$password = 'StrongPassword123!';

$dbname = "mfjdb"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $model = $_POST['model'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $status = $_POST['status'] ?? '';
    $description = $_POST['description'] ?? ''; 
    // Validate required fields
    if (empty($model) || empty($category) || empty($price) || empty($stock) || empty($rating) || empty($status) || empty($description)) {
        echo "All fields are required.";
        return;
    }

    $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = 'uploads/'; 
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $image_file = $upload_dir . basename($_FILES['image']['name']);
            
        
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_file)) {
                $image_url = $image_file; 
            } else {
                echo "Error uploading the image.";
            }
        }

    $sql = "INSERT INTO products (model, category, price, stock, rating, added_date, status, image_url, description) 
            VALUES ('$model', '$category', '$price', '$stock', '$rating', NOW(), '$status', '$image_url', '$description')";

    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


    if (isset($_POST['delete_ids'])) {
        $ids_to_delete = implode(",", $_POST['delete_ids']);
        $sql = "DELETE FROM products WHERE id IN ($ids_to_delete)";

        if ($conn->query($sql) === TRUE) {
            echo "Products deleted successfully!";
            header("Location: /MFJ/manage_products.php"); // Redirect after deletion
            exit;
        } else {
            echo "Error deleting products: " . $conn->error;
        }
    }


$sql = "SELECT id, model, category, price, stock, rating, added_date, status, image_url, description FROM products";
$result = $conn->query($sql);
   

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $model = $_POST['model'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $rating = $_POST['rating'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url']; // or handle a new file upload if required

    $sql = "UPDATE products SET model='$model', category='$category', price='$price', stock='$stock', 
            rating='$rating', status='$status', description='$description' WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully!";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}


    $sql = "SELECT id, model, category, price, stock, rating, added_date, status, description, image_url FROM products";
    $result = $conn->query($sql);


    $limit = 5; 
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
    $offset = ($current_page - 1) * $limit;


    $sql = "SELECT id, model, category, price, stock, rating, added_date, status, image_url, description FROM products LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);


    $total_products_sql = "SELECT COUNT(*) as total FROM products";
    $total_result = $conn->query($total_products_sql);
    $total_row = $total_result->fetch_assoc();
    $total_products = $total_row['total'];
    $total_pages = ceil($total_products / $limit); 

    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Admin Panel - Manage Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        }

        body {
            display: flex;
            background-color: #f8fafc;
            color: var(--text);
            min-height: 100vh;
        }

        /* Keep sidebar as is per request */
        .sidebar {
            width: 260px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            padding-top: 20px;
        }

        /* Sidebar - kept as requested */
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
        /* New main content styles */
        .container {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .back-btn {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background-color: #f1f5f9;
        }

        .back-btn i {
            margin-right: 0.5rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .page-title i {
            margin-right: 0.75rem;
            color: var(--primary);
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .form-grid-full {
            grid-column: span 3;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-input-wrapper {
            position: relative;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--text-light);
            transition: all 0.2s;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .file-input-label i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-icon {
            margin-right: 0.5rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar - kept as is per request -->
    <div class="sidebar">
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
        </aside>    </div>

    <!-- Main Content Area -->
    <div class="container">
        <!-- Header -->
        <div class="header">
            <button class="back-btn" onclick="window.history.back();">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <h1 class="page-title"><i class="fas fa-box-open"></i> Manage Products</h1>
        </div>

        <!-- Add Product Form Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Add New Product</h2>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="model">Product Name</label>
                            <input type="text" id="model" name="model" class="form-control" placeholder="Enter product name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="category">Category</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="Window Air Conditioner">Window Air Conditioner</option>
                                <option value="Split Air Conditioner">Split Air Conditioner</option>
                                <option value="Portable Air Conditioner">Portable Air Conditioner</option>
                                <option value="Central Air Conditioner">Central Air Conditioner</option>
                                <option value="Ductless Mini-Split Air Conditioner">Ductless Mini-Split Air Conditioner</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="price">Price</label>
                            <input type="number" id="price" name="price" class="form-control" placeholder="Enter price" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" class="form-control" placeholder="Enter stock quantity" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="rating">Rating</label>
                            <input type="number" id="rating" name="rating" class="form-control" placeholder="Enter rating (1-5)" min="1" max="5" step="0.1" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="available">Available</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label" for="description">Product Description</label>
                            <textarea id="description" name="description" class="form-control" placeholder="Enter product description" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label">Product Image</label>
                            <div class="file-input-wrapper">
                                <label class="file-input-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span id="file-name">Choose a file or drag it here</span>
                                </label>
                                <input type="file" id="image" name="image" class="file-input" accept="image/*" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_product" class="btn btn-primary">
                            <i class="fas fa-plus btn-icon"></i> Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show file name when selected
        document.getElementById('image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose a file or drag it here';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>
