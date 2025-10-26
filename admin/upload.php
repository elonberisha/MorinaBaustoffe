<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    die("Ndalohet qasja");
}

if (isset($_FILES['image'])) {
    $targetDir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $filename = basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . time() . "_" . $filename; // Add timestamp to avoid conflicts
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check === false) {
        echo "Skedari nuk është imazh.";
        exit;
    }
    
    // Check file size (limit to 5MB)
    if ($_FILES["image"]["size"] > 5000000) {
        echo "Skedari është shumë i madh.";
        exit;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "webp") {
        echo "Vetëm JPG, JPEG, PNG, GIF & WEBP janë të lejuara.";
        exit;
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        header("Location: dashboard.php");
    } else {
        echo "Ngarkimi dështoi.";
    }
}
?>
