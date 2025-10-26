<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Check for session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

// Update login time for activity tracking
$_SESSION['login_time'] = time();

$uploadsDir = "uploads/";
$commentsFile = "image_comments.json";
$message = '';
$messageType = '';

// Create uploads directory if it doesn't exist
if (!is_dir($uploadsDir)) {
    if (!mkdir($uploadsDir, 0755, true)) {
        $message = "Gabim në krijimin e drejtorisë së ngarkimeve!";
        $messageType = "error";
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $targetDir = $uploadsDir;
    $fileName = basename($_FILES["image"]["name"]);
    
    // Add timestamp to prevent filename conflicts
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);
    $uniqueFileName = $fileBaseName . '_' . time() . '.' . $fileExtension;
    
    $targetFilePath = $targetDir . $uniqueFileName;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Check if file is an image - expanded list of supported formats
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'ico');
    
    if (in_array($fileExtension, $allowedTypes)) {
        // Check file size (limit to 10MB)
        if ($_FILES["image"]["size"] > 10000000) {
            $message = "Skedari është shumë i madh. Maksimumi është 10MB.";
            $messageType = "error";
        } else {
            // Verify it's actually an image (except for SVG)
            if ($fileExtension !== 'svg') {
                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if ($check === false) {
                    $message = "Skedari nuk është imazh i vlefshëm.";
                    $messageType = "error";
                } else {
                    $uploadSuccess = true;
                }
            } else {
                $uploadSuccess = true;
            }
            
            if (isset($uploadSuccess) && $uploadSuccess) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    // Set proper permissions
                    chmod($targetFilePath, 0644);
                    
                    // Save description if provided
                    if (!empty($description)) {
                        $comments = [];
                        if (file_exists($commentsFile)) {
                            $commentsData = file_get_contents($commentsFile);
                            $comments = json_decode($commentsData, true) ?: [];
                        }
                        $comments[$uniqueFileName] = $description;
                        file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
                        chmod($commentsFile, 0644);
                    }
                    
                    $message = "Imazhi u ngarkua me sukses!";
                    $messageType = "success";
                } else {
                    $message = "Gabim gjatë ngarkimit të imazhit! Kontrolloni lejet e drejtorisë.";
                    $messageType = "error";
                }
            }
        }
    } else {
        $message = "Lloji i skedarit nuk është i lejuar. Llojet e lejuara: " . implode(', ', $allowedTypes);
        $messageType = "error";
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileToDelete = $uploadsDir . basename($_GET['delete']);
    if (file_exists($fileToDelete)) {
        if (unlink($fileToDelete)) {
            
            // Remove from comments file
            if (file_exists($commentsFile)) {
                $commentsData = file_get_contents($commentsFile);
                $comments = json_decode($commentsData, true) ?: [];
                $fileName = basename($_GET['delete']);
                unset($comments[$fileName]);
                file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
            }
            
            $message = "Imazhi u fshi me sukses!";
            $messageType = "success";
        } else {
            $message = "Gabim gjatë fshirjes së imazhit!";
            $messageType = "error";
        }
    }
}

// Get all images and their descriptions
$images = [];
$comments = [];

if (file_exists($commentsFile)) {
    $commentsData = file_get_contents($commentsFile);
    $comments = json_decode($commentsData, true) ?: [];
}

if (is_dir($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir), ['.', '..']);
    foreach ($files as $file) {
        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'ico'])) {
            $images[] = $file;
        }
    }
    // Sort images by modification time (newest first)
    usort($images, function($a, $b) use ($uploadsDir) {
        return filemtime($uploadsDir . $b) - filemtime($uploadsDir . $a);
    });
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Morina Baustoffe</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-light {
            background: white;
            color: #333;
        }

        .btn-light:hover {
            background: #f8f9fa;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            position: relative;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .gallery-item-overlay {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 0 0 0 10px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-item-overlay {
            opacity: 1;
        }

        .gallery-item-content {
            padding: 15px;
        }

        .gallery-item-name {
            font-size: 0.9rem;
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .gallery-item-description {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.4;
            margin-top: 5px;
        }

        .message {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-images {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <div class="header-actions">
                <a href="../index.php" class="btn btn-light">
                    <i class="fas fa-home"></i> Faqja Kryesore
                </a>
                <a href="catalogs.php" class="btn btn-light">
                    <i class="fas fa-sitemap"></i> Kategori & Katalogë
                </a>
                <a href="clients.php" class="btn btn-light">
                    <i class="fas fa-users"></i> Klientë Besnikë
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Dil
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($images); ?></div>
                <div class="stat-label">Imazhe në Galeri</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('d/m/Y'); ?></div>
                <div class="stat-label">Data e sotme</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2><i class="fas fa-upload"></i> Ngarko Imazh</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="image">Zgjidh imazhin</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*,.svg" required>
                        <small style="color: #666; font-size: 0.8rem;">Llojet e lejuara: JPG, PNG, GIF, WEBP, BMP, SVG, TIFF, ICO (max 10MB)</small>
                    </div>
                    <div class="form-group">
                        <label for="description">Përshkrimi i imazhit (opsional)</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Shkruaj një përshkrim të shkurtër për imazhin..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Ngarko
                    </button>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-info-circle"></i> Informacion</h2>
                <p>Mirësevini në panelin administrativ të Morina Baustoffe. Këtu mund të:</p>
                <ul style="margin-left: 20px; margin-top: 10px; color: #666;">
                    <li>Ngarkoni imazhe të reja në galeri</li>
                    <li>Shtoni përshkrime për imazhet</li>
                    <li>Fshini imazhet ekzistuese</li>
                    <li>Menaxhoni përmbajtjen e faqes</li>
                    <li>Mbështetja për të gjitha llojet e imazheve</li>
                </ul>
                <div style="margin-top: 15px; padding: 10px; background: #f0f8ff; border-radius: 5px;">
                    <strong>Status i sistemit:</strong><br>
                    <span style="color: <?php echo is_writable($uploadsDir) ? 'green' : 'red'; ?>;">
                        <?php echo is_writable($uploadsDir) ? '✓ Direktoria e ngarkimeve është e shkrueshme' : '✗ Direktoria e ngarkimeve nuk është e shkrueshme'; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-images"></i> Galeria e Imazheve</h2>
            <?php if (empty($images)): ?>
                <div class="no-images">
                    <i class="fas fa-image" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                    <p>Nuk ka imazhe të ngarkuara akoma.</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($images as $image): ?>
                        <div class="gallery-item">
                            <img src="uploads/<?php echo $image; ?>" alt="<?php echo $image; ?>">
                            <div class="gallery-item-overlay" onclick="deleteImage('<?php echo $image; ?>')">
                                <i class="fas fa-trash"></i>
                            </div>
                            <div class="gallery-item-content">
                                <div class="gallery-item-name">
                                    <?php echo pathinfo($image, PATHINFO_FILENAME); ?>
                                </div>
                                <?php if (isset($comments[$image]) && !empty($comments[$image])): ?>
                                    <div class="gallery-item-description">
                                        <?php echo htmlspecialchars($comments[$image]); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteImage(filename) {
            if (confirm('Jeni i sigurt që doni të fshini këtë imazh?')) {
                window.location.href = '?delete=' + encodeURIComponent(filename);
            }
        }
    </script>
</body>
</html>
