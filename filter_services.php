<?php
include 'db_connection.php';

$status = $_POST['status'] ?? '';
$clientType = $_POST['clientType'] ?? '';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

$sql = "SELECT * FROM services WHERE 1";

if (!empty($status)) {
  $sql .= " AND status = '$status'";
}
if (!empty($clientType)) {
  $sql .= " AND client_type = '$clientType'";
}
if (!empty($startDate) && !empty($endDate)) {
  $sql .= " AND date BETWEEN '$startDate' AND '$endDate'";
}

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
  echo "<div class='card mb-3'>";
  echo "<div class='card-body'>";
  echo "<h5 class='card-title'>{$row['client_name']}</h5>";
  echo "<p class='card-text'>Status: {$row['status']}</p>";
  echo "<p class='card-text'>Client Type: {$row['client_type']}</p>";
  echo "<p class='card-text'>Date: {$row['date']}</p>";
  echo "</div></div>";
}
?>
