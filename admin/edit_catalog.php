<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$catalogsFile = __DIR__ . '/catalogs.json';
$uploadsBase  = __DIR__ . '/uploads/';

function load_data($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) return $data;
    }
    return [ 'categories' => [] ];
}
function save_data($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    @chmod($file, 0644);
}
function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: 'item';
}

$category = $_GET['category'] ?? '';
$catalog  = $_GET['catalog'] ?? '';
$data = load_data($catalogsFile);

// Find references
$catRef = null; $catIndex = null; $catalogRef = null; $catalogIndex = null;
foreach ($data['categories'] as $i => $c) {
    if ($c['slug'] === $category) {
        $catRef = &$data['categories'][$i];
        $catIndex = $i;
        foreach ($c['catalogs'] as $j => $k) {
            if ($k['slug'] === $catalog) {
                $catalogRef = &$data['categories'][$i]['catalogs'][$j];
                $catalogIndex = $j;
                break 2;
            }
        }
    }
}

if (!$catRef || !$catalogRef) {
    http_response_code(404);
    echo 'Kategoria ose katalogu nuk u gjet.';
    exit;
}

$message = $error = '';

// Handle rename
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_catalog'])) {
    $newName = trim($_POST['new_name'] ?? '');
    if ($newName !== '') {
        $oldSlug = $catalogRef['slug'];
        $newSlug = slugify($newName);
        // Ensure unique among catalogs in this category
        $existing = array_column($catRef['catalogs'], 'slug');
        if (in_array($newSlug, $existing) && $newSlug !== $oldSlug) {
            $newSlug .= '-' . substr(md5(uniqid('', true)), 0, 4);
        }
        $oldPath = $uploadsBase . $category . '/' . $oldSlug;
        $newPath = $uploadsBase . $category . '/' . $newSlug;
        // Rename folder if exists and slug changed
        if ($oldSlug !== $newSlug && is_dir($oldPath)) {
            if (!is_dir(dirname($newPath))) { @mkdir(dirname($newPath), 0755, true); }
            if (!@rename($oldPath, $newPath)) {
                $error = 'Nuk u arrit të riemërtohet drejtorja e ngarkimeve.';
            }
        }
        if (!$error) {
            $catalogRef['name'] = $newName;
            $catalogRef['slug'] = $newSlug;
            save_data($catalogsFile, $data);
            $catalog = $newSlug; // update query context
            $message = 'Katalogu u përditësua.';
        }
    }
}

