<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == true) {
  
    $_SESSION = array();

  
    session_destroy();

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");


    header("Location: /MFJ/admin_login.php");
    exit();
}


function validateAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
     
        header("Location: /MFJ/admin_login.php");
        exit();
    }
}

validateAdminSession();
?>