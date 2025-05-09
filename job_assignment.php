<?php 
date_default_timezone_set('Asia/Manila'); // or whatever timezone you are

session_start();

if (!isset($_SESSION['username'])) {
    die("Error: You must log in to view job assignments.");
}

$username = $_SESSION['username'];

$conn = new mysqli('localhost', 'root', '', 'mfjdb');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id_query = "SELECT id, name FROM employees WHERE username = ?";
$stmt = $conn->prepare($user_id_query);

if (!$stmt) {
    die("Error preparing query (employee ID): " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($employee_id, $name);
$stmt->fetch();
$stmt->close();

if (empty($employee_id)) {
    die("Error: Employee not found. Please ensure the username exists in the database.");
}

// Handle form submission for marking job as complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $service_id = $_POST['service_id'];
    $upload_success = false;
    $error_message = '';
    
    // Get the recorded time or use current time if not provided
    $recorded_time = !empty($_POST['recorded_time']) ? $_POST['recorded_time'] : date('Y-m-d H:i:s');
    
    // Check if proof image was uploaded
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === 0) {
        $upload_dir = 'uploads/proof/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_ext), $allowed_extensions)) {
            $file_name = 'proof_' . $service_id . '_' . time() . '.' . $file_ext;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_file)) {
                // Update service status and add proof image path to database
$update_sql = "UPDATE services 
               SET status = 'Completed pending validation', 
                   evaluation_status = 'For Evaluation',
                   proof_image = ?, 
                   time_finished = ? 
               WHERE id = ?";


                $update_stmt = $conn->prepare($update_sql);
                
                if ($update_stmt) {
                    $update_stmt->bind_param("ssi", $target_file, $recorded_time, $service_id);
                    
                    if ($update_stmt->execute()) {
                        // Calculate overtime
                        $service_info_sql = "SELECT scheduled_date, scheduled_time, duration FROM services WHERE id = ?";
                        $service_info_stmt = $conn->prepare($service_info_sql);
                        $service_info_stmt->bind_param("i", $service_id);
                        $service_info_stmt->execute();
                        $service_info_result = $service_info_stmt->get_result();
                        $service_info = $service_info_result->fetch_assoc();
                        $service_info_stmt->close();
                        
                        // Calculate scheduled end time
                                                // Calculate scheduled end time
                        $scheduled_datetime = $service_info['scheduled_date'] . ' ' . $service_info['scheduled_time'];
                        $scheduled_start = new DateTime($scheduled_datetime);

                        // Handle duration properly
                        $duration_value = intval($service_info['duration']);

                        // Assume duration is in hours
                        $scheduled_end = clone $scheduled_start;
                        $scheduled_end->add(new DateInterval('PT' . $duration_value . 'H'));

                        // Calculate actual time difference
                        $time_finished = new DateTime($recorded_time);

                        // Only calculate overtime if the employee finished later than scheduled
                        $overtime_hours = 0;
                        if ($time_finished > $scheduled_end) {
                            $time_diff_seconds = $time_finished->getTimestamp() - $scheduled_end->getTimestamp();
                            $overtime_hours = $time_diff_seconds / 3600; // seconds to hours
                        }

                        // Update overtime hours
                        $update_overtime_sql = "UPDATE services SET overtime_hours = ? WHERE id = ?";
                        $update_overtime_stmt = $conn->prepare($update_overtime_sql);
                        $update_overtime_stmt->bind_param("di", $overtime_hours, $service_id);
                        $update_overtime_stmt->execute();
                        $update_overtime_stmt->close();

                        
                        $_SESSION['overtime_calculated'] = $overtime_hours > 0;
                        $_SESSION['overtime_hours'] = $overtime_hours;
                        
                        $upload_success = true;
                    } else {
                        $error_message = "Failed to update service status: " . $update_stmt->error;
                    }
                    $update_stmt->close();
                } else {
                    $error_message = "Error preparing update query: " . $conn->error;
                }
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        $error_message = "Please upload a proof image.";
    }
    
    // Set session variables to show toast notification
    if ($upload_success) {
        $_SESSION['completion_success'] = true;
    } else {
        $_SESSION['completion_error'] = $error_message;
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Updated SQL query to include scheduled_time column
$sql = "SELECT s.id, s.duration, s.price, s.service_type, s.status, s.scheduled_date, s.scheduled_time, 
               s.client_name, s.client_address, s.client_contact, s.proof_image, s.time_finished,
               s.overtime_hours, 
               s.evaluation_status,
               GROUP_CONCAT(e.name SEPARATOR ', ') AS assigned_employees
        FROM services AS s
        INNER JOIN service_employees AS se ON s.id = se.service_id
        INNER JOIN employees AS e ON se.employee_id = e.id
        WHERE se.service_id IN (
            SELECT service_id FROM service_employees WHERE employee_id = ?
        )
        GROUP BY s.id";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing query (job assignments): " . $conn->error);
}

