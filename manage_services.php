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

// Handle single service deletion with delete_service button
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_service'])) {
    if (isset($_POST['delete_ids']) && !empty($_POST['delete_ids'])) {
        $success = true;
        
        foreach ($_POST['delete_ids'] as $id) {
            $service_id = (int)$id;
            
            // First delete from service_employees table
            $stmt = $conn->prepare("DELETE FROM service_employees WHERE service_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $service_id);
                $result = $stmt->execute();
                if (!$result) {
                    $success = false;
                    echo "Error deleting from service_employees: " . $conn->error;
                }
                $stmt->close();
            } else {
                $success = false;
                echo "Error preparing statement for service_employees: " . $conn->error;
            }
            
            // Then delete from services table
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $service_id);
                $result = $stmt->execute();
                if (!$result) {
                    $success = false;
                    echo "Error deleting from services: " . $conn->error;
                }
                $stmt->close();
            } else {
                $success = false;
                echo "Error preparing statement for services: " . $conn->error;
            }
        }
        
        // Redirect back to the page after deletion
        header("Location: /manage_services.php" . ($success ? "?delete=success" : "?delete=error"));
        exit;
    } else {
        // No IDs provided
        header("Location: /manage_services.php?delete=error&message=no_ids");
        exit;
    }
}

// Pagination settings
$records_per_page = 10; // Number of records to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page, default is 1
$offset = ($page - 1) * $records_per_page; // Calculate offset for SQL query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base SQL query
$base_sql = "SELECT 
    s.id, 
    s.service_type, 
    s.price, 
    s.duration, 
    s.created_at, 
    s.scheduled_date, 
    s.time_finished,
    s.status, 
    s.worker_count, 
    s.client_name, 
    s.client_address, 
    s.client_contact,
    s.description,
    s.proof_image,
    s.evaluation_status,
    s.client_type,
    s.company_name,
    GROUP_CONCAT(DISTINCT e.name ORDER BY e.name SEPARATOR ', ') AS employees
FROM services s
LEFT JOIN service_employees se ON s.id = se.service_id
LEFT JOIN employees e ON se.employee_id = e.id";

// Where clause for search
$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = " WHERE s.service_type LIKE ? OR s.status LIKE ? ";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
    $types = "ss";
}

// Group by clause
$group_by = " GROUP BY s.id, s.service_type, s.price, s.duration, s.created_at, 
    s.scheduled_date, s.status, s.worker_count, s.client_name, s.client_address, 
    s.client_contact";

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT s.id
    FROM services s
    LEFT JOIN service_employees se ON s.id = se.service_id
    LEFT JOIN employees e ON se.employee_id = e.id";

if (!empty($where_clause)) {
    $count_sql .= $where_clause;
}

$count_sql .= $group_by . ") AS count_table";

$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    die("Prepare failed for count query: (" . $conn->errno . ") " . $conn->error);
}

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Main query with pagination
$final_sql = $base_sql . $where_clause . $group_by . " LIMIT ? OFFSET ?";

// Add pagination parameters
$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii"; // Add integer types for LIMIT and OFFSET parameters

$stmt = $conn->prepare($final_sql);
if (!$stmt) {
    die("Prepare failed for main query: (" . $conn->errno . ") " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Service Management | MFJ Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <style>
        /* Your existing styles remain the same */
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

        /* Main Content Area - Improved */
        .main-content {
            flex: 1;
            margin-left: 270px;
            padding: 30px;
            background-color: #f8fafc;
            transition: all 0.3s;
        }

        /* Top Navigation and Actions Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: var(--shadow);
        }

        .back-btn:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
        }

        /* Page Header */
        .page-header {
            background-color: white;
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .page-title .icon {
            background-color: var(--primary-light);
            color: white;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            font-size: 20px;
        }

        /* Search and Actions */
        .search-actions {
            display: flex;
            gap: 12px;
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-input {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 280px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn i {
    margin-right: 6px;
}

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Table Container */
        .table-container {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 16px;
            font-size: 14px;
        }

        table td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table tr:hover {
            background-color: #f1f5f9;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-available {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-unavailable {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Worker Count Badge */
        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            font-size: 12px;
            font-weight: 500;
        }

        /* Employee Badge */
        .employee-badge {
            background-color: #f1f5f9;
            padding: 4px 10px;
            border-radius: 9999px;
            display: inline-block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text);
            margin-right: 4px;
        }

.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
}
.modal-content {
    background-color: white;
    border-radius: 16px;
    width: 900px;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 20px 48px -10px rgba(0, 0, 0, 0.15);
    animation: modalFadeIn 0.3s ease-out;
    border: 1px solid var(--border);
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.modal-content::-webkit-scrollbar {
    width: 8px;
}
.modal-content::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 0 16px 16px 0;
}
.modal-content::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
    border: 2px solid #f8fafc;
}
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f8fafc;
    border-radius: 16px 16px 0 0;
    position: sticky;
    top: 0;
    z-index: 10;
}


.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}
.modal-header h3 i {
    color: var(--primary);
    font-size: 16px;
}

.modal-header h3::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 18px;
    background-color: var(--primary);
    border-radius: 4px;
}

.modal-close {
    font-size: 22px;
    color: var(--text-light);
    cursor: pointer;
    height: 32px;
    width: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}


.modal-close:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--danger);
}

.modal-body {
    padding: 0;
}

