<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
  session_destroy();
  header('Location: login.php?timeout=1');
  exit;
}

$_SESSION['login_time'] = time();

$dataFile = __DIR__ . '/clients.json';
$logoStorageDir = __DIR__ . '/uploads/partners/';
$logoRelativePrefix = 'admin/uploads/partners/';

if (!is_dir($logoStorageDir)) {
  mkdir($logoStorageDir, 0755, true);
}

function load_registry(string $file): array {
  if (file_exists($file)) {
    $decoded = json_decode(file_get_contents($file), true);
    if (is_array($decoded)) {
      $decoded['clients'] = $decoded['clients'] ?? [];
      $decoded['partners'] = $decoded['partners'] ?? [];
      foreach ($decoded['partners'] as &$partner) {
        if (!isset($partner['logo'])) {
          $partner['logo'] = '';
        }
        if (!isset($partner['description'])) {
          $partner['description'] = '';
        }
        if (!isset($partner['highlight'])) {
          $partner['highlight'] = false;
        }
        if (!isset($partner['order'])) {
          $partner['order'] = 0;
        }
      }
      unset($partner);
      return $decoded;
    }
  }
  return ['clients' => [], 'partners' => []];
}

function save_registry(string $file, array $data): void {
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  @chmod($file, 0644);
}

function handle_logo_upload(string $fieldName, string $storageDir, string $relativePrefix): array {
  if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
    return ['path' => null, 'error' => null];
  }

  $file = $_FILES[$fieldName];
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return ['path' => null, 'error' => 'Ngarkimi i logos dështoi. Ju lutemi provoni përsëri.'];
  }

  $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  if (!in_array($extension, $allowedExtensions, true)) {
    return ['path' => null, 'error' => 'Formati i logos nuk lejohet. Përdorni JPG, PNG, WEBP ose SVG.'];
  }

  if ($extension !== 'svg' && $file['size'] > 5 * 1024 * 1024) {
    return ['path' => null, 'error' => 'Logoja është shumë e madhe. Maksimumi i lejuar është 5MB.'];
  }

  if ($extension !== 'svg') {
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
      return ['path' => null, 'error' => 'Skedari i ngarkuar nuk është imazh i vlefshëm.'];
    }
  }

  $fileName = 'partner_' . uniqid('', true) . '.' . $extension;
  $targetPath = rtrim($storageDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

  if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    return ['path' => null, 'error' => 'Logoja nuk u ruajt dot. Kontrolloni lejet e serverit.'];
  }

  @chmod($targetPath, 0644);

  return ['path' => $relativePrefix . $fileName, 'error' => null];
}

function delete_logo_file(?string $relativePath, string $storageDir): void {
  if (!$relativePath) {
    return;
  }

  $normalized = str_replace(['\\'], '/', $relativePath);
  if (strpos($normalized, 'admin/uploads/partners/') !== 0) {
    return;
  }

  $localPath = substr($normalized, strlen('admin/'));
  $fullPath = realpath(__DIR__ . '/' . $localPath);
  $storageRoot = realpath($storageDir);

  if ($fullPath && $storageRoot && strpos($fullPath, $storageRoot) === 0 && file_exists($fullPath)) {
    @unlink($fullPath);
  }
}

function partner_logo_url(?string $storedPath): ?string {
  if (!$storedPath) {
    return null;
  }
  if (preg_match('/^https?:\/\//i', $storedPath)) {
    return $storedPath;
  }
  return '../' . ltrim($storedPath, '/');
}

