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

$clientsFile = __DIR__ . '/clients.json';
$logoStorageDir = __DIR__ . '/uploads/clients/';
$logoRelativePrefix = 'admin/uploads/clients/';

if (!is_dir($logoStorageDir)) {
  mkdir($logoStorageDir, 0755, true);
}

function load_clients(string $file): array {
  if (file_exists($file)) {
    $decoded = json_decode(file_get_contents($file), true);
    if (is_array($decoded)) {
      $decoded['clients'] = $decoded['clients'] ?? [];
      foreach ($decoded['clients'] as &$client) {
        if (!isset($client['logo'])) {
          $client['logo'] = '';
        }
      }
      unset($client);
      return $decoded;
    }
  }
  return ['clients' => []];
}

function save_clients(string $file, array $data): void {
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

  $fileName = 'logo_' . uniqid('', true) . '.' . $extension;
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
  if (strpos($normalized, 'admin/uploads/clients/') !== 0) {
    return;
  }

  $localPath = substr($normalized, strlen('admin/'));
  $fullPath = realpath(__DIR__ . '/' . $localPath);
  $storageRoot = realpath($storageDir);

  if ($fullPath && $storageRoot && strpos($fullPath, $storageRoot) === 0 && file_exists($fullPath)) {
    @unlink($fullPath);
  }
}

function client_logo_url(?string $storedPath): ?string {
  if (!$storedPath) {
    return null;
  }
  if (preg_match('/^https?:\/\//i', $storedPath)) {
    return $storedPath;
  }
  return '../' . ltrim($storedPath, '/');
}

$data = load_clients($clientsFile);
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_client'])) {
    $name = trim($_POST['client_name'] ?? '');
    $order = (int)($_POST['client_order'] ?? 0);
    $upload = handle_logo_upload('client_logo', $logoStorageDir, $logoRelativePrefix);

    if ($upload['error']) {
      $message = $upload['error'];
      $messageType = 'error';
    } elseif ($name !== '') {
      if ($order <= 0) {
        $existingOrders = array_column($data['clients'], 'order');
        $order = empty($existingOrders) ? 1 : (max($existingOrders) + 1);
      }

      $data['clients'][] = [
        'id' => uniqid('client_', true),
        'name' => $name,
        'description' => '',
        'icon' => 'fas fa-user-friends',
        'logo' => $upload['path'] ?? '',
        'order' => $order
      ];
      save_clients($clientsFile, $data);
      $message = 'Klienti u shtua me sukses.';
      $messageType = 'success';
    } else {
      $message = 'Ju lutemi plotësoni emrin e klientit.';
      $messageType = 'error';
    }
  }

  if (isset($_POST['update_client'])) {
    $clientId = $_POST['client_id'] ?? '';
    $found = false;
    foreach ($data['clients'] as &$client) {
      if ($client['id'] === $clientId) {
        $found = true;
        $client['logo'] = $client['logo'] ?? '';
        $client['name'] = trim($_POST['client_name'] ?? $client['name']);
        $order = (int)($_POST['client_order'] ?? $client['order']);
        $client['order'] = $order > 0 ? $order : $client['order'];

        $upload = handle_logo_upload('client_logo', $logoStorageDir, $logoRelativePrefix);
        if ($upload['error']) {
          $message = $upload['error'];
          $messageType = 'error';
        } elseif ($upload['path']) {
          delete_logo_file($client['logo'] ?? null, $logoStorageDir);
          $client['logo'] = $upload['path'];
        }

        $message = $messageType === 'error' ? $message : 'Klienti u përditësua me sukses.';
        break;
      }
    }
    unset($client);

    if ($found && $messageType !== 'error') {
      save_clients($clientsFile, $data);
    } elseif (!$found) {
      $message = 'Klienti i kërkuar nuk u gjet.';
      $messageType = 'error';
    }
  }

  if (isset($_POST['delete_client'])) {
    $clientId = $_POST['client_id'] ?? '';
    $remaining = [];
    foreach ($data['clients'] as $client) {
      if ($client['id'] === $clientId) {
        delete_logo_file($client['logo'] ?? null, $logoStorageDir);
        continue;
      }
      $remaining[] = $client;
    }
    $data['clients'] = $remaining;
    save_clients($clientsFile, $data);
    $message = 'Klienti u fshi.';
    $messageType = 'success';
  }

  if ($message === '') {
    $message = 'Ndryshimet u ruajtën.';
    $messageType = 'success';
  }
}

