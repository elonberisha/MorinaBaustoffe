<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    die("Ndalohet qasja");
}

if (isset($_GET['file'])) {
    $file = "uploads/" . basename($_GET['file']);
    if (file_exists($file)) {
        unlink($file);
        header("Location: dashboard.php");
    } else {
        echo "Skedari nuk ekziston.";
    }
}
?>