.modal-section {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}
.modal-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title::before {
    content: "";
    display: inline-block;
    width: 3px;
    height: 14px;
    background-color: var(--primary);
    border-radius: 3px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-label i {
    color: var(--primary);
    width: 16px;
    text-align: center;
}

.detail-value {
    font-size: 15px;
    color: var(--dark);
    line-height: 1.4;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    align-items: center;
    width: fit-content;
}

#view-status:contains("Confirmed") {
    background-color: rgba(16, 185, 129, 0.12);
    color: #065f46;
}

#view-status:contains("Pending") {
    background-color: rgba(245, 158, 11, 0.12);
    color: #92400e;
}

#view-status:contains("Completed") {
    background-color: rgba(37, 99, 235, 0.12);
    color: #1e40af;
}

#view-status:contains("Cancelled") {
    background-color: rgba(239, 68, 68, 0.12);
    color: #b91c1c;
}

.evaluation-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    align-items: center;
    width: fit-content;
}

#view-evaluation_status:contains("Not evaluated") {
    background-color: rgba(148, 163, 184, 0.12);
    color: #475569;
}

#view-evaluation_status:contains("Evaluated") {
    background-color: rgba(16, 185, 129, 0.12);
    color: #065f46;
}

/* Employee Badges */
.employee-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.employee-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    background-color: rgba(37, 99, 235, 0.1);
    color: #1e40af;
    align-items: center;
    gap: 6px;
}

.employee-badge i {
    font-size: 12px;
}

/* Description Section */
.detail-description {
    padding: 12px 16px;
    border-radius: 8px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: 14px;
    color: var(--text);
    line-height: 1.5;
    min-height: 60px;
}

/* Proof Image */
.proof-image-container {
    width: 100%;
    min-height: 200px;
    max-height: 400px;
    overflow: hidden;
    border-radius: 8px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.proof-image-container img {
    max-width: 100%;
    max-height: 400px;
    border-radius: 8px;
    object-fit: contain;
}

.no-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    font-size: 14px;
    height: 100%;
    width: 100%;
    min-height: 120px;
}

/* View Modal Styles */
#viewServiceModal .modal-body p {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    flex-wrap: wrap;
}

#viewServiceModal .modal-body p strong {
    flex: 0 0 170px;
    color: var(--dark);
    display: inline-flex;
    align-items: center;
}

#viewServiceModal .modal-body p span {
    flex: 1;
    color:white;
}

#viewServiceModal .modal-body p:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

/* Status badge in view modal */
#view-status {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 500;
}

.status-available {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.status-unavailable {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

/* Edit Modal Styles */
#editModal .modal-body {
    padding: 20px 24px;
}

#editModal .form-group {
    margin-bottom: 16px;
}

#editModal label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--dark);
    margin-bottom: 8px;
}

#editModal input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 14px;
    transition: all 0.2s;
}

#editModal input:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background-color: #f8fafc;
    border-radius: 0 0 16px 16px;
    position: sticky;
    bottom: 0;
    z-index: 10;
}


/* Confirmation Modal Specific Styles */
#deleteModal .modal-body {
    text-align: center;
    padding: 30px 24px;
}

#deleteModal .modal-body p {
    margin-bottom: 0;
    font-size: 15px;
    color: var(--text);
}

#deleteModal .modal-body i {
    font-size: 48px;
    color: var(--danger);
    margin-bottom: 20px;
    display: block;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button styles within modals */
.modal .btn {
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
}

.modal .btn-primary {
    background-color: var(--primary);
    color: white;
}


.modal .btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.modal .btn-secondary {
    background-color: white;
    color: var(--text);
    border: 1px solid var(--border);
}


.modal .btn-secondary:hover {
    background-color: #f8fafc;
}

.modal .btn-success {
    background-color: #10b981;
    color: white;
}

.modal .btn-success:hover {
    background-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

modal .btn-danger {
    background-color: var(--danger);
    color: white;
}

.modal .btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-section {
        padding: 16px 20px;
    }
    
    .modal-header, .modal-footer {
        padding: 16px 20px;
    }
}

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-secondary {
            background-color: white;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background-color: white;
            border-radius: var(--radius);
            padding: 16px 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 2000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success {
            border-left: 4px solid var(--success);
        }

        .toast-error {
            border-left: 4px solid var(--danger);
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 14px;
        }

        .toast-success .toast-icon {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .toast-error .toast-icon {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .toast-message {
            color: var(--text-light);
            font-size: 14px;
        }

        .toast-close {
            color: var(--text-light);
            cursor: pointer;
            padding: 4px;
        }

        /* Ensure the content has proper spacing */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .search-actions {
                flex-direction: column;
                gap: 12px;
            }
            .search-form {
                width: 100%;
            }
            .search-input {
                flex: 1;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }

        /* Animation for content loading */
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

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        .status-badge {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-confirmed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        /* Add these to your existing styles */
        .proof-image-container {
            margin-top: 20px;
        }

        #view-proof-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        #serviceTableBody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #serviceTableBody tr:hover {
            background-color: rgba(59, 130, 246, 0.05) !important;
        }

        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 14px;
            transition: all 0.2s;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        #current-proof-image img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        /* Tooltip for table rows */
        [title] {
            position: relative;
        }

        tr[title]:hover:after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--dark);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 100;
            opacity: 0.9;
            pointer-events: none;
        }
         .btn-success {
        background-color: var(--success);
        color: white;
    }
    .btn-success:hover {
        background-color: #0da271;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    /* Printable area styling */
    .print-container {
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        font-family: 'Arial', sans-serif;
    }
    
    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
        margin-bottom: 20px;
    }
    
    .print-logo {
        font-size: 32px;
        font-weight: bold;
        background-color: #4478bb;
        color: white;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 8px;
    }
    
    .print-service-id {
        text-align: right;
        margin-bottom: 15px;
        font-size: 14px;
    }
    
    .print-qr-container {
        float: right;
        margin-left: 20px;
        margin-bottom: 20px;
    }
    
    .print-section {
        margin-bottom: 25px;
        clear: both;
    }
    
    .print-section h3 {
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 15px;
        font-size: 18px;
    }
    
    .print-row {
        display: flex;
        margin-bottom: 12px;
    }
    
    .print-col {
        flex: 1;
        padding-right: 15px;
    }
    
    .full-width {
        flex-direction: column;
    }
    
    .print-signatures {
        margin-top: 50px;
    }
    
    .signature-line {
        border-bottom: 1px solid #333;
        height: 40px;
        margin-bottom: 5px;
    }
    
    .print-footer {
        margin-top: 50px;
        text-align: center;
        font-size: 12px;
        color: #666;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }

    #editModal {
    font-family: 'Poppins', 'Segoe UI', sans-serif;
}

#editModal .modal-content {
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: none;
    max-width: 900px;
    width: 90%;
    margin: 30px auto;
    background: #fff;
}
#editModal .modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
    background-color: #f8f9fa;
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#editModal .modal-header h3 {
    margin: 0;
    font-weight: 600;
    color: var(--dark);
    font-size: 20px;
    display: flex;
    align-items: center;
}