// Image operations (upload/delete) scoped to this catalog
$targetDir = $uploadsBase . $category . '/' . $catalog . '/';
if (!is_dir($targetDir)) { @mkdir($targetDir, 0755, true); }
$commentsFile = $targetDir . 'image_comments.json';
$comments = file_exists($commentsFile) ? (json_decode(file_get_contents($commentsFile), true) ?: []) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    if (!empty($_FILES['image']['name'])) {
        $fileName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','bmp','svg','tiff','ico'];
        if (!in_array($ext, $allowed)) {
            $error = 'Lloj skedari i palejuar.';
        } else if ($_FILES['image']['size'] > 10000000) {
            $error = 'Skedari është shumë i madh (10MB max).';
        } else {
            $base = pathinfo($fileName, PATHINFO_FILENAME);
            $unique = $base . '_' . time() . '.' . $ext;
            $dest = $targetDir . $unique;
            if ($ext !== 'svg') {
                $check = @getimagesize($_FILES['image']['tmp_name']);
                if ($check === false) $error = 'Skedari nuk është imazh i vlefshëm.';
            }
            if (!$error) {
                if (@move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    @chmod($dest, 0644);
                    $desc = trim($_POST['description'] ?? '');
                    if ($desc !== '') {
                        $comments[$unique] = $desc;
                        file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
                        @chmod($commentsFile, 0644);
                    }
                    $message = 'Imazhi u ngarkua.';
                } else {
                    $error = 'Dështoi ngarkimi i imazhit.';
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $filename = $_POST['filename'] ?? '';
    $path = $targetDir . $filename;
    if ($filename && file_exists($path)) {
        @unlink($path);
        unset($comments[$filename]);
        file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
        $message = 'Imazhi u fshi.';
    }
}

// Refresh listing
$images = [];
if (is_dir($targetDir)) {
    $files = array_diff(@scandir($targetDir), ['.', '..', 'image_comments.json']);
    foreach ($files as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg','tiff','ico'])) $images[] = $f;
    }
    usort($images, function($a,$b) use ($targetDir){ return filemtime($targetDir.$b) - filemtime($targetDir.$a); });
}

?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edito Katalogun</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body{font-family:Poppins, Arial, sans-serif; background:#f5f7fb; color:#222}
    .container{max-width:1100px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.08); padding:24px}
    .top{display:flex; gap:10px; align-items:center; margin-bottom:10px}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; border:1px solid #ddd; background:#fff; text-decoration:none; color:#222}
    .btn-primary{background:linear-gradient(135deg,#667eea,#764ba2); border:none; color:#fff}
    .btn-danger{background:#ff6b6b; border:none; color:#fff}
    .message{margin:10px 0; padding:10px 12px; border-radius:8px}
    .success{background:#e6ffed; color:#046c4e; border:1px solid #abefc6}
    .error{background:#ffe6e6; color:#8b0000; border:1px solid #f6bcbc}
    .grid{display:grid; grid-template-columns:1fr; gap:20px}
    .card{background:#fff; border:1px solid #eee; border-radius:12px; padding:16px}
    .form-row{display:flex; gap:8px; align-items:center}
    input[type=text]{padding:10px; border:1px solid #ddd; border-radius:8px; width:100%}
    .upload-form{display:grid; gap:12px;}
    .gallery{display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:14px}
    .item{background:#fff; border:1px solid #eee; border-radius:10px; overflow:hidden}
    .item img{width:100%; height:170px; object-fit:cover}
    .item-body{padding:10px}
    .comment{width:100%; padding:8px; border:1px solid #ddd; border-radius:8px}
    .actions{display:flex; gap:8px; margin-top:8px}
  </style>
</head>
<body>
  <div class="container">
    <div class="top">
      <a href="catalogs.php" class="btn"><i class="fa fa-arrow-left"></i> Kthehu</a>
      <a href="gallery.php?category=<?php echo urlencode($category); ?>&catalog=<?php echo urlencode($catalog); ?>" class="btn"><i class="fa fa-images"></i> Hap Menaxhimin e Imazheve</a>
      <a href="../index.php#products" class="btn"><i class="fa fa-store"></i> Shiko Faqen</a>
    </div>

    <h1><i class="fa fa-book-open"></i> Edito Katalogun</h1>
    <?php if ($message): ?><div class="message success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3><i class="fa fa-pen"></i> Riemërto</h3>
        <form method="post" class="form-row">
          <input type="text" name="new_name" value="<?php echo htmlspecialchars($catalogRef['name']); ?>" placeholder="Emri i ri i katalogut" required>
          <button class="btn btn-primary" name="rename_catalog"><i class="fa fa-save"></i> Ruaj</button>
        </form>
        <p style="margin-top:6px; color:#666">Slug aktual: <code><?php echo htmlspecialchars($catalogRef['slug']); ?></code></p>
      </div>

      <div class="card">
        <h3><i class="fa fa-upload"></i> Shto imazh</h3>
        <form method="post" enctype="multipart/form-data" class="upload-form">
          <input type="file" name="image" accept="image/*" required>
          <input type="text" name="description" class="comment" placeholder="Përshkrim (opsional)">
          <button class="btn btn-primary" name="upload_image"><i class="fa fa-upload"></i> Ngarko</button>
        </form>
      </div>

      <div class="card">
        <h3><i class="fa fa-images"></i> Imazhet</h3>
        <div class="gallery">
          <?php if (empty($images)): ?>
            <p style="color:#666">Nuk ka imazhe ende.</p>
          <?php endif; ?>
          <?php foreach ($images as $img): ?>
            <div class="item">
              <img src="uploads/<?php echo htmlspecialchars($category . '/' . $catalog . '/' . $img); ?>" alt="<?php echo htmlspecialchars($img); ?>">
              <div class="item-body">
                <form method="post">
                  <input type="hidden" name="filename" value="<?php echo htmlspecialchars($img); ?>">
                  <div class="actions">
                    <button class="btn btn-danger" name="delete_image" onclick="return confirm('Fshi imazhin?')"><i class="fa fa-trash"></i> Fshi</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
