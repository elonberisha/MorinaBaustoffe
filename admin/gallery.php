<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

// Handle form submissions
// Load catalogs
$catalogsPath = __DIR__ . '/catalogs.json';
$catalogsData = [ 'categories' => [] ];
if (file_exists($catalogsPath)) {
    $json = file_get_contents($catalogsPath);
    $data = json_decode($json, true);
    if (is_array($data)) $catalogsData = $data;
}

// Selected filters (for browsing)
$selectedCategory = $_POST['filter_category'] ?? $_GET['category'] ?? '';
$selectedCatalog  = $_POST['filter_catalog']  ?? $_GET['catalog']  ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_image'])) {
        // Require category & catalog
        $categorySlug = $_POST['category_slug'] ?? '';
        $catalogSlug  = $_POST['catalog_slug'] ?? '';
        if (!$categorySlug || !$catalogSlug) {
            $error = "Zgjidh kategorinë dhe katalogun para ngarkimit.";
        }
        $target_dir = "uploads/{$categorySlug}/{$catalogSlug}/";
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $fileName = basename($_FILES["image"]["name"]);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);
        $uniqueFileName = $fileBaseName . '_' . time() . '.' . $fileExtension;
    $target_file = $target_dir . $uniqueFileName;
        
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Extended list of supported image types
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'ico');
        
        if (in_array($fileExtension, $allowedTypes)) {
            // Check file size (10MB limit)
            if ($_FILES["image"]["size"] > 10000000) {
                $error = "Skedari është shumë i madh. Maksimumi është 10MB.";
            } else {
                // Verify it's actually an image (except SVG)
                if ($fileExtension !== 'svg') {
                    $check = getimagesize($_FILES["image"]["tmp_name"]);
                    if ($check === false) {
                        $error = "Skedari nuk është imazh i vlefshëm.";
                    } else {
                        $uploadSuccess = true;
                    }
                } else {
                    $uploadSuccess = true;
                }
                
                if (isset($uploadSuccess) && $uploadSuccess) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        chmod($target_file, 0644);
                        
                        // Save description if provided
                        if (!empty($description)) {
                            $commentsFile = $target_dir . "image_comments.json";
                            $comments = [];
                            
                            if (file_exists($commentsFile)) {
                                $commentsData = file_get_contents($commentsFile);
                                $comments = json_decode($commentsData, true) ?: [];
                            }
                            
                            $comments[$uniqueFileName] = $description;
                            file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
                            chmod($commentsFile, 0644);
                        }
                        
                        $message = "Imazhi u ngarkua me sukses.";
                    } else {
                        $error = "Gabim gjatë ngarkimit të imazhit. Kontrolloni lejet e drejtorisë.";
                    }
                }
            }
        } else {
            $error = "Lloji i skedarit nuk është i lejuar. Llojet e lejuara: " . implode(', ', $allowedTypes);
        }
    }
    
    if (isset($_POST['update_comment'])) {
    $filename = $_POST['filename'];
    $comment = $_POST['comment'];
    $categorySlug = $_POST['category_slug'] ?? '';
    $catalogSlug  = $_POST['catalog_slug'] ?? '';
    $commentsFile = "uploads/{$categorySlug}/{$catalogSlug}/image_comments.json";
        $comments = [];
        
        if (file_exists($commentsFile)) {
            $commentsData = file_get_contents($commentsFile);
            $comments = json_decode($commentsData, true) ?: [];
        }
        
        $comments[$filename] = $comment;
        file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
        $message = "Komenti u përditësua me sukses.";
    }
    
    if (isset($_POST['delete_image'])) {
    $filename = $_POST['filename'];
    $categorySlug = $_POST['category_slug'] ?? '';
    $catalogSlug  = $_POST['catalog_slug'] ?? '';
    $filepath = "uploads/{$categorySlug}/{$catalogSlug}/" . $filename;
        
        if (file_exists($filepath)) {
            unlink($filepath);
            
            // Remove from comments file
            $commentsFile = "uploads/{$categorySlug}/{$catalogSlug}/image_comments.json";
            if (file_exists($commentsFile)) {
                $commentsData = file_get_contents($commentsFile);
                $comments = json_decode($commentsData, true) ?: [];
                unset($comments[$filename]);
                file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
            }
            
            $message = "Imazhi u fshi me sukses.";
        }
    }
}

