<?php
// check_updates.php
header('Content-Type: application/json');


$currentVersion = "1.0.0"; 
$latestVersion = "1.0.1"; 

if (version_compare($latestVersion, $currentVersion, '>')) {
    echo json_encode(['updateAvailable' => true, 'latestVersion' => $latestVersion]);
} else {
    echo json_encode(['updateAvailable' => false]);
}
?>
