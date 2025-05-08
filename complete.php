<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Error: You must log in to view job assignments.");
}

$username = $_SESSION['username'];

// Database connection
$host = "localhost";
$dbname = "mfj_db";
$username_db = "root";
$password = "";

$conn = new mysqli($host, $username_db, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee ID
$user_id_query = "SELECT id, name FROM employees WHERE username = ?";
$stmt = $conn->prepare($user_id_query);

if (!$stmt) {
    die("Error preparing query (employee ID): " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($employee_id, $employee_name);
$stmt->fetch();
$stmt->close();

if (empty($employee_id)) {
    die("Error: Employee not found. Please ensure the username exists in the database.");
}

// Get service ID from the URL parameter
$service = null;
if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    
    // Fetch service details
    $service_query = "SELECT s.* 
                      FROM services AS s
                      INNER JOIN service_employees AS se ON s.id = se.service_id
                      WHERE se.employee_id = ? AND s.id = ?";
    
    $stmt = $conn->prepare($service_query);
    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $employee_id, $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $service = $result->fetch_assoc();
    } else {
        die("Service not found or you don't have access to this service.");
    }
    
    $stmt->close();
} else {
    die("No service ID provided.");
}

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
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
                        $_SESSION['completion_success'] = true;
                        
                        // Redirect back to job_assignment.php after successful completion
                        header("Location: job_assignment.php");
                        exit();
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
    
    if (!$upload_success) {
        $_SESSION['completion_error'] = $error_message;
    }
}

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
    <title>Complete Job Assignment</title>
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/symphony.png')] opacity-5 z-0"></div>

    <!-- Toast notifications -->
    <?php if (isset($_SESSION['completion_error'])): ?>
        <div id="error-toast" class="toast flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 shadow-lg" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                </svg>
            </div>
            <div class="ml-3 text-sm font-medium">
                <?php echo htmlspecialchars($_SESSION['completion_error']); ?>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#error-toast" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['completion_error']); ?>
    <?php endif; ?>

    <div class="w-full max-w-4xl bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl z-10 overflow-hidden animate-fade-in-up border border-gray-100">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 flex justify-between items-center rounded-t-3xl shadow-md">
            <div class="flex items-center space-x-4">
                <a href="job_assignment.php" class="text-white hover:bg-indigo-600 p-2 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-xl sm:text-2xl font-semibold text-white">Complete Job Assignment</h1>
            </div>
            <div class="text-white text-sm font-light">
                Welcome, <span class="font-medium"><?php echo htmlspecialchars($employee_name); ?></span>
            </div>
        </div>

        <div class="p-8">
            <?php if ($service): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-indigo-50 p-5 rounded-xl shadow-sm">
                        <h3 class="text-lg font-semibold text-indigo-800 mb-4">Service Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Service Type:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['service_type']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Duration:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['duration']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Price:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['price']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['status']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-5 rounded-xl shadow-sm">
                        <h3 class="text-lg font-semibold text-purple-800 mb-4">Schedule Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Scheduled Date:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['scheduled_date']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Scheduled Time:</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['scheduled_time']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Countdown:</span>
                                <span class="font-medium text-gray-800"><?php echo calculateDaysLeft($service['scheduled_date']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-xl shadow-sm mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Client Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="block text-gray-600 mb-1">Name</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['client_name']); ?></span>
                        </div>
                        <div>
                            <span class="block text-gray-600 mb-1">Contact</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['client_contact']); ?></span>
                        </div>
                        <div>
                            <span class="block text-gray-600 mb-1">Address</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($service['client_address']); ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Mark Job as Complete</h3>
                    
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                    
                    <div class="mb-6">
                        <label for="recorded_time" class="block mb-2 text-sm font-medium text-gray-700">Time of Completion</label>
                        <input type="datetime-local" id="recorded_time" name="recorded_time" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        <p class="mt-1 text-sm text-gray-500">Leave as is if you're completing the job now</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="proof_image" class="block mb-2 text-sm font-medium text-gray-700">Proof of Completion (Image)</label>
                        <input type="file" id="proof_image" name="proof_image" accept="image/*" required
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                        <p class="mt-1 text-sm text-gray-500">Upload a photo as proof of completed service (JPG, PNG, GIF)</p>
                    </div>
                    
                    <div class="flex justify-end">
                        <a href="job_assignment.php" class="text-gray-500 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2">
                            Cancel
                        </a>
                        <button type="submit" name="mark_complete" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Mark as Complete
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Error</p>
                            <p class="text-sm">No service found or you don't have permission to view this service.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <a href="job_assignment.php" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Job Assignments
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-dismiss toast notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const errorToast = document.getElementById('error-toast');
                
                if (errorToast) {
                    errorToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => errorToast.remove(), 500);
                }
            }, 5000);
        });
    </script>
</body>
</html>