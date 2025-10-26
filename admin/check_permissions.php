<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$uploadsDir = "uploads/";
$commentsFile = "image_comments.json";

echo "<h2>Kontrolli i Lejeve</h2>";

// Check and create uploads directory
if (!is_dir($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<p style='color: green;'>✓ Direktoria e ngarkimeve u krijua me sukses</p>";
    } else {
        echo "<p style='color: red;'>✗ Nuk mund të krijohet direktoria e ngarkimeve</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Direktoria e ngarkimeve ekziston</p>";
}

// Check if uploads directory is writable
if (is_writable($uploadsDir)) {
    echo "<p style='color: green;'>✓ Direktoria e ngarkimeve është e shkrueshme</p>";
} else {
    echo "<p style='color: red;'>✗ Direktoria e ngarkimeve nuk është e shkrueshme</p>";
    if (chmod($uploadsDir, 0755)) {
        echo "<p style='color: green;'>✓ Lejet e drejtorisë u ndryshuan</p>";
    } else {
        echo "<p style='color: red;'>✗ Nuk mund të ndryshohen lejet e drejtorisë</p>";
    }
}

// Check comments file
if (file_exists($commentsFile)) {
    if (is_writable($commentsFile)) {
        echo "<p style='color: green;'>✓ Skedari i komenteve është i shkrueshëm</p>";
    } else {
        echo "<p style='color: red;'>✗ Skedari i komenteve nuk është i shkrueshëm</p>";
        if (chmod($commentsFile, 0644)) {
            echo "<p style='color: green;'>✓ Lejet e skedarit u ndryshuan</p>";
        }
    }
} else {
    echo "<p style='color: yellow;'>! Skedari i komenteve nuk ekziston (do të krijohet automatikisht)</p>";
}

// Show current directory permissions
echo "<h3>Informacioni i Sistemit:</h3>";
echo "<p>Direktoria aktuale: " . realpath('.') . "</p>";
echo "<p>Përdoruesi i serverit: " . get_current_user() . "</p>";
echo "<p>Lejet e drejtorisë uploads: " . (is_dir($uploadsDir) ? decoct(fileperms($uploadsDir) & 0777) : 'N/A') . "</p>";

echo "<br><a href='dashboard.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kthehu në Dashboard</a>";
?>