// Load existing images and comments based on selected filters
$comments = [];
$images = [];
if ($selectedCategory && $selectedCatalog) {
    $dir = __DIR__ . "/uploads/{$selectedCategory}/{$selectedCatalog}/";
    if (file_exists($dir . 'image_comments.json')) {
        $commentsData = file_get_contents($dir . 'image_comments.json');
        $comments = json_decode($commentsData, true) ?: [];
    }
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..', 'image_comments.json']);
        foreach ($files as $file) {
            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($fileExtension, ['jpg','jpeg','png','gif','webp','bmp','svg','tiff','ico'])) {
                $images[] = $file;
            }
        }
        usort($images, function($a, $b) use ($dir) {
            return filemtime($dir . $b) - filemtime($dir . $a);
        });
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menaxhimi i Galerisë - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .upload-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .file-input {
            padding: 15px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .gallery-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .gallery-item-content {
            padding: 20px;
        }
        
        .comment-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
            resize: vertical;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .gallery-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #667eea;
            font-weight: bold;
        }
        
        .back-btn:hover {
            color: #764ba2;
        }
        
        /* Add modal styles */
        .fullscreen-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .modal-content img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 10px;
        }

        .close-btn {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 10000;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 30px;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }

        .nav-arrow:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .nav-arrow-left {
            left: 20px;
        }

        .nav-arrow-right {
            right: 20px;
        }

        .modal-caption {
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .gallery-item img {
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-item img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kthehu në Dashboard
        </a>
        
        <div class="header">
            <h1><i class="fas fa-images"></i> Menaxhimi i Produkteve</h1>
            <p>Zgjidh kategorinë dhe katalogun për të ngarkuar dhe menaxhuar imazhet</p>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="upload-section">
            <h2><i class="fas fa-upload"></i> Ngarko Imazh të Ri</h2>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="category_slug">Kategoria</label>
                    <select name="category_slug" id="category_slug" required>
                        <option value="">Zgjidh...</option>
                        <?php foreach ($catalogsData['categories'] as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['slug']); ?>" <?php echo ($selectedCategory===$c['slug']?'selected':''); ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="catalog_slug">Katalogu</label>
                    <select name="catalog_slug" id="catalog_slug" required>
                        <option value="">Zgjidh...</option>
                        <?php foreach ($catalogsData['categories'] as $c): if ($selectedCategory && $c['slug']!==$selectedCategory) continue; ?>
                            <?php foreach ($c['catalogs'] as $k): ?>
                                <option value="<?php echo htmlspecialchars($k['slug']); ?>" <?php echo ($selectedCatalog===$k['slug']?'selected':''); ?>><?php echo htmlspecialchars($k['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="file-input">
                    <input type="file" name="image" accept="image/*" required>
                    <p><i class="fas fa-cloud-upload-alt"></i> Zgjidh imazhin për ngarkimin</p>
                </div>
                <div class="form-group">
                    <label for="image-description">Përshkrimi i imazhit (opsional)</label>
                    <textarea name="description" id="image-description" class="comment-input" rows="3" placeholder="Shkruaj një përshkrim të shkurtër për imazhin..."></textarea>
                </div>
                <button type="submit" name="upload_image" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Ngarko Imazhin
                </button>
            </form>
        </div>
        
        <div class="gallery-section">
            <h2><i class="fas fa-edit"></i> Menaxho Imazhet Ekzistuese</h2>
            <form method="post" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap">
                <div>
                    <label for="filter_category">Kategoria</label>
                    <select name="filter_category" id="filter_category" onchange="this.form.submit()">
                        <option value="">Zgjidh...</option>
                        <?php foreach ($catalogsData['categories'] as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['slug']); ?>" <?php echo ($selectedCategory===$c['slug']?'selected':''); ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter_catalog">Katalogu</label>
                    <select name="filter_catalog" id="filter_catalog" onchange="this.form.submit()">
                        <option value="">Zgjidh...</option>
                        <?php foreach ($catalogsData['categories'] as $c): if ($selectedCategory && $c['slug']!==$selectedCategory) continue; ?>
                            <?php foreach ($c['catalogs'] as $k): ?>
                                <option value="<?php echo htmlspecialchars($k['slug']); ?>" <?php echo ($selectedCatalog===$k['slug']?'selected':''); ?>><?php echo htmlspecialchars($k['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selectedCategory && $selectedCatalog): ?>
                <a class="btn" style="text-decoration:none" href="?category=<?php echo urlencode($selectedCategory); ?>&catalog=<?php echo urlencode($selectedCatalog); ?>">Rifresko</a>
                <a class="btn" style="text-decoration:none" href="edit_catalog.php?category=<?php echo urlencode($selectedCategory); ?>&catalog=<?php echo urlencode($selectedCatalog); ?>"><i class="fa fa-pen"></i> Edito katalogun</a>
                <?php endif; ?>
            </form>
            <div class="gallery-grid">
                <?php if (!$selectedCategory || !$selectedCatalog): ?>
                    <p style="grid-column:1 / -1; color:#666;">Zgjidh një kategori dhe një katalog për të parë imazhet.</p>
                <?php endif; ?>
                <?php foreach ($images as $image): ?>
                    <div class="gallery-item">
                        <img src="uploads/<?php echo htmlspecialchars($selectedCategory . '/' . $selectedCatalog . '/' . $image); ?>" alt="<?php echo htmlspecialchars($image); ?>">
                        <div class="gallery-item-content">
                            <form method="POST">
                                <input type="hidden" name="category_slug" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                                <input type="hidden" name="catalog_slug" value="<?php echo htmlspecialchars($selectedCatalog); ?>">
                                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($image); ?>">
                                <input type="text" name="comment" class="comment-input" 
                                       placeholder="Shkruaj komentin për imazhin..." 
                                       value="<?php echo htmlspecialchars($comments[$image] ?? ''); ?>">
                                <div class="gallery-actions">
                                    <button type="submit" name="update_comment" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Ruaj Komentin
                                    </button>
                                    <button type="submit" name="delete_image" class="btn btn-danger" 
                                            onclick="return confirm('A jeni i sigurt që doni të fshini këtë imazh?')">
                                        <i class="fas fa-trash"></i> Fshi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
<script>
// Dynamic catalogs per category
const catalogsData = <?php echo json_encode($catalogsData, JSON_UNESCAPED_UNICODE); ?>;
const catSelectUpload = document.getElementById('category_slug');
const catalogSelectUpload = document.getElementById('catalog_slug');
function populateCatalogs(selectCat, selectCatalog) {
    if (!selectCat || !selectCatalog) return;
    const catSlug = selectCat.value;
    selectCatalog.innerHTML = '<option value="">Zgjidh...</option>';
    const cat = catalogsData.categories.find(c => c.slug === catSlug);
    if (cat) {
        cat.catalogs.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.slug; opt.textContent = k.name;
            selectCatalog.appendChild(opt);
        });
    }
}
if (catSelectUpload && catalogSelectUpload) {
    catSelectUpload.addEventListener('change', () => populateCatalogs(catSelectUpload, catalogSelectUpload));
}

// Gallery modal functionality
let currentImageIndex = 0;
let galleryImages = [];

// Initialize gallery images array
function initializeGallery() {
    const galleryItems = document.querySelectorAll('.gallery-item img');
    galleryImages = Array.from(galleryItems).map(img => ({
        src: img.src,
        alt: img.alt
    }));
}

// Open full screen modal
function openFullScreen(imageSrc, imageAlt) {
    const modal = document.getElementById('fullscreen-modal');
    const modalImg = document.getElementById('modal-image');
    const modalCaption = document.getElementById('modal-caption');
    
    // Find current image index
    currentImageIndex = galleryImages.findIndex(img => img.src === imageSrc);
    
    modal.style.display = 'flex';
    modalImg.src = imageSrc;
    modalImg.alt = imageAlt;
    modalCaption.textContent = imageAlt;
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Close full screen modal
function closeFullScreen() {
    const modal = document.getElementById('fullscreen-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Navigate to previous image
function previousImage() {
    if (galleryImages.length > 0) {
        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
        updateModalImage();
    }
}

// Navigate to next image
function nextImage() {
    if (galleryImages.length > 0) {
        currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
        updateModalImage();
    }
}

// Update modal image
function updateModalImage() {
    const modalImg = document.getElementById('modal-image');
    const modalCaption = document.getElementById('modal-caption');
    const currentImage = galleryImages[currentImageIndex];
    
    modalImg.src = currentImage.src;
    modalImg.alt = currentImage.alt;
    modalCaption.textContent = currentImage.alt;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('fullscreen-modal');
    if (modal.style.display === 'flex') {
        if (e.key === 'ArrowLeft') {
            previousImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'Escape') {
            closeFullScreen();
        }
    }
});

// Initialize gallery when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeGallery();
    
    // Add click event listeners to gallery images
    const galleryItems = document.querySelectorAll('.gallery-item img');
    galleryItems.forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            openFullScreen(this.src, this.alt);
        });
    });
    
    // Create modal if it doesn't exist
    if (!document.getElementById('fullscreen-modal')) {
        const modal = document.createElement('div');
        modal.id = 'fullscreen-modal';
        modal.className = 'fullscreen-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-btn" onclick="closeFullScreen()">&times;</span>
                <div class="nav-arrow nav-arrow-left" onclick="previousImage()">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="nav-arrow nav-arrow-right" onclick="nextImage()">
                    <i class="fas fa-chevron-right"></i>
                </div>
                <img id="modal-image" src="" alt="" />
                <div class="modal-caption" id="modal-caption"></div>
            </div>
        `;
        document.body.appendChild(modal);
    }
});
</script>
</html>