#editModal .modal-header h3::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 24px;
    background: linear-gradient(to bottom, var(--primary), var(--primary-light));
    margin-right: 12px;
    border-radius: 4px;
}

#editModal .modal-close {
    font-size: 24px;
    cursor: pointer;
    color: var(--medium);
    transition: var(--transition);
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

#editModal .modal-close:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--danger);
}

#editModal .modal-body {
    padding: 24px;
    max-height: 70vh;
    overflow-y: auto;
}

.form-columns {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.form-column {
    flex: 1;
    min-width: 300px;
}

.form-section {
    background: #f8f9fa;
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    border: 1px solid #f0f0f0;
}

.form-section h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    margin-top: 0;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.form-row .form-group {
    flex: 1;
    margin-bottom: 0;
}

#editModal .form-group {
    margin-bottom: 16px;
}

#editModal label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--medium);
    margin-bottom: 6px;
}

#editModal input,
#editModal textarea,
#editModal select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 14px;
    transition: var(--transition);
    background-color: #fff;
}

#editModal input:focus,
#editModal textarea:focus,
#editModal select:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
}

.input-icon-wrapper,
.select-wrapper {
    position: relative;
}

.input-icon,
.select-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--medium);
    pointer-events: none;
}

.select-wrapper select {
    appearance: none;
    padding-right: 30px;
}

.number-input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.number-input-wrapper input {
    border: none !important;
    text-align: center;
    box-shadow: none !important;
    width: 60px !important;
    padding: 10px 0 !important;
    background: transparent !important;
}

.decrement-btn,
.increment-btn {
    background: #f3f4f6;
    border: none;
    padding: 10px 14px;
    cursor: pointer;
    color: var(--dark);
    transition: var(--transition);
}

.decrement-btn:hover,
.increment-btn:hover {
    background: #e9ecef;
}

.description-group textarea {
    resize: vertical;
    min-height: 80px;
}

.proof-image-section {
    margin-bottom: 0;
}

.current-image {
    width: 100%;
    height: 120px;
    background-color: #f0f0f0;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    overflow: hidden;
}

.current-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.file-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-light);
    color: #fff;
    padding: 10px;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}

.file-upload-label:hover {
    background: var(--primary);
}

.file-upload-label i {
    margin-right: 6px;
}

.file-upload-input {
    display: none;
}
.status-completed {
    background-color: rgba(37, 99, 235, 0.1); /* soft blue background */
    color: var(--primary-dark);              /* dark blue text */
}


.file-name {
    font-size: 12px;
    color: var(--medium);
    text-align: center;
}
#editModal .modal-footer {
    padding: 16px 24px;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-top: 1px solid #f0f0f0;
    background-color: #f8f9fa;
    border-radius: 0 0 12px 12px;
}
/* Responsive adjustments */
@media (max-width: 768px) {
    .form-columns {
        flex-direction: column;
    }
    
    .form-row {
        flex-direction: column;
        gap: 16px;
    }
    
    #editModal .modal-content {
        width: 95%;
    }
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.pagination {
    margin: 0;
}

.page-link {
    color: #495057;
    background-color: #fff;
    border: 1px solid #dee2e6;
}

.page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
}

    </style>
</head>
<body>
    <!-- Sidebar - Kept as requested -->
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
                    <a href="/admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-section">Management</li>
                <li class="nav-item">
                    <a href="/manage_products.php" class="nav-link">
                       <i class="fas fa-box nav-icon"></i>
                        <span class="nav-text">Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/manage_services.php" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin_calendar.php" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Calendar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/manage_employee.php" class="nav-link">
                        <i class="fas fa-id-card nav-icon"></i>
                        <span class="nav-text">Employees</span>
                    </a>
                </li>
                
                <li class="nav-item" style="margin-top: auto;">
                    <a href="/index.php?logout=true" class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

    <!-- Main Content Area -->
    <div class="main-content animate-fade-in">
        <!-- Top Navigation Bar -->
        <div class="top-bar">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Page Header with Title and Actions -->
        <div class="page-header">
            <div class="page-title">
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h1>Services Management</h1>
            </div>
            <div class="search-actions">
                <form id="searchForm" method="GET" action="" class="search-form">
                    <input type="text" name="search" id="searchInput" placeholder="Search services..." class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                <a href="/add_service.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Service
                </a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