$data = load_registry($dataFile);
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_partner'])) {
    $name = trim($_POST['partner_name'] ?? '');
    $description = trim($_POST['partner_description'] ?? '');
    $order = (int)($_POST['partner_order'] ?? 0);
    $highlight = isset($_POST['partner_highlight']);
    $upload = handle_logo_upload('partner_logo', $logoStorageDir, $logoRelativePrefix);

    if ($upload['error']) {
      $message = $upload['error'];
      $messageType = 'error';
    } elseif ($name !== '') {
      if ($order <= 0) {
        $existingOrders = array_column($data['partners'], 'order');
        $order = empty($existingOrders) ? 1 : (max($existingOrders) + 1);
      }

      $data['partners'][] = [
        'id' => uniqid('partner_', true),
        'name' => $name,
        'description' => $description,
        'logo' => $upload['path'] ?? '',
        'highlight' => $highlight,
        'order' => $order
      ];
      save_registry($dataFile, $data);
      $message = 'Partneri u shtua me sukses.';
      $messageType = 'success';
    } else {
      $message = 'Ju lutemi plotësoni emrin e partnerit.';
      $messageType = 'error';
    }
  }

  if (isset($_POST['update_partner'])) {
    $partnerId = $_POST['partner_id'] ?? '';
    $found = false;
    foreach ($data['partners'] as &$partner) {
      if ($partner['id'] === $partnerId) {
        $found = true;
        $partner['name'] = trim($_POST['partner_name'] ?? $partner['name']);
        $partner['description'] = trim($_POST['partner_description'] ?? $partner['description'] ?? '');
        $order = (int)($_POST['partner_order'] ?? $partner['order']);
        $partner['order'] = $order > 0 ? $order : $partner['order'];
        $partner['highlight'] = isset($_POST['partner_highlight']);

        $upload = handle_logo_upload('partner_logo', $logoStorageDir, $logoRelativePrefix);
        if ($upload['error']) {
          $message = $upload['error'];
          $messageType = 'error';
        } elseif ($upload['path']) {
          delete_logo_file($partner['logo'] ?? null, $logoStorageDir);
          $partner['logo'] = $upload['path'];
        }

        $message = $messageType === 'error' ? $message : 'Partneri u përditësua me sukses.';
        break;
      }
    }
    unset($partner);

    if ($found && $messageType !== 'error') {
      save_registry($dataFile, $data);
    } elseif (!$found) {
      $message = 'Partneri i kërkuar nuk u gjet.';
      $messageType = 'error';
    }
  }

  if (isset($_POST['delete_partner'])) {
    $partnerId = $_POST['partner_id'] ?? '';
    $remaining = [];
    foreach ($data['partners'] as $partner) {
      if ($partner['id'] === $partnerId) {
        delete_logo_file($partner['logo'] ?? null, $logoStorageDir);
        continue;
      }
      $remaining[] = $partner;
    }
    $data['partners'] = $remaining;
    save_registry($dataFile, $data);
    $message = 'Partneri u fshi.';
    $messageType = 'success';
  }

  if ($message === '') {
    $message = 'Ndryshimet u ruajtën.';
    $messageType = 'success';
  }
}

