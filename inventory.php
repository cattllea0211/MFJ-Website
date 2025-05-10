<?php   
  
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $servername = "localhost";
   $username = 'mfj_user';
$password = 'StrongPassword123!';
    $dbname = "mfjdb";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $products_per_page = 8;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start_from = ($page - 1) * $products_per_page;

    $search_query = isset($_GET['query']) ? $_GET['query'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 9999999;

    $sql_count = "SELECT COUNT(id) AS total_products FROM products WHERE model LIKE '%$search_query%' 
                  AND (status LIKE '%$status_filter%' OR '$status_filter' = '') 
                  AND (category LIKE '%$category_filter%' OR '$category_filter' = '') 
                  AND price BETWEEN $min_price AND $max_price";
    $result_count = $conn->query($sql_count);
    $row_count = $result_count->fetch_assoc();
    $total_products = $row_count['total_products'];

    $sql = "SELECT id, model, category, price, stock, rating, added_date, status, image_url, description 
            FROM products WHERE model LIKE '%$search_query%' 
            AND (status LIKE '%$status_filter%' OR '$status_filter' = '') 
            AND (category LIKE '%$category_filter%' OR '$category_filter' = '') 
            AND price BETWEEN $min_price AND $max_price 
            LIMIT $start_from, $products_per_page";
    $result = $conn->query($sql);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $rating = $_POST['rating'];
            $status = $_POST['status'];
            $description = $_POST['description'];
            
            $stmt = $conn->prepare("UPDATE products SET model = ?, category = ?, price = ?, stock = ?, rating = ?, status = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssddsssi", $name, $category, $price, $stock, $rating, $status, $description, $id);
            
            if ($stmt->execute()) {
                header("Location: manage_products.php?success=1");
                exit();
            } else {
                $error_message = "Error updating product: " . $stmt->error;
            }
            $stmt->close();
        }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #5D5FEF;
            --primary-light: #EAEAFF;
            --secondary-color: #64748B;
            --success-color: #22C55E;
            --info-color: #0EA5E9;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --light-color: #F9FAFB;
            --dark-color: #1E293B;
            --border-color: #E2E8F0;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --transition: all 0.25s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: #f0f2f5;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1700px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-left: 100px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
        }

        .add-new-btn {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-container {
            flex: 1;
            position: relative;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            min-width: 150px;
        }

        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        th {
            background-color: #f9fafb;
            padding: 12px 16px;
            text-align: left;
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-image {
            width: 40px;
            height: 40px;
            background-color: #f3f4f6;
            border-radius: 8px;
        }

        .product-info {
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: 500;
            color: #1a1a1a;
        }

        .product-sku {
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }

        .pagination-buttons {
            display: flex;
            gap: 5px;
        }

        .pagination-btn {
            padding: 6px 12px;
            border: 1px solid #e5e7eb;
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #4b5563;
            transition: all 0.2s;
        }

        .pagination-btn:hover {
            background-color: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        .pagination-btn.active, .pagination-btn.available {
            background-color: #4478bb;
            color: white;
            border-color: #4478bb;
        }

        .pagination-btn:disabled {
            background-color: #f3f4f6;
            color: #9ca3af;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }
        .pagination-btn:hover:not(:disabled) {
            background-color: #4478bb;
            color: white;
            border-color: #4478bb;
        }
        
        .pagination-btn:disabled:hover {
            transform: none;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .checkbox-cell {
            width: 40px;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .sidebar {
            width: 280px;
            background-color:#ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            z-index: 10;
            transition: all 0.2s ease;
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            padding: 0 24px 24px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
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
            color: gray;
            font-weight: 600;
        }

        .nav-item {
            margin: 4px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: gray;
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
      .container {
            margin-left: 290px;
            width: calc(100% - 220px);
            max-width: none;
            background: #f8f9fa;
            box-shadow: none;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
            margin-top: 0;
        }

        .back-btn {
            background-color: #4478bb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            transition: background-color 0.2s;
        }

        .back-btn:hover {
            background-color: #3867a7;
        }

        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-unavailable {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .welcome-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 18px;
            color: #374151;
        }

        .date-display {
            color: #6b7280;
            font-size: 14px;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #555;
        }

        .dropdown-menu {
            display: block;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 120px;
            z-index: 100;
            border-radius: 6px;
            padding: 5px 0;
        }

        .dropdown-menu.hidden {
            display: none;
        }

        .dropdown-menu a {
            display: block;
            padding: 8px 15px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }
        
        /* Modern Edit Button */
        .edit-btn {
            background-color: #f0f9ff;
            color: #3b82f6;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .edit-btn:hover {
            background-color: #dbeafe;
            color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }

        .edit-btn i {
            font-size: 12px;
        }

        /* Improved Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 0 auto;
            padding: 25px;
            border-radius: 12px;
            width: 95%;
            max-width: 550px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {transform: translateY(-20px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        .close {
            color: #a0aec0;
            font-size: 24px;
            font-weight: bold;
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: #4a5568;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #4a5568;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
            background-color: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: #e5e7eb;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .button-group button {
            flex: 1;
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

        <div class="header">
            <h1> <i class="bi bi-box"></i> Inventory</h1>
        </div>

        <form method="GET" class="filters">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>

            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="available" <?php if ($status_filter == 'available') echo 'selected'; ?>>Available</option>
                <option value="unavailable" <?php if ($status_filter == 'unavailable') echo 'selected'; ?>>Unavailable</option>
            </select>

            <select name="category" id="edit-category" required>
            <option value="Window Type">Choose Category</option>
            <option value="Window Type">Window Type</option>
            <option value="Split Type">Split Type</option>
            <option value="Central Type">Central Type</option>
            <option value="Ductless Mini-Split Type">Ductless Mini-Split Type</option>
            <option value="Portable Type">Portable Type</option>
        </select>

            <input type="number" name="min_price" class="filter-select" placeholder="Min Price" value="<?php echo $min_price; ?>">
            <input type="number" name="max_price" class="filter-select" placeholder="Max Price" value="<?php echo $max_price; ?>">

            <button type="submit" class="add-new-btn">Filter</button>
        </form>


        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox">
                        </th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox">
                                </td>
                                <td>
                                    <div class="product-cell">
                                        <div class="product-image">
                                            <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['model']; ?>" width="40" height="40">
                                        </div>
                                        <div class="product-info">
                                            <span class="product-name"><?php echo $row['model']; ?></span>
                                            <span class="product-sku">SKU-<?php echo $row['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $row['category']; ?></td>
                                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] == 'available' ? 'status-available' : 'status-unavailable'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['rating']; ?></td>
                
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No products found</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

  


        <div class="pagination">
            <div class="pagination-info">
                Showing <?php echo $start_from + 1; ?> to <?php echo min($start_from + $products_per_page, $total_products); ?> of <?php echo $total_products; ?> entries
            </div>
            <div class="pagination-buttons">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&query=<?php echo urlencode($search_query); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>" class="pagination-btn">Previous</a>
                <?php else: ?>
                    <button class="pagination-btn" disabled>Previous</button>
                <?php endif; ?>

                <?php
                $total_pages = ceil($total_products / $products_per_page);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>&query=<?php echo urlencode($search_query); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>" class="pagination-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&query=<?php echo urlencode($search_query); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>" class="pagination-btn">Next</a>
                <?php else: ?>
                    <button class="pagination-btn" disabled>Next</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Show modal function
        function openEditModal(button) {
            // Get product data from data attributes
            document.getElementById("edit-id").value = button.dataset.id;
            document.getElementById("edit-model").value = button.dataset.model;
            document.getElementById("edit-category").value = button.dataset.category;
            document.getElementById("edit-price").value = button.dataset.price;
            document.getElementById("edit-stock").value = button.dataset.stock;
            document.getElementById("edit-rating").value = button.dataset.rating;
            document.getElementById("edit-status").value = button.dataset.status;
            document.getElementById("edit-description").value = button.dataset.description;
            
            // Show the modal with animation
            const modal = document.getElementById("editModal");
            modal.style.display = "flex";
            
            // Add animation class
            modal.querySelector('.modal-content').classList.add('modalFadeIn');
        }

        // Close modal function
        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById("editModal");
            if (event.target == modal) {
                closeEditModal();
            }
        }

        // Form submission handler
        document.addEventListener('DOMContentLoaded', function() {
            const editForm = document.getElementById('editForm');
            
            editForm.addEventListener('submit', function(e) {
                // You can add validation here if needed
                
                // Submit the form normally since we're using PHP to handle the update
                return true;
            });
        });
    </script>
</body>
</html>