<table id="serviceTable">
        <thead>
            <tr>
                <th>Service Type</th>
                <th>Client Type</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Added Date</th>
                <th>Scheduled Date</th>
                <th>Time Finished</th>
                <th>Days Left</th>
                <th>Status</th>
                <th>Worker Count</th>
                <th>Assigned Employees</th>
                <th>Evaluation Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="serviceTableBody">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Status badge class
                $status_class = '';
                switch(strtolower($row['status'])) {
                    case 'confirmed':
                        $status_class = 'status-confirmed';
                        break;
                    case 'cancelled':
                        $status_class = 'status-cancelled';
                        break;
                    case 'pending':
                        $status_class = 'status-pending';
                        break;
                    case 'completed':
                        $status_class = 'status-completed';
                        break;
                    default:
                        $status_class = 'status-pending';
                }
                // Calculate days left
                $scheduledDate = $row['scheduled_date'];
                $daysLeft      = '—';
                if ($scheduledDate) {
                    $now      = new DateTime();
                    $sched    = new DateTime($scheduledDate);
                    $diffDays = $now->diff($sched)->format('%r%a');
                    $daysLeft = ((int)$diffDays >= 0)
                              ? $diffDays . ' day(s) left'
                              : 'Passed';
                }
                ?>
                <tr data-id="<?php echo $row['id']; ?>" title="Double click to show details">
                    <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['client_type'] ?? 'N/A'); ?></td>
                    <td>₱ <?php echo number_format((float)$row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['duration']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($scheduledDate ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['time_finished'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($daysLeft); ?></td>
                    <td>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </td>
                    <td><span class="count-badge"><?php echo (int)$row['worker_count']; ?></span></td>
                    <td>
                        <?php
                        if (!empty($row['employees'])) {
                            foreach (explode(', ', $row['employees']) as $emp) {
                                echo '<span class="employee-badge">' 
                                     . htmlspecialchars($emp) 
                                     . '</span> ';
                            }
                        } else {
                            echo '<span class="text-muted">No employees assigned</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['evaluation_status'] ?? 'N/A'); ?></td>
                    <td>
                        <button class="btn btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php
            }
        } else {
            echo '<tr><td colspan="13" class="text-center">No services found</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>



    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this service? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelDelete">Cancel</button>
                <form id="deleteForm" method="post">
                    <input type="hidden" name="delete_ids[]" id="deleteServiceId" value="">
                    <button type="submit" name="delete_service" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">Success</div>
            <div class="toast-message">Operation completed successfully</div>
        </div>
        <div class="toast-close">
            <i class="fas fa-times"></i>
        </div>
    </div>

<div id="viewServiceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-clipboard-list"></i> View Service Details</h3>
            <span class="modal-close close-view">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id">
            
            <div class="modal-section">
                <h4 class="section-title">Service Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-tag fa-fw"></i> Service Type</div>
                        <div class="detail-value" id="view-service_type"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-briefcase fa-fw"></i> Client Type</div>
                        <div class="detail-value" id="view-client_type"></div>
                    </div>

                    <div class="detail-item" id="view-company-container" style="display: none;">
                            <div class="detail-label"><i class="fas fa-building fa-fw"></i> Company Name</div>
                            <div class="detail-value" id="view-company_name"></div>
                        </div>

                    <div class="detail-item">
                          <div class="detail-label"><i class="fas fa-cubes fa-fw"></i> No. of Units</div>
                          <div class="detail-value" id="view-number_of_units"></div>
                        </div>


                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-money-bill-wave fa-fw"></i> Price</div>
                        <div class="detail-value">₱<span id="view-price"></span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-clock fa-fw"></i> Duration</div>
                        <div class="detail-value" id="view-duration"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-users fa-fw"></i> Worker Count</div>
                        <div class="detail-value" id="view-worker_count"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-check-circle fa-fw"></i> Status</div>
                        <div class="detail-value status-badge" id="view-status"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-section">
                <h4 class="section-title">Client Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-user fa-fw"></i> Client Name</div>
                        <div class="detail-value" id="view-client_name"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-phone fa-fw"></i> Client Contact</div>
                        <div class="detail-value" id="view-client_contact"></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label"><i class="fas fa-map-marker-alt fa-fw"></i> Client Address</div>
                        <div class="detail-value" id="view-client_address"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-section">
                <h4 class="section-title">Schedule & Assignment</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-calendar-plus fa-fw"></i> Created At</div>
                        <div class="detail-value" id="view-created_at"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-calendar-alt fa-fw"></i> Scheduled Date</div>
                        <div class="detail-value" id="view-scheduled_date"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-hourglass-end fa-fw"></i> Time Finished</div>
                        <div class="detail-value" id="view-time_finished"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-star-half-alt fa-fw"></i> Evaluation Status</div>
                        <div class="detail-value evaluation-badge" id="view-evaluation_status"></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label"><i class="fas fa-id-card fa-fw"></i> Assigned Employees</div>
                        <div class="detail-value employee-badges" id="view-employees"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-section">
                <h4 class="section-title">Description</h4>
                <div class="detail-description" id="view-description"></div>
            </div>
            
            <div class="modal-section">
                <h4 class="section-title"><i class="fas fa-image fa-fw"></i> Proof Image</h4>
                <div class="proof-image-container" id="view-proof-image">
                    <div class="no-image-placeholder">No image available</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-view">
                <i class="fas fa-times"></i> Close
            </button>
            <button type="button" class="btn btn-primary" id="editServiceBtn">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-success" id="printServiceBtn">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<div id="printableArea" style="display: none;">
    <div class="print-container">
        <div class="print-header">
            <h2>MFJ SERVICE DETAILS</h2>
            <div class="print-logo">MFJ</div>
        </div>
        <div class="print-service-id">
            <strong>Service ID:</strong> <span class="print-id"></span>
        </div>
        <div class="print-qr-container">
            <div id="serviceQrCode"></div>
        </div>
        <div class="print-section">
            <h3>Service Information</h3>
            <div class="print-row">
                <div class="print-col">
                    <strong>Service Type:</strong> <span class="print-service_type"></span>
                </div>
                <div class="print-col">
                    <strong>Status:</strong> <span class="print-status"></span>
                </div>
            </div>
            <div class="print-row">
                <div class="print-col">
                    <strong>Price:</strong> ₱<span class="print-price"></span>
                </div>
                <div class="print-col">
                    <strong>Duration:</strong> <span class="print-duration"></span>
                </div>
            </div>
            <div class="print-row">
                <div class="print-col">
                    <strong>Created At:</strong> <span class="print-created_at"></span>
                </div>
                <div class="print-col">
                    <strong>Scheduled Date:</strong> <span class="print-scheduled_date"></span>
                </div>
            </div>
            <div class="print-row">
                <div class="print-col">
                    <strong>Time Finished:</strong> <span class="print-time_finished"></span>
                </div>
                <div class="print-col">
                    <strong>Worker Count:</strong> <span class="print-worker_count"></span>
                </div>
            </div>
        </div>
        
        <div class="print-section">
            <h3>Client Information</h3>
            <div class="print-row">
                <div class="print-col">
                    <strong>Client Name:</strong> <span class="print-client_name"></span>
                </div>
                <div class="print-col">
                    <strong>Client Contact:</strong> <span class="print-client_contact"></span>
                </div>
            </div>

            <div class="print-row">
                <div class="print-col">
                    <strong>Client Type:</strong> <span class="print-client_type"></span>
                </div>
                <div class="print-col">
                    <strong>No. of Units:</strong> <span class="print-number_of_units"></span>
                </div>
            </div>
            <div class="print-row" id="print-company-row" style="display: none;">
                <strong>Company Name:</strong> <span class="print-company_name"></span>
            </div>

            <div class="print-row full-width">
                <strong>Client Address:</strong> <span class="print-client_address"></span>
            </div>
        </div>
        
        <div class="print-section">
            <h3>Assignment Details</h3>
            <div class="print-row full-width">
                <strong>Assigned Employees:</strong> <span class="print-employees"></span>
            </div>
            <div class="print-row full-width">
                <strong>Description:</strong> <span class="print-description"></span>
            </div>
        </div>
        
        <div class="print-section">
            <div class="print-row print-signatures">
                <div class="print-col">
                    <div class="signature-line"></div>
                    <p>Client Signature</p>
                </div>
                <div class="print-col">
                    <div class="signature-line"></div>
                    <p>Employee Signature</p>
                </div>
            </div>
        </div>
        
        <div class="print-footer">
            <p>Scan the QR code to confirm service completion</p>
            <p>This document was generated on <span id="print-date"></span></p>
        </div>
    </div>
</div>
<style type="text/css" media="print">
    body * {
        visibility: hidden;
    }
    #printableArea, #printableArea * {
        visibility: visible;
    }
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        display: block !important;
    }
    .modal {
        position: static;
        display: block;
        background: none;
    }
    .sidebar, .main-content, .modal-content {
        display: none;
    }
    @page {
        size: A4;
        margin: 0.5cm;
    }
</style>

<!-- Edit Service Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Service</h3>
            <span class="modal-close edit-close">&times;</span>
        </div>
        <form id="editForm" method="POST" action="update_service.php" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="form-columns">
                    <!-- Left Column -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4>Service Details</h4>
                            <div class="form-group">
                                <label for="edit-service_type">Service Type</label>
                                <input type="text" name="service_type" id="edit-service_type" required>
                            </div>

                            <div class="form-group">
                                <label for="edit-client_type">Client Type</label>
                                <div class="select-wrapper">
                                    <select name="client_type" id="edit-client_type" required>
                                        <option value="Household">Household</option>
                                        <option value="Company">Company</option>
                                    </select>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                </div>
                            </div>

                                <div class="form-group company-only" style="display: none;">
                                    <label for="edit-company_name">Company Name</label>
                                    <input type="text" name="company_name" id="edit-company_name">
                                </div>

                            <div class="form-group">
                                <label for="edit-number_of_units">No. of Units</label>
                                <input type="number" name="number_of_units" id="edit-number_of_units" min="0" required>
                            </div>


                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-price">Price (₱)</label>
                                    <input type="number" name="price" id="edit-price" step="0.01" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit-duration">Duration</label>
                                    <input type="text" name="duration" id="edit-duration" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-scheduled_date">Scheduled Date</label>
                                    <div class="input-icon-wrapper">
                                        <input type="date" name="scheduled_date" id="edit-scheduled_date">
                                        <i class="far fa-calendar-alt input-icon"></i>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="edit-time_finished">Time Finished</label>
                                    <div class="input-icon-wrapper">
                                        <input type="datetime-local" name="time_finished" id="edit-time_finished">
                                        <i class="far fa-clock input-icon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-status">Status</label>
                                    <div class="select-wrapper">
                                        <select name="status" id="edit-status" required>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Cancelled">Cancelled</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                        </select>

                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="edit-evaluation_status">Evaluation</label>
                                    <div class="select-wrapper">
                                        <select name="evaluation_status" id="edit-evaluation_status" required>
                                            <option value="For Evaluation">For Evaluation</option>
                                            <option value="Evaluated">Evaluated</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-worker_count">Worker Count</label>
                                <div class="number-input-wrapper">
                                    <button type="button" class="decrement-btn" onclick="decrementWorkers()">-</button>
                                    <input type="number" name="worker_count" id="edit-worker_count" min="0" value="1">
                                    <button type="button" class="increment-btn" onclick="incrementWorkers()">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4>Client Information</h4>
                            <div class="form-group">
                                <label for="edit-client_name">Client Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="client_name" id="edit-client_name">
                                    <i class="far fa-user input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit-client_address">Client Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="client_address" id="edit-client_address">
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit-client_contact">Client Contact</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="client_contact" id="edit-client_contact">
                                    <i class="fas fa-phone input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group description-group">
                                <label for="edit-description">Description</label>
                                <textarea name="description" id="edit-description" rows="3"></textarea>
                            </div>
                        </div>
                            
                        <div class="form-section proof-image-section">
                            <h4>Proof Image</h4>
                            <div class="form-group">
                                <div class="image-upload-container">
                                    <div id="current-proof-image" class="current-image">
                                        <!-- Image preview will be displayed here -->
                                    </div>
                                    <div class="file-upload-wrapper">
                                        <label for="edit-proof_image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Choose File</span>
                                        </label>
                                        <input type="file" name="proof_image" id="edit-proof_image" accept="image/*" class="file-upload-input">
                                        <div class="file-name" id="file-name-display">No file chosen</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary edit-close">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Are you sure you want to delete this service? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelDelete">
                <i class="fas fa-times"></i> Cancel
            </button>
            <form id="deleteForm" method="post">
                <input type="hidden" name="delete_ids[]" id="deleteServiceId" value="">
                <button type="submit" name="delete_service" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
   <script>
document.addEventListener('DOMContentLoaded', function() {
  // ───── DELETE & TOAST ──────────────────────────────────────
  const deleteModal = document.getElementById('deleteModal');
  const deleteForm = document.getElementById('deleteForm');
  const deleteServiceIdInput = document.getElementById('deleteServiceId');
  const cancelDeleteBtn = document.getElementById('cancelDelete');
  const modalCloses = document.querySelectorAll('.modal-close');
  const toast = document.getElementById('toast');
  const toastClose = document.querySelector('.toast-close');
  const viewModal = document.getElementById('viewServiceModal');
  const editModal = document.getElementById('editModal');
  const editBtn = document.getElementById('editServiceBtn');
  const editForm = document.getElementById('editForm');

  // show delete-success / error
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('delete') === 'success') {
    showToast('success','Success','Service deleted successfully');
  } else if (urlParams.get('delete') === 'error') {
    showToast('error','Error','Failed to delete service');
  }

  // Delete button click handler
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent event bubbling to the row
      deleteServiceIdInput.value = btn.getAttribute('data-id');
      deleteModal.style.display = 'flex';
    });
  });

  // Cancel delete action
  cancelDeleteBtn.addEventListener('click', () => {
    deleteModal.style.display = 'none';
  });

  // Close modals with X button
  modalCloses.forEach(close => {
    close.addEventListener('click', () => {
      close.closest('.modal').style.display = 'none';
    });
  });

  // Close toast
  if (toastClose) {
    toastClose.addEventListener('click', () => {
      toast.className = 'toast';
    });
  }
  
  // Close modal when clicking outside
  window.addEventListener('click', e => {
    if (e.target.classList.contains('modal')) {
      e.target.style.display = 'none';
    }
  });

  // Toast notification function
  function showToast(type, title, message) {
    toast.className = 'toast show';
    const toastIcon = toast.querySelector('.toast-icon i');
    if (toastIcon) {
      toastIcon.className = type === 'success' ? 'fas fa-check' : 'fas fa-exclamation';
    }
    toast.classList.add(type === 'success' ? 'toast-success' : 'toast-error');
    toast.classList.remove(type === 'success' ? 'toast-error' : 'toast-success');

    const toastTitle = toast.querySelector('.toast-title');
    if (toastTitle) {
      toastTitle.textContent = title;
    }

    const toastMessage = toast.querySelector('.toast-message');
    if (toastMessage) {
      toastMessage.textContent = message;
    }

    setTimeout(() => {
      toast.className = 'toast';
    }, 3000);
  }

  // ───── VIEW / EDIT MODALS ─────────────────────────────────────────────
  
  // Add double-click event listener to each table row
  document.querySelectorAll('#serviceTableBody tr').forEach(row => {
    row.addEventListener('dblclick', () => {
      const serviceId = row.getAttribute('data-id');
      if (!serviceId) return;
      
      // Fetch service details via AJAX
      fetch(`get_service.php?id=${serviceId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to fetch service details');
          }
          return response.json();
        })
        .then(service => {
          // Populate the view modal with service details
          document.querySelector('#viewServiceModal input[name="id"]').value = service.id;
          
          // Set service details in the view modal
          document.getElementById('view-service_type').textContent = service.service_type || '-';
          document.getElementById('view-client_type').textContent = service.client_type || '-';
          document.getElementById('view-price').textContent = parseFloat(service.price).toFixed(2) || '-';
          document.getElementById('view-duration').textContent = service.duration || '-';
          document.getElementById('view-created_at').textContent = service.created_at || '-';
          document.getElementById('view-scheduled_date').textContent = service.scheduled_date || '-';
          document.getElementById('view-time_finished').textContent = service.time_finished || '-';
          document.getElementById('view-evaluation_status').textContent = service.evaluation_status || 'Not evaluated yet';
          document.getElementById('view-number_of_units').textContent = service.number_of_units || '-';



            if (service.client_type === 'Company') {
                document.getElementById('view-company_name').textContent = service.company_name || '-';
                document.getElementById('view-company-container').style.display = 'block';
            } else {
                document.getElementById('view-company-container').style.display = 'none';
            }

          
          // Set status with appropriate styling
          const statusElement = document.getElementById('view-status');
          statusElement.textContent = service.status || '-';
          statusElement.className = 'status-badge';
          if (service.status) {
            if (service.status.toLowerCase() === 'confirmed') {
              statusElement.classList.add('status-confirmed');
            } else if (service.status.toLowerCase() === 'cancelled') {
              statusElement.classList.add('status-cancelled');
            } else if (service.status.toLowerCase() === 'pending') {
              statusElement.classList.add('status-pending');
            }
          }
          
          document.getElementById('view-worker_count').textContent = service.worker_count || '0';
          document.getElementById('view-client_name').textContent = service.client_name || '-';
          document.getElementById('view-client_address').textContent = service.client_address || '-';
          document.getElementById('view-client_contact').textContent = service.client_contact || '-';
          document.getElementById('view-employees').textContent = service.employees || 'No employees assigned';
          document.getElementById('view-description').textContent = service.description || 'No description available';
          document.getElementById('edit-evaluation_status').value = service.evaluation_status || '';

          
          // Set proof image if available
          const proofImageContainer = document.getElementById('view-proof-image');
          if (service.proof_image) {
proofImageContainer.innerHTML = `<img src="./${service.proof_image}" alt="Proof of service">`;
          } else {
            proofImageContainer.innerHTML = '<p class="text-muted">No image available</p>';
          }
          
          // Show the view modal
          viewModal.style.display = 'flex';
          
          // Set up the Edit button to populate and show the edit form
          if (editBtn) {
            editBtn.onclick = function() {
              // Populate edit form with service details
              document.getElementById('edit-id').value = service.id;
              document.getElementById('edit-service_type').value = service.service_type || '';
              document.getElementById('edit-client_type').value = service.client_type || 'Household';
              document.getElementById('edit-number_of_units').value = service.number_of_units || 0;
              document.getElementById('edit-price').value = service.price || '';
              document.getElementById('edit-duration').value = service.duration || '';
              document.getElementById('edit-company_name').value = service.company_name || '';

              if (service.client_type === 'Company') {
    document.querySelector('.company-only').style.display = 'block';
} else {
    document.querySelector('.company-only').style.display = 'none';
}

              
              // Format scheduled date for input field
              if (service.scheduled_date) {
                // Convert to YYYY-MM-DD format
                const date = new Date(service.scheduled_date);
                if (!isNaN(date.getTime())) {
                  const year = date.getFullYear();
                  const month = String(date.getMonth() + 1).padStart(2, '0');
                  const day = String(date.getDate()).padStart(2, '0');
                  document.getElementById('edit-scheduled_date').value = `${year}-${month}-${day}`;
                }
              } else {
                document.getElementById('edit-scheduled_date').value = '';
              }
              
              // Format time finished for datetime-local input
              if (service.time_finished) {
    const dateObj = new Date(service.time_finished);
    if (!isNaN(dateObj.getTime())) {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        const hours = String(dateObj.getHours()).padStart(2, '0');
        const minutes = String(dateObj.getMinutes()).padStart(2, '0');
        document.getElementById('edit-time_finished').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    } else {
        document.getElementById('edit-time_finished').value = '';
    }
} else {
    document.getElementById('edit-time_finished').value = '';
}
              
              // Set status dropdown
              const statusSelect = document.getElementById('edit-status');
              Array.from(statusSelect.options).forEach(option => {
                option.selected = option.value === service.status;
              });
              
              document.getElementById('edit-worker_count').value = service.worker_count || '';
              document.getElementById('edit-client_name').value = service.client_name || '';
              document.getElementById('edit-client_address').value = service.client_address || '';
              document.getElementById('edit-client_contact').value = service.client_contact || '';
              document.getElementById('edit-description').value = service.description || '';
              
              // Set current proof image preview if available
              const currentProofImage = document.getElementById('current-proof-image');
              if (service.proof_image) {
                currentProofImage.innerHTML = `<img src="${service.proof_image}" alt="Current proof image">`;
              } else {
                currentProofImage.innerHTML = '<p class="text-muted">No image currently uploaded</p>';
              }
              
              // Hide view modal and show edit modal
              viewModal.style.display = 'none';
              editModal.style.display = 'flex';
              // Set evaluation status dropdown
                const evaluationStatusSelect = document.getElementById('edit-evaluation_status');
                if (evaluationStatusSelect) {
                    Array.from(evaluationStatusSelect.options).forEach(option => {
                        option.selected = option.value === service.evaluation_status;
                    });
                }

            };
          }
        })
        .catch(error => {
          console.error('Error fetching service details:', error);
          showToast('error', 'Error', 'Failed to fetch service details');
        });
    });
  });

  // Close view modal buttons
  if (viewModal) {
    viewModal.querySelectorAll('.close-view').forEach(btn => {
      btn.addEventListener('click', () => {
        viewModal.style.display = 'none';
      });
    });
  }

  // Close edit modal buttons
  if (editModal) {
    editModal.querySelectorAll('.edit-close').forEach(btn => {
      btn.addEventListener('click', () => {
        editModal.style.display = 'none';
      });
    });
  }

  // Submit edit form via AJAX
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Show loading state in button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
      submitBtn.disabled = true;
      
      const formData = new FormData(this);
      
      fetch('update_service.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(result => {
        if (result.status === 'success') {
          showToast('success', 'Updated', 'Service updated successfully');
          editModal.style.display = 'none';
          
          // Refresh the page after a short delay
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          throw new Error(result.message || 'Failed to update service');
        }
      })
      .catch(error => {
        console.error('Error updating service:', error);
        showToast('error', 'Error', error.message || 'Network error occurred');
        
        // Reset button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
      });
    });
  }

  // Add keyboard listeners for Escape key to close modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      document.querySelectorAll('.modal').forEach(modal => {
        if (modal.style.display === 'flex') {
          modal.style.display = 'none';
        }
      });
    }
  });
});

document.addEventListener('DOMContentLoaded', function() {
    // Add print button functionality
    const printBtn = document.getElementById('printServiceBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            const serviceId = document.querySelector('#viewServiceModal input[name="id"]').value;
            const clientType = document.getElementById('view-client_type').textContent;
            const numberOfUnits = document.getElementById('edit-number_of_units')?.value || '0';
            const companyName = document.getElementById('edit-company_name')?.value || '-';
            
            // Populate printable area with service details
            document.querySelector('.print-id').textContent = serviceId;
            document.querySelector('.print-service_type').textContent = document.getElementById('view-service_type').textContent;
            document.querySelector('.print-price').textContent = document.getElementById('view-price').textContent;
            document.querySelector('.print-duration').textContent = document.getElementById('view-duration').textContent;
            document.querySelector('.print-created_at').textContent = document.getElementById('view-created_at').textContent;
            document.querySelector('.print-scheduled_date').textContent = document.getElementById('view-scheduled_date').textContent;
            document.querySelector('.print-time_finished').textContent = document.getElementById('view-time_finished').textContent;
            document.querySelector('.print-status').textContent = document.getElementById('view-status').textContent;
            document.querySelector('.print-worker_count').textContent = document.getElementById('view-worker_count').textContent;
            document.querySelector('.print-client_name').textContent = document.getElementById('view-client_name').textContent;
            document.querySelector('.print-client_address').textContent = document.getElementById('view-client_address').textContent;
            document.querySelector('.print-client_contact').textContent = document.getElementById('view-client_contact').textContent;
            document.querySelector('.print-employees').textContent = document.getElementById('view-employees').textContent;
            document.querySelector('.print-description').textContent = document.getElementById('view-description').textContent;
            document.querySelector('.print-client_type').textContent = clientType;
            document.querySelector('.print-number_of_units').textContent = numberOfUnits;


// Show company name only if client is a company
const companyRow = document.getElementById('print-company-row');
if (clientType.toLowerCase() === 'company') {
    document.querySelector('.print-company_name').textContent = companyName;
    companyRow.style.display = 'block';
} else {
    companyRow.style.display = 'none';
}
            
            // Add current date to printout
            const now = new Date();
            const formattedDate = now.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('print-date').textContent = formattedDate;
            
            // Generate random QR code
            generateServiceQR(serviceId);
            
            // Slight delay to ensure QR code renders
            setTimeout(function() {
                window.print();
            }, 300);
        });
    }
    
    // Function to generate QR code
    function generateServiceQR(serviceId) {
        // Generate a random token to add to the URL for security
        const randomToken = Math.random().toString(36).substring(2, 15);
        
        // Create URL for job assignment with service ID
        const serviceUrl = `/employee_login.php?service_id=${serviceId}&token=${randomToken}`;
        
        // Generate QR code
        const qrContainer = document.getElementById('serviceQrCode');
        if (qrContainer) {
            // Clear any existing QR code
            qrContainer.innerHTML = '';
            
            // Generate new QR code
            const qr = qrcode(0, 'L');
            qr.addData(serviceUrl);
            qr.make();
            
            // Add QR code to container
            const qrImage = qr.createImgTag(5);
            qrContainer.innerHTML = qrImage;
            
            // Add alt text for accessibility
            const img = qrContainer.querySelector('img');
            if (img) {
                img.alt = 'QR Code for service completion';
                img.style.width = '120px';
                img.style.height = '120px';
            }
        }
    }
});


function incrementWorkers() {
    document.getElementById('edit-worker_count').value = parseInt(document.getElementById('edit-worker_count').value) + 1;
}

function decrementWorkers() {
    const input = document.getElementById('edit-worker_count');
    const value = parseInt(input.value);
    if (value > 0) {
        input.value = value - 1;
    }
}

// Display file name when selected
document.getElementById('edit-proof_image').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
    document.getElementById('file-name-display').textContent = fileName;
    
    // Preview image
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgPreview = document.createElement('img');
            imgPreview.src = e.target.result;
            imgPreview.alt = 'Preview';
            document.getElementById('current-proof-image').innerHTML = '';
            document.getElementById('current-proof-image').appendChild(imgPreview);
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

$(document).ready(function () {
    $('#serviceTable').DataTable({
        "pageLength": 5,
        "lengthChange": false,
        "pagingType": "simple_numbers",
        "language": {
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });
});


</script>
</body>
</html>
