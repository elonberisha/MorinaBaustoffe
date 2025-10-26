<?php
// products.php - Shfaq fotot për një katalog të zgjedhur
$catalogsPath = __DIR__ . '/admin/catalogs.json';
$uploadsBase = __DIR__ . '/admin/uploads/';

$category = isset($_GET['category']) ? preg_replace('~[^a-z0-9\-]+~', '', strtolower($_GET['category'])) : '';
$catalog  = isset($_GET['catalog'])  ? preg_replace('~[^a-z0-9\-]+~', '', strtolower($_GET['catalog'])) : '';

$catalogsData = [ 'categories' => [] ];
if (file_exists($catalogsPath)) {
  $json = file_get_contents($catalogsPath);
  $data = json_decode($json, true);
  if (is_array($data)) $catalogsData = $data;
}

// Gjej emrat miqësorë
$categoryName = $category;
$catalogName  = $catalog;
foreach ($catalogsData['categories'] as $c) {
  if ($c['slug'] === $category) {
    $categoryName = $c['name'];
    foreach ($c['catalogs'] as $k) {
      if ($k['slug'] === $catalog) {
        $catalogName = $k['name'];
        break;
      }
    }
    break;
  }
}

$targetDir = $uploadsBase . $category . '/' . $catalog . '/';
$webBase   = 'admin/uploads/' . $category . '/' . $catalog . '/';
$commentsFile = $targetDir . 'image_comments.json';
$comments = [];
if (file_exists($commentsFile)) {
  $commentsData = file_get_contents($commentsFile);
  $comments = json_decode($commentsData, true) ?: [];
}

$images = [];
if (is_dir($targetDir)) {
  $files = array_diff(@scandir($targetDir), ['.', '..', 'image_comments.json']);
  foreach ($files as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg','tiff','ico'])) {
      $images[] = $file;
    }
  }
}
?><!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($categoryName . ' • ' . $catalogName); ?> | Produktet</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
  <nav class="navbar">
    <a href="index.php" class="logo">
      <img src="logo.svg" alt="Morina Baustoffe Logo" class="logo-img"> Morina Baustoffe
    </a>
    <ul class="nav-links">
      <li><a href="index.php#services">Shërbimet</a></li>
      <li><a href="index.php#products">Produktet</a></li>
      <li><a href="index.php#why-us">Pse Ne</a></li>
      <li><a href="index.php#contact">Kontakt</a></li>
    </ul>
    <div class="mobile-menu"><i class="fas fa-bars"></i></div>
  </nav>

  <section class="viber-gallery" style="padding-top:120px">
    <div class="gallery-header">
      <h2 class="gallery-title"><?php echo htmlspecialchars($categoryName); ?> — <?php echo htmlspecialchars($catalogName); ?></h2>
      <p style="text-align:center; color:#666; margin-top:8px">
        <a href="index.php#products" style="color:#00c3ff; text-decoration:none"><i class="fa fa-arrow-left"></i> Kthehu te Produktet</a>
      </p>
    </div>
    <div class="gallery-container">
      <div class="gallery-grid" id="gallery-grid">
        <?php if (!empty($images)): ?>
          <?php foreach ($images as $file): $comment = $comments[$file] ?? pathinfo($file, PATHINFO_FILENAME); ?>
            <div class="gallery-item" onclick="openFullScreen('<?php echo $webBase . rawurlencode($file); ?>', '<?php echo htmlspecialchars($comment, ENT_QUOTES); ?>')">
              <img src="<?php echo $webBase . htmlspecialchars($file); ?>" alt="<?php echo htmlspecialchars($comment); ?>" loading="lazy" />
              <div class="gallery-overlay"><h3><?php echo htmlspecialchars($comment); ?></h3></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; color:#666; grid-column:1 / -1;">Nuk ka imazhe në këtë katalog ende.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Full Screen Modal (reuse) -->
  <div id="fullscreen-modal" class="fullscreen-modal">
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
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="script.js"></script>
</body>
</html>
