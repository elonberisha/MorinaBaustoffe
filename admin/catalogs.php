<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$catalogsFile = __DIR__ . '/catalogs.json';

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

$data = load_data($catalogsFile);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name'] ?? '');
        if ($name !== '') {
            $slug = slugify($name);
            // ensure unique slug
            $existing = array_column($data['categories'], 'slug');
            if (in_array($slug, $existing)) {
                $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
            }
            $data['categories'][] = [ 'name' => $name, 'slug' => $slug, 'catalogs' => [] ];
            save_data($catalogsFile, $data);
            $message = 'Kategoria u shtua.';
        }
    }

    if (isset($_POST['add_catalog'])) {
        $catSlug = $_POST['parent_category'] ?? '';
        $name = trim($_POST['catalog_name'] ?? '');
        if ($catSlug && $name !== '') {
            foreach ($data['categories'] as &$cat) {
                if ($cat['slug'] === $catSlug) {
                    $slug = slugify($name);
                    $existing = array_column($cat['catalogs'], 'slug');
                    if (in_array($slug, $existing)) {
                        $slug .= '-' . substr(md5(uniqid('', true)), 0, 4);
                    }
                    $cat['catalogs'][] = [ 'name' => $name, 'slug' => $slug ];
                    save_data($catalogsFile, $data);
                    $message = 'Katalogu u shtua.';
                    break;
                }
            }
            unset($cat);
        }
    }

    if (isset($_POST['delete_category'])) {
        $catSlug = $_POST['cat_slug'] ?? '';
        if ($catSlug) {
            $data['categories'] = array_values(array_filter($data['categories'], function($c) use ($catSlug) { return $c['slug'] !== $catSlug; }));
            save_data($catalogsFile, $data);
            $message = 'Kategoria u fshi.';
        }
    }

    if (isset($_POST['delete_catalog'])) {
        $catSlug = $_POST['cat_slug'] ?? '';
        $catalogSlug = $_POST['catalog_slug'] ?? '';
        if ($catSlug && $catalogSlug) {
            foreach ($data['categories'] as &$cat) {
                if ($cat['slug'] === $catSlug) {
                    $cat['catalogs'] = array_values(array_filter($cat['catalogs'], function($k) use ($catalogSlug) { return $k['slug'] !== $catalogSlug; }));
                    break;
                }
            }
            unset($cat);
            save_data($catalogsFile, $data);
            $message = 'Katalogu u fshi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menaxho Kategori & Katalogë</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body{font-family: Poppins, Arial, sans-serif; background:#f5f7fb; color:#222}
    .container{max-width:1100px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.08); padding:24px}
    h1{margin:0 0 16px}
    .grid{display:grid; grid-template-columns:1fr 1fr; gap:20px}
    .card{background:#fff; border:1px solid #eee; border-radius:12px; padding:16px}
    .list{margin:0; padding:0; list-style:none}
    .row{display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px dashed #eee}
    .row:last-child{border-bottom:none}
    .muted{color:#666}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; border:1px solid #ddd; background:#fff; cursor:pointer}
    .btn-primary{background:linear-gradient(135deg,#667eea,#764ba2); border:none; color:#fff}
    .btn-danger{background:#ff6b6b; border:none; color:#fff}
    .form-group{display:flex; gap:8px; margin:10px 0}
    input,select{padding:10px; border:1px solid #ddd; border-radius:8px; width:100%}
    .message{margin:10px 0; color:green}
    .top-actions{display:flex; gap:10px; margin-bottom:10px}
  </style>
</head>
<body>
  <div class="container">
    <div class="top-actions">
      <a href="dashboard.php" class="btn"><i class="fa fa-arrow-left"></i> Dashboard</a>
      <a href="../index.php#products" class="btn"><i class="fa fa-store"></i> Shiko Faqen</a>
    </div>
    <h1><i class="fa fa-sitemap"></i> Kategori & Katalogë</h1>
    <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3>Shto Kategori</h3>
        <form method="post">
          <div class="form-group">
            <input type="text" name="category_name" placeholder="Emri i kategorisë p.sh. Bojrat" required>
          </div>
          <button class="btn btn-primary" name="add_category"><i class="fa fa-plus"></i> Shto</button>
        </form>
        <hr>
        <h3>Shto Katalog</h3>
        <form method="post">
          <div class="form-group">
            <select name="parent_category" required>
              <option value="">Zgjidh Kategorinë</option>
              <?php foreach ($data['categories'] as $c): ?>
                <option value="<?php echo htmlspecialchars($c['slug']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <input type="text" name="catalog_name" placeholder="Emri i katalogut p.sh. Bojrat e fasadës" required>
          </div>
          <button class="btn btn-primary" name="add_catalog"><i class="fa fa-plus"></i> Shto</button>
        </form>
      </div>

      <div class="card">
        <h3>Lista</h3>
        <ul class="list">
          <?php foreach ($data['categories'] as $cat): ?>
            <li>
              <div class="row">
                <div>
                  <strong><?php echo htmlspecialchars($cat['name']); ?></strong> <span class="muted">(<?php echo htmlspecialchars($cat['slug']); ?>)</span>
                </div>
                <form method="post" onsubmit="return confirm('Fshi kategorinë dhe të gjithë katalogët?')">
                  <input type="hidden" name="cat_slug" value="<?php echo htmlspecialchars($cat['slug']); ?>">
                  <button class="btn btn-danger" name="delete_category"><i class="fa fa-trash"></i></button>
                </form>
              </div>
              <?php if (!empty($cat['catalogs'])): ?>
                <ul class="list" style="margin-left:14px">
                  <?php foreach ($cat['catalogs'] as $k): ?>
                    <li>
                      <div class="row">
                        <div>
                          - <?php echo htmlspecialchars($k['name']); ?> <span class="muted">(<?php echo htmlspecialchars($k['slug']); ?>)</span>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                          <a class="btn" href="edit_catalog.php?category=<?php echo urlencode($cat['slug']); ?>&catalog=<?php echo urlencode($k['slug']); ?>">
                            <i class="fa fa-pen"></i> Edito
                          </a>
                          <form method="post" onsubmit="return confirm('Fshi këtë katalog?')">
                            <input type="hidden" name="cat_slug" value="<?php echo htmlspecialchars($cat['slug']); ?>">
                            <input type="hidden" name="catalog_slug" value="<?php echo htmlspecialchars($k['slug']); ?>">
                            <button class="btn btn-danger" name="delete_catalog"><i class="fa fa-trash"></i></button>
                          </form>
                        </div>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
