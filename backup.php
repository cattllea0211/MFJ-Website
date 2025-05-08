<?php

function createBackup($dbHost, $dbUsername, $dbPassword, $dbName, $backupDir) {

    $timestamp = date("Y-m-d_H-i-s");
    
   
    $backupFile = $backupDir . "/backup_{$dbName}_{$timestamp}.sql";

   
    $mysqldumpPath = 'C:/xampp/mysql/bin/mysqldump'; // Update if necessary

  
    $command = "\"{$mysqldumpPath}\" --host={$dbHost} --user={$dbUsername} --password={$dbPassword} {$dbName} > \"{$backupFile}\"";

   
    exec($command, $output, $returnVar);

  
    if ($returnVar == 0) {
        echo "Backup successful! File saved at: " . $backupFile;
    } else {
        echo "Error occurred during backup.";
    }
}

createBackup('localhost', 'root', '', 'mfj_db', 'C:/xampp/backup');
?>
