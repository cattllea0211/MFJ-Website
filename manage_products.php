<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$limit = 8; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($page - 1) * $limit; 

$totalProductsResult = $conn->query("SELECT COUNT(*) as total FROM products");
$totalProductsRow = $totalProductsResult->fetch_assoc();
$totalProducts = $totalProductsRow['total'];
$totalPages = ceil($totalProducts / $limit); 

$sql = "SELECT id, model, category, price, stock, rating, added_date, status, image_url, description FROM products LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Product List</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    
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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles remain untouched */
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

        /* Improved main content styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            max-width: 1600px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 12px;
            color: var(--primary);
            font-size: 24px;
        }

        .date {
            color: var(--text-light);
            font-size: 15px;
            font-weight: 500;
            background-color: var(--secondary);
            padding: 8px 16px;
            border-radius: var(--radius);
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 16px;
            align-items: center;
        }

        .search-box {
            flex-grow: 1;
            position: relative;
            max-width: 500px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 45px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            font-size: 15px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            background-color: var(--white);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 18px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: var(--radius);
            border: none;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            min-width: 130px;
            box-shadow: var(--shadow-sm);
        }

        .btn i {
            margin-right: 10px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Improved product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: calc(var(--animation-order) * 0.1s);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 450px;
        }

        .product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .product-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .product-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: linear-gradient(to top, rgba(255,255,255,0.7), transparent);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .product-content {
            padding: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 50px;
        }

        .product-info {
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            align-items: center;
        }

        .info-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--text);
        }

        .product-description {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 24px;
            height: 68px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            background: linear-gradient(to bottom, var(--text-light) 80%, rgba(255,255,255,0));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-fill-color: transparent;
        }

        .product-actions {
            display: flex;
            gap: 12px;
            margin-top: auto;
        }

        .product-actions .btn {
            flex: 1;
            padding: 10px;
            min-width: 0;
        }

        /* Improved pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 40px 0;
            gap: 8px;
        }

        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--white);
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
            font-size: 15px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .pagination a:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Improved status badges */
        .status-badge {
            display: inline-flex;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            align-items: center;
        }

        .status-badge::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-available {
            background-color: rgba(16, 185, 129, 0.12);
            color: var(--success);
        }

        .status-available::before {
            background-color: var(--success);
        }

        .status-low {
            background-color: rgba(245, 158, 11, 0.12);
            color: var(--warning);
        }

        .status-low::before {
            background-color: var(--warning);
        }

        .status-out {
            background-color: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }

        .status-out::before {
            background-color: var(--danger);
        }

        /* Improved rating */
        .rating {
            color: #facc15;
            font-size: 16px;
            letter-spacing: 1px;
        }

        /* Empty state */
        .no-results {
            text-align: center;
            padding: 60px 0;
            color: var(--text-light);
            font-size: 18px;
            font-weight: 500;
            grid-column: 1 / -1;
        }

        .no-results::before {
            content: '🔍';
            display: block;
            font-size: 40px;
            margin-bottom: 20px;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive improvements */
        @media screen and (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media screen and (max-width: 992px) {
            .main-content {
                padding: 20px;
            }
            
            .btn-group {
                flex-wrap: wrap;
            }
            
            .btn {
                min-width: 120px;
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header {
                padding: 0 10px 20px;
            }
            
            .logo-text {
                display: none;
            }
            
            .nav-section, .nav-link span {
                display: none;
            }
            
            .nav-icon {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: none;
            }
            
            .btn-group {
                justify-content: space-between;
                width: 100%;
            }
            
            .btn {
                flex: 1;
                min-width: 0;
            }
        }

        @media screen and (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .product-card {
                height: auto;
            }
            
            .product-description {
                height: auto;
                -webkit-line-clamp: 2;
            }
            
            .pagination a {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
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
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-box"></i> Products
                </h1>
                <div class="date" id="current-date"></div>
            </div>

            <div class="actions-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search products..." onkeyup="searchProducts()">
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="window.location.href='/MFJ/inventory.php'">
                        <i class="fas fa-warehouse"></i> Inventory
                    </button>
                    <button class="btn btn-primary" onclick="window.location.href='/MFJ/add_product.php'">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </button>
                </div>
            </div>

            <div class="product-grid">
                <?php
                if ($result->num_rows > 0) {
                    $i = 0;
                    while ($product = $result->fetch_assoc()) {
                        $i++;
                        
                        // Determine status class and text
                        $statusClass = 'status-available';
                        $statusText = 'In Stock';
                        if ($product['stock'] == 0) {
                            $statusClass = 'status-out';
                            $statusText = 'Out of Stock';
                        } elseif ($product['stock'] < 5) {
                            $statusClass = 'status-low';
                            $statusText = 'Low Stock';
                        }
                        
                        echo "<div class='product-card' style='--animation-order: $i' data-id='{$product['id']}'>";
                        echo "<div class='product-image'>";
                        echo "<img src='{$product['image_url']}' alt='{$product['model']}'>";
                        echo "</div>";
                        echo "<div class='product-content'>";
                        echo "<h3 class='product-title'>{$product['model']}</h3>";
                        echo "<div class='product-info'>";
                        echo "<div class='info-row'>";
                        echo "<span class='info-label'>Category:</span>";
                        echo "<span class='info-value'>{$product['category']}</span>";
                        echo "</div>";
                        echo "<div class='info-row'>";
                        echo "<span class='info-label'>Price:</span>";
                        echo "<span class='info-value'>₱{$product['price']}</span>";
                        echo "</div>";
                        echo "<div class='info-row'>";
                        echo "<span class='info-label'>Stock:</span>";
                        echo "<span class='info-value'>{$product['stock']}</span>";
                        echo "</div>";
                        echo "<div class='info-row'>";
                        echo "<span class='info-label'>Rating:</span>";
                        echo "<span class='info-value rating'>";
                        $rating = $product['rating'];
                        for ($j = 1; $j <= 5; $j++) {
                            if ($j <= $rating) {
                                echo "<i class='fas fa-star'></i>";
                            } elseif ($j - 0.5 <= $rating) {
                                echo "<i class='fas fa-star-half-alt'></i>";
                            } else {
                                echo "<i class='far fa-star'></i>";
                            }
                        }
                        echo "</span>";
                        echo "</div>";
                        echo "<div class='info-row'>";
                        echo "<span class='info-label'>Status:</span>";
                        echo "<span class='status-badge $statusClass'>$statusText</span>";
                        echo "</div>";
                        echo "</div>";
                        echo "<p class='product-description'>{$product['description']}</p>";
                        echo "<div class='product-actions'>";
                        echo "<a href='/MFJ/add_product.php?id={$product['id']}' class='btn btn-secondary'><i class='fas fa-edit'></i> Edit</a>";
                        echo "<button class='btn btn-danger' onclick='deleteProduct({$product['id']})'><i class='fas fa-trash-alt'></i> Delete</button>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='no-results'>No products found</div>";
                }
                ?>
            </div>

            <div class="pagination">
                <?php
                // Previous button
                if ($page > 1) {
                    echo "<a href='?page=" . ($page - 1) . "' title='Previous'><i class='fas fa-chevron-left'></i></a>";
                }
                
                // Page numbers
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo "<a href='?page=$i' class='$activeClass'>$i</a>";
                }

                // Next button
                if ($page < $totalPages) {
                    echo "<a href='?page=" . ($page + 1) . "' title='Next'><i class='fas fa-chevron-right'></i></a>";
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        function searchProducts() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const productCards = document.querySelectorAll(".product-card");

            productCards.forEach(card => {
                const title = card.querySelector(".product-title").textContent.toLowerCase();
                const category = card.querySelector(".product-info").textContent.toLowerCase();
                const description = card.querySelector(".product-description").textContent.toLowerCase();
                
                if (title.includes(filter) || category.includes(filter) || description.includes(filter)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function deleteProduct(productId) {
            if (confirm("Are you sure you want to delete this product?")) {
                fetch(`/MFJ/delete_product.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const productCard = document.querySelector(`.product-card[data-id='${productId}']`);
                            if (productCard) {
                                productCard.style.opacity = "0";
                                setTimeout(() => {
                                    productCard.remove();
                                }, 500);
                            }
                        } else {
                            alert("Error deleting product: " + data.error);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("An error occurred while deleting the product.");
                    });
            }
        }

        // Format the current date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const today = new Date().toLocaleDateString('en-US', options);
        document.getElementById('current-date').textContent = today;
    </script>
</body>
</html>

<?php
$conn->close();
?>