$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$stmt->close();

// Get closest service schedule - also include scheduled_time
$closest_service_sql = "SELECT s.id, s.service_type, s.scheduled_date, s.scheduled_time, s.client_name, s.client_address
                        FROM services AS s
                        INNER JOIN service_employees AS se ON s.id = se.service_id
                        WHERE se.employee_id = ? AND s.scheduled_date >= CURDATE()
                        ORDER BY s.scheduled_date ASC
                        LIMIT 1";

$closest_stmt = $conn->prepare($closest_service_sql);
$closest_stmt->bind_param("i", $employee_id);
$closest_stmt->execute();
$closest_result = $closest_stmt->get_result();
$closest_service = $closest_result->fetch_assoc();
$closest_stmt->close();

$conn->close();

// Function to calculate days left
function calculateDaysLeft($scheduledDate) {
    $today = new DateTime();
    $scheduled = new DateTime($scheduledDate);
    $interval = $today->diff($scheduled);
    
    if ($interval->invert) {
        return "Overdue by " . $interval->days . " day(s)";
    } else {
        return $interval->days . " day(s) left";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Job Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: url('employeebg3.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: fadeInUp 0.6s ease-out;
        }
        .fixed {
            z-index: 50 !important;
        }
       
        .modal-content {
            z-index: 60 !important;
            position: relative;
        }
        .modal input, 
        .modal button, 
        .modal label {
            position: relative;
            z-index: 70 !important;
        }

        /* Make file input container properly clickable */
        label[for^="proof_image_"] {
            position: relative;
            z-index: 70;
            pointer-events: auto !important;
        }

        #completeJobModal {
            position: fixed !important;
            z-index: 9999 !important;
            pointer-events: auto !important;
            background: white; /* optional for visibility */
        }
        .button-container {
          text-align: center;
        }
        
        .complete-button {
          background-color: #4CAF50;
          color: white;
          border: none;
          padding: 10px 15px;
          font-size: 12px;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: all 0.3s ease;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          display: inline-flex;
          align-items: center;
          gap: 8px;
        }
        
        .complete-button:hover {
          background-color: #3e8e41;
          transform: translateY(-2px);
          box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .complete-button:active {
          transform: translateY(1px);
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .icon {
          display: inline-block;
          width: 18px;
          height: 18px;
        }
        
        /* Alternative buttons */
        .button-options {
          display: flex;
          gap: 20px;
          margin-top: 40px;
          flex-wrap: wrap;
          justify-content: center;
        }
        
        .blue-button {
          background-color: #2196F3;
        }
        
        .blue-button:hover {
          background-color: #0d8bf2;
        }
        
        .purple-button {
          background-color: #673AB7;
        }
        
        .purple-button:hover {
          background-color: #5e35b1;
        }
        
        .outlined-button {
          background-color: transparent;
          color: #4CAF50;
          border: 2px solid #4CAF50;
        }
        
        .outlined-button:hover {
          background-color: rgba(76, 175, 80, 0.1);
        }
        
        .rounded-button {
          border-radius: 50px;
        }

        /* Fix for small screens */
        @media (max-width: 640px) {
            .fixed {
                width: 100% !important;
                height: 100% !important;
                overflow-y: auto !important;
            }
        }

        input[type="file"]::file-selector-button {
            background-color: #4F46E5;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        input[type="file"]::file-selector-button:hover {
            background-color: #4338CA;
        }
        @media (max-width: 640px) {
              .toast {
                top: auto;
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
                width: auto;
              }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/symphony.png')] opacity-5 z-0"></div>

    <!-- Toast notifications -->
    <div id="success-toast" class="toast flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 shadow-lg hidden" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
        </div>
        <div class="ml-3 text-xs sm:text-sm font-medium">
            Job marked as complete successfully! The admin will validate your submission.
        </div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#success-toast" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>

    <div class="w-full max-w-[1600px] h-[90vh] bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl z-10 overflow-hidden flex flex-col animate-fade-in-up border border-gray-100">

        <!-- Teal header like in the second image -->
        <div class="bg-[#1d7691] p-6 flex justify-between items-center rounded-t-3xl shadow-md">
            <div class="flex items-center space-x-4">
                <a href="employee_dashboard.php" class="text-white hover:bg-teal-600 p-2 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-xl sm:text-2xl font-semibold text-white">Job Assignments</h1>
            </div>
            <div class="text-white text-sm font-light">
                Welcome, <span class="font-medium">Athena Dizon</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">
            <!-- Main content area -->
            <div class="flex-1 p-6 overflow-y-auto">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">
                    Current Job Assignments
                </h2>
                <div class="overflow-x-auto w-full mb-10">
                    <table class="min-w-full text-sm text-left text-gray-700 border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-xs text-indigo-800 uppercase tracking-wide">
                                <th class="px-4 py-3 bg-indigo-100 rounded-l-xl">Service Type</th>
                                <th class="px-4 py-3 bg-indigo-100">Duration</th>
                                <th class="px-4 py-3 bg-indigo-100">Price</th>
                                <th class="px-4 py-3 bg-indigo-100">Scheduled Date</th>
                                <th class="px-4 py-3 bg-indigo-100">Scheduled Time</th>
                                <th class="px-4 py-3 bg-indigo-100">Days Left</th>
                                <th class="px-4 py-3 bg-indigo-100">Client Name</th>
                                <th class="px-4 py-3 bg-indigo-100">Time Finished</th>
                                <th class="px-4 py-3 bg-indigo-100">Overtime (hrs)</th>
                                <th class="px-4 py-3 bg-indigo-100">Action</th>
                                <th class="px-4 py-3 bg-indigo-100 rounded-r-xl">Evaluation Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr class="bg-white shadow-sm hover:shadow-md transition duration-200 rounded-xl" style="animation: fadeInUp 0.5s ease 0s both;">
                                <td class="px-4 py-4 rounded-l-xl">Preventive Maintenance</td>
                                <td class="px-4 py-4">1</td>
                                <td class="px-4 py-4">1000.00</td>
                                <td class="px-4 py-4">2025-05-04</td>
                                <td class="px-4 py-4">10:16:00</td>
                                <td class="px-4 py-4">Overdue by 5 day(s)</td>
                                <td class="px-4 py-4">Celia Foote</td>
                                <td class="px-4 py-4">2025-05-04 18:00</td>
                                <td class="px-4 py-4 text-amber-600 font-medium">6.87</td>
                                <td class="px-4 py-4">
                                    <button class="complete-button outlined-button" onclick="window.location.href='complete.php?id=1'">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Mark Complete
                                    </button>
                                </td>
                                <td class="px-4 py-4 rounded-r-xl">For Evaluation</td>
                            </tr>
                            
                            <!-- Row 2 -->
                            <tr class="bg-white shadow-sm hover:shadow-md transition duration-200 rounded-xl" style="animation: fadeInUp 0.5s ease 0.1s both;">
                                <td class="px-4 py-4 rounded-l-xl">Repair</td>
                                <td class="px-4 py-4">1</td>
                                <td class="px-4 py-4">1000.00</td>
                                <td class="px-4 py-4">2025-05-09</td>
                                <td class="px-4 py-4">21:18:00</td>
                                <td class="px-4 py-4">Overdue by 0 day(s)</td>
                                <td class="px-4 py-4">Celia Foote</td>
                                <td class="px-4 py-4">2025-05-04 20:12</td>
                                <td class="px-4 py-4">-</td>
                                <td class="px-4 py-4">
                                    <button class="complete-button outlined-button" onclick="window.location.href='complete.php?id=2'">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Mark Complete
                                    </button>
                                </td>
                                <td class="px-4 py-4 rounded-r-xl">For Evaluation</td>
                            </tr>
                            
                            <!-- Row 3 -->
                            <tr class="bg-white shadow-sm hover:shadow-md transition duration-200 rounded-xl" style="animation: fadeInUp 0.5s ease 0.2s both;">
                                <td class="px-4 py-4 rounded-l-xl">Repair</td>
                                <td class="px-4 py-4">1</td>
                                <td class="px-4 py-4">1000.00</td>
                                <td class="px-4 py-4">2025-05-07</td>
                                <td class="px-4 py-4">08:00:00</td>
                                <td class="px-4 py-4">Overdue by 2 day(s)</td>
                                <td class="px-4 py-4">Maria Clara Ibarra</td>
                                <td class="px-4 py-4">-</td>
                                <td class="px-4 py-4">-</td>
                                <td class="px-4 py-4">
                                    <button class="complete-button outlined-button" onclick="window.location.href='complete.php?id=3'">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Mark Complete
                                    </button>
                                </td>
                                <td class="px-4 py-4 rounded-r-xl">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">
                    Completed Services
                </h2>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-xs text-indigo-800 uppercase tracking-wide">
                                <th class="px-4 py-3 bg-indigo-100 rounded-l-xl">Service Type</th>
                                <th class="px-4 py-3 bg-indigo-100">Duration</th>
                                <th class="px-4 py-3 bg-indigo-100">Price</th>
                                <th class="px-4 py-3 bg-indigo-100">Scheduled Date</th>
                                <th class="px-4 py-3 bg-indigo-100">Scheduled Time</th>
                                <th class="px-4 py-3 bg-indigo-100">Days Left</th>
                                <th class="px-4 py-3 bg-indigo-100">Client Name</th>
                                <th class="px-4 py-3 bg-indigo-100">Time Finished</th>
                                <th class="px-4 py-3 bg-indigo-100">Overtime (hrs)</th>
                                <th class="px-4 py-3 bg-indigo-100 rounded-r-xl">Evaluation Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example completed services would go here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right sidebar for closest service -->
            <div class="w-full lg:w-80 bg-gray-50 p-6 border-t lg:border-t-0 lg:border-l border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Next Scheduled Service</h2>
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-indigo-600">Repair</h3>
                        <p class="text-sm text-gray-600">Service ID: 34</p>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-700 font-medium">May 9, 2025</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-gray-700 font-medium">09:18 PM</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-gray-700 font-medium">Overdue by 0 day(s)</span>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Client:</span> Celia Foote
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Address:</span> dsadad
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss toast notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const successToast = document.getElementById('success-toast');
                const errorToast = document.getElementById('error-toast');
                const timeToast = document.getElementById('time-toast');
                
                if (successToast) {
                    successToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => successToast.remove(), 500);
                }
                
                if (errorToast) {
                    errorToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => errorToast.remove(), 500);
                }
                
                if (timeToast) {
                    timeToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => timeToast.remove(), 500);
                }
            }, 5000);
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const label = input.closest('label');
                    const textElement = label.querySelector('p');
                    if (textElement) {
                        textElement.innerHTML = `<span class="font-semibold">${file.name}</span> selected`;
                    }
                }
            });
        });
    </script>
</body>
</html>
