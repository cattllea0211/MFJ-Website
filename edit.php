<?php 
$servername = "localhost";
$username = "mfj_user";
$password = "StrongPassword123!";
$dbname = "mfjdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No service ID provided.</div>";
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-warning'>Service not found.</div>";
    exit;
}

$service = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
<div class="container">
    <h2 class="mb-4">Update Service</h2>
    <form method="post" action="manager_update_service.php" class="row g-3">
        <input type="hidden" name="id" value="<?= htmlspecialchars($service['id']) ?>">

        <?php
        function formGroup($label, $name, $value, $type = 'text', $extra = '') {
            echo <<<HTML
            <div class="col-md-6">
                <label for="$name" class="form-label">$label</label>
                <input type="$type" name="$name" id="$name" class="form-control" value="$value" $extra>
            </div>
            HTML;
        }

        formGroup("Duration", "duration", htmlspecialchars($service['duration']));
        formGroup("Price", "price", htmlspecialchars($service['price']));
        formGroup("Scheduled Date", "scheduled_date", htmlspecialchars($service['scheduled_date']), 'date');
        formGroup("Scheduled Time", "scheduled_time", htmlspecialchars($service['scheduled_time']), 'time');
        formGroup("Client Name", "client_name", htmlspecialchars($service['client_name']));
        formGroup("Company Name", "company_name", htmlspecialchars($service['company_name']));
        formGroup("Client Address", "client_address", htmlspecialchars($service['client_address']));
        formGroup("Client Contact", "client_contact", htmlspecialchars($service['client_contact']));
        formGroup("Number of Units", "number_of_units", htmlspecialchars($service['number_of_units']), 'number');
        ?>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($service['description']) ?></textarea>
        </div>

        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <?php
                $statuses = ['Confirmed', 'Cancelled', 'Pending', 'Completed'];
                foreach ($statuses as $status) {
                    $selected = ($service['status'] === $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>$status</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="client_type" class="form-label">Client Type</label>
            <select name="client_type" id="client_type" class="form-select">
                <option <?= $service['client_type'] === 'Household' ? 'selected' : '' ?>>Household</option>
                <option <?= $service['client_type'] === 'Company' ? 'selected' : '' ?>>Company</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="evaluation_status" class="form-label">Evaluation Status</label>
            <select name="evaluation_status" id="evaluation_status" class="form-select">
                <option <?= $service['evaluation_status'] === 'For Evaluation' ? 'selected' : '' ?>>For Evaluation</option>
                <option <?= $service['evaluation_status'] === 'Evaluated' ? 'selected' : '' ?>>Evaluated</option>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Update Service</button>
        </div>
    </form>
</div>
</body>
</html>
