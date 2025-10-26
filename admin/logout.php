<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to login page (same folder)
header("Location: login.php");
exit;
?>