$partners = $data['partners'];
usort($partners, function ($a, $b) {
  $orderA = $a['order'] ?? 0;
  $orderB = $b['order'] ?? 0;
  if ($orderA === $orderB) {
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
  }
  return $orderA <=> $orderB;
});
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Partnerë Strategjikë</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Poppins', Arial, sans-serif;
      background: radial-gradient(circle at top, #eef2ff 0%, #e0e7ff 40%, #c7d2fe 100%);
      color: #1e1b4b;
    }
    .wrapper {
      max-width: 1180px;
      margin: 40px auto;
      padding: 0 24px 48px;
    }
    .top-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 18px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 16px;
      border-radius: 10px;
      border: 1px solid rgba(15, 23, 42, 0.12);
      background: rgba(255, 255, 255, 0.85);
      color: inherit;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.25s ease;
    }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12); }
    .btn-primary {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border-color: transparent;
      color: #fff;
    }
    h1 {
      margin: 0 0 24px;
      font-size: 2rem;
      display: flex;
      align-items: center;
      gap: 12px;
      color: #0f172a;
    }
    .grid {
      display: grid;
      grid-template-columns: minmax(0, 360px) minmax(0, 1fr);
      gap: 28px;
      align-items: flex-start;
    }
    .card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 22px;
      padding: 26px;
      box-shadow: 0 26px 60px rgba(15, 23, 42, 0.12);
      border: 1px solid rgba(148, 163, 184, 0.25);
    }
    .card h3 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 1.2rem;
      color: #1f2937;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 16px;
    }
    label { font-weight: 600; font-size: 0.95rem; color: #312e81; }
    input[type="text"], input[type="number"], input[type="file"], textarea {
      padding: 11px 12px;
      border-radius: 12px;
      border: 1px solid rgba(148, 163, 184, 0.35);
      font-family: inherit;
      background: rgba(255, 255, 255, 0.92);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input[type="text"]:focus, input[type="number"]:focus, input[type="file"]:focus, textarea:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.16);
    }
    textarea { min-height: 110px; resize: vertical; }
    .toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.92rem;
      color: #4338ca;
    }
    .message {
      margin-bottom: 22px;
      padding: 14px 18px;
      border-radius: 14px;
      font-weight: 500;
    }
    .message.success {
      background: rgba(74, 222, 128, 0.2);
      border: 1px solid rgba(34, 197, 94, 0.3);
      color: #166534;
    }
    .message.error {
      background: rgba(248, 113, 113, 0.2);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #b91c1c;
    }
    .list {
      display: flex;
      flex-direction: column;
      gap: 18px;
      max-height: 650px;
      overflow-y: auto;
      padding-right: 6px;
    }
    .partner-row {
      display: grid;
      grid-template-columns: 110px minmax(0, 1fr);
      gap: 20px;
      padding: 18px;
      border-radius: 18px;
      border: 1px solid rgba(129, 140, 248, 0.25);
      background: rgba(237, 242, 255, 0.75);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    .partner-order {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }
    .partner-order label { font-size: 0.8rem; color: #6366f1; }
    .order-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 54px;
      height: 54px;
      border-radius: 16px;
      background: linear-gradient(145deg, rgba(99, 102, 241, 0.2), rgba(129, 140, 248, 0.28));
      color: #3730a3;
      font-weight: 700;
      font-size: 1.2rem;
    }
    .partner-details {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 14px;
    }
    .partner-head {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .logo-preview {
      width: 88px;
      height: 88px;
      border-radius: 20px;
      background: rgba(224, 231, 255, 0.85);
      border: 1px solid rgba(129, 140, 248, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .logo-preview img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }
    .logo-preview .fallback-icon {
      font-size: 2rem;
      color: #4c51bf;
    }
    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    .actions button {
      flex: 1 1 160px;
      padding: 11px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .actions button:hover { transform: translateY(-1px); }
    .actions .save { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; }
    .actions .delete { background: linear-gradient(135deg, #f97316, #ef4444); color: #fff; }
    small { color: #6b7280; font-size: 0.8rem; }
    @media (max-width: 992px) {
      .grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 620px) {
      .partner-row { grid-template-columns: 1fr; }
      .partner-order { flex-direction: row; justify-content: space-between; }
      .logo-preview { width: 70px; height: 70px; }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="top-actions">
      <a class="btn" href="dashboard.php"><i class="fas fa-arrow-left"></i> Paneli</a>
      <a class="btn" href="../index.php#partners" target="_blank"><i class="fas fa-eye"></i> Shiko seksionin</a>
    </div>
    <h1><i class="fas fa-handshake"></i> Partnerë Strategjikë</h1>
    <?php if ($message): ?>
      <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3>Shto Partner të Ri</h3>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="partner_order">Renditja (01, 02...)</label>
            <input type="number" id="partner_order" name="partner_order" placeholder="p.sh. 1" min="1">
          </div>
          <div class="form-group">
            <label for="partner_name">Emri i partnerit</label>
            <input type="text" id="partner_name" name="partner_name" placeholder="p.sh. Bunjamini" required>
          </div>
          <div class="form-group">
            <label for="partner_description">Përshkrimi i shkurtër</label>
            <textarea id="partner_description" name="partner_description" placeholder="p.sh. Furnizues i materialeve premium"></textarea>
          </div>
          <div class="form-group">
            <label for="partner_logo">Logoja (SVG, PNG, JPG)</label>
            <input type="file" id="partner_logo" name="partner_logo" accept="image/*,.svg">
            <small>Logoja shfaqet në seksionin e partnerëve. Madhësia maksimale 5MB.</small>
          </div>
          <div class="form-group toggle">
            <input type="checkbox" id="partner_highlight" name="partner_highlight" value="1">
            <label for="partner_highlight">Shfaqe si partner të veçantë</label>
          </div>
          <button class="btn btn-primary" name="add_partner" value="1"><i class="fas fa-plus"></i> Shto Partnerin</button>
        </form>
      </div>

      <div class="card">
        <h3>Lista e Partnerëve</h3>
        <div class="list">
          <?php if (empty($partners)): ?>
            <p>Nuk ka partnerë të regjistruar ende.</p>
          <?php else: ?>
            <?php foreach ($partners as $partner): ?>
              <form method="post" enctype="multipart/form-data" class="partner-row">
                <input type="hidden" name="partner_id" value="<?php echo htmlspecialchars($partner['id']); ?>">
                <div class="partner-order">
                  <div class="order-pill"><?php echo str_pad((string)($partner['order'] ?? 0), 2, '0', STR_PAD_LEFT); ?></div>
                  <label for="order_<?php echo htmlspecialchars($partner['id']); ?>">Renditja</label>
                  <input type="number" id="order_<?php echo htmlspecialchars($partner['id']); ?>" name="partner_order" value="<?php echo htmlspecialchars($partner['order'] ?? 1); ?>" min="1">
                </div>
                <div class="partner-details">
                  <div class="partner-head">
                    <div class="logo-preview">
                      <?php $logoUrl = partner_logo_url($partner['logo'] ?? null); ?>
                      <?php if ($logoUrl): ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logoja e partnerit">
                      <?php else: ?>
                        <span class="fallback-icon"><i class="fas fa-handshake"></i></span>
                      <?php endif; ?>
                    </div>
                    <div class="partner-name">
                      <label for="name_<?php echo htmlspecialchars($partner['id']); ?>">Emri</label>
                      <input type="text" id="name_<?php echo htmlspecialchars($partner['id']); ?>" name="partner_name" value="<?php echo htmlspecialchars($partner['name']); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="description_<?php echo htmlspecialchars($partner['id']); ?>">Përshkrimi</label>
                    <textarea id="description_<?php echo htmlspecialchars($partner['id']); ?>" name="partner_description" placeholder="Përshkrim i partnerit"><?php echo htmlspecialchars($partner['description'] ?? ''); ?></textarea>
                  </div>
                  <div class="form-group">
                    <label for="logo_<?php echo htmlspecialchars($partner['id']); ?>">Ndrysho logon</label>
                    <input type="file" id="logo_<?php echo htmlspecialchars($partner['id']); ?>" name="partner_logo" accept="image/*,.svg">
                    <small>Ngarko një logo të re për ta zëvendësuar të tanishmen.</small>
                  </div>
                  <div class="form-group toggle">
                    <input type="checkbox" id="highlight_<?php echo htmlspecialchars($partner['id']); ?>" name="partner_highlight" value="1" <?php echo !empty($partner['highlight']) ? 'checked' : ''; ?>>
                    <label for="highlight_<?php echo htmlspecialchars($partner['id']); ?>">Shfaqe si partner të veçantë</label>
                  </div>
                  <div class="actions">
                    <button class="save" name="update_partner" value="1"><i class="fas fa-save"></i> Ruaj</button>
                    <button class="delete" name="delete_partner" value="1" onclick="return confirm('Je i sigurt që dëshiron ta fshish këtë partner?');"><i class="fas fa-trash-alt"></i> Fshi</button>
                  </div>
                </div>
              </form>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