$clients = $data['clients'];
usort($clients, function ($a, $b) {
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
  <title>Klientë Besnikë</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Poppins', Arial, sans-serif;
      background: radial-gradient(circle at top, #f8fafc 0%, #e2e8f0 60%, #cbd5f5 100%);
      color: #1e293b;
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
    label { font-weight: 600; font-size: 0.95rem; color: #334155; }
    input[type="text"], input[type="number"], input[type="file"] {
      padding: 11px 12px;
      border-radius: 12px;
      border: 1px solid rgba(148, 163, 184, 0.35);
      font-family: inherit;
      background: rgba(255, 255, 255, 0.92);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input[type="text"]:focus, input[type="number"]:focus, input[type="file"]:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.16);
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
    .client-row {
      display: grid;
      grid-template-columns: 110px minmax(0, 1fr);
      gap: 20px;
      padding: 18px;
      border-radius: 18px;
      border: 1px solid rgba(148, 163, 184, 0.25);
      background: rgba(248, 250, 252, 0.75);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    .client-order {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }
    .client-order label { font-size: 0.8rem; color: #64748b; }
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
    .client-details {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 14px;
    }
    .client-head {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .logo-preview {
      width: 88px;
      height: 88px;
      border-radius: 20px;
      background: rgba(241, 245, 249, 0.85);
      border: 1px solid rgba(148, 163, 184, 0.4);
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
      color: #475569;
    }
    .client-name input { font-size: 1rem; font-weight: 600; }
    .actions {
      display: flex;
      gap: 12px;
    }
    .actions button {
      flex: 1;
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
    small { color: #718096; font-size: 0.8rem; }
    @media (max-width: 992px) {
      .grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 620px) {
      .client-row { grid-template-columns: 1fr; }
      .client-order { flex-direction: row; justify-content: space-between; }
      .logo-preview { width: 70px; height: 70px; }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="top-actions">
      <a class="btn" href="dashboard.php"><i class="fas fa-arrow-left"></i> Paneli</a>
      <a class="btn" href="../index.php#clients" target="_blank"><i class="fas fa-eye"></i> Shiko seksionin</a>
    </div>
    <h1><i class="fas fa-handshake"></i> Klientë Besnikë</h1>
    <?php if ($message): ?>
      <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3>Shto Klient të Ri</h3>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="client_order">Renditja (01, 02...)</label>
            <input type="number" id="client_order" name="client_order" placeholder="p.sh. 1" min="1">
          </div>
          <div class="form-group">
            <label for="client_name">Emri i kompanisë</label>
            <input type="text" id="client_name" name="client_name" placeholder="p.sh. Morina Group" required>
          </div>
          <div class="form-group">
            <label for="client_logo">Logoja (SVG, PNG, JPG)</label>
            <input type="file" id="client_logo" name="client_logo" accept="image/*,.svg">
            <small>Logoja shfaqet në seksionin e klientëve dhe partnerëve. Madhësia maksimale 5MB.</small>
          </div>
          <button class="btn btn-primary" name="add_client" value="1"><i class="fas fa-plus"></i> Shto Klientin</button>
        </form>
      </div>

      <div class="card">
        <h3>Lista e Klientëve</h3>
        <div class="list">
          <?php if (empty($clients)): ?>
            <p>Asnjë klient nuk është regjistruar ende.</p>
          <?php else: ?>
            <?php foreach ($clients as $client): ?>
              <form method="post" enctype="multipart/form-data" class="client-row">
                <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client['id']); ?>">
                <div class="client-order">
                  <div class="order-pill"><?php echo str_pad((string)($client['order'] ?? 0), 2, '0', STR_PAD_LEFT); ?></div>
                  <label for="order_<?php echo htmlspecialchars($client['id']); ?>">Renditja</label>
                  <input type="number" id="order_<?php echo htmlspecialchars($client['id']); ?>" name="client_order" value="<?php echo htmlspecialchars($client['order'] ?? 1); ?>" min="1">
                </div>
                <div class="client-details">
                  <div class="client-head">
                    <div class="logo-preview">
                      <?php $logoUrl = client_logo_url($client['logo'] ?? null); ?>
                      <?php if ($logoUrl): ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logoja e klientit">
                      <?php else: ?>
                        <span class="fallback-icon"><i class="fas fa-user-friends"></i></span>
                      <?php endif; ?>
                    </div>
                    <div class="client-name">
                      <label for="name_<?php echo htmlspecialchars($client['id']); ?>">Emri</label>
                      <input type="text" id="name_<?php echo htmlspecialchars($client['id']); ?>" name="client_name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="logo_<?php echo htmlspecialchars($client['id']); ?>">Ndrysho logon</label>
                    <input type="file" id="logo_<?php echo htmlspecialchars($client['id']); ?>" name="client_logo" accept="image/*,.svg">
                    <small>Ngarko një logo të re për ta zëvendësuar të tanishmen.</small>
                  </div>
                  <div class="actions">
                    <button class="save" name="update_client" value="1"><i class="fas fa-save"></i> Ruaj</button>
                    <button class="delete" name="delete_client" value="1" onclick="return confirm('Je i sigurt që dëshiron ta fshish këtë klient?');"><i class="fas fa-trash-alt"></i> Fshi</button>
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
