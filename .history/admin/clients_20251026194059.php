<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$clientsFile = __DIR__ . '/clients.json';

function load_clients($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $data['clients'] = $data['clients'] ?? [];
            return $data;
        }
    }
    return [ 'clients' => [] ];
}

function save_clients($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    @chmod($file, 0644);
}

$data = load_clients($clientsFile);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_client'])) {
        $name = trim($_POST['client_name'] ?? '');
        $description = trim($_POST['client_description'] ?? '');
        $icon = trim($_POST['client_icon'] ?? '');
        $order = (int)($_POST['client_order'] ?? 0);
        if ($name !== '') {
            if ($order <= 0) {
                $existingOrders = array_column($data['clients'], 'order');
                $order = empty($existingOrders) ? 1 : (max($existingOrders) + 1);
            }
            $data['clients'][] = [
                'id' => uniqid('client_', true),
                'name' => $name,
                'description' => $description,
                'icon' => $icon !== '' ? $icon : 'fas fa-user-friends',
                'order' => $order
            ];
            save_clients($clientsFile, $data);
            $message = 'Klienti u shtua me sukses.';
        }
    }

    if (isset($_POST['update_client'])) {
        $clientId = $_POST['client_id'] ?? '';
        foreach ($data['clients'] as &$client) {
            if ($client['id'] === $clientId) {
                $client['name'] = trim($_POST['client_name'] ?? $client['name']);
                $client['description'] = trim($_POST['client_description'] ?? $client['description']);
                $client['icon'] = trim($_POST['client_icon'] ?? $client['icon']);
                $order = (int)($_POST['client_order'] ?? $client['order']);
                $client['order'] = $order > 0 ? $order : $client['order'];
                $message = 'Klienti u përditësua.';
                break;
            }
        }
        unset($client);
        save_clients($clientsFile, $data);
    }

    if (isset($_POST['delete_client'])) {
        $clientId = $_POST['client_id'] ?? '';
        $data['clients'] = array_values(array_filter($data['clients'], function($client) use ($clientId) {
            return $client['id'] !== $clientId;
        }));
        save_clients($clientsFile, $data);
        $message = 'Klienti u fshi.';
    }
}

$clients = $data['clients'];
usort($clients, function($a, $b) {
    $orderA = $a['order'] ?? 0;
    $orderB = $b['order'] ?? 0;
    if ($orderA == $orderB) {
        return strcmp($a['name'] ?? '', $b['name'] ?? '');
    }
    return ($orderA <=> $orderB);
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
    body{font-family: Poppins, Arial, sans-serif; background:#f5f7fb; color:#222}
    .container{max-width:1100px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.08); padding:24px}
    h1{margin:0 0 16px}
    .top-actions{display:flex; gap:10px; margin-bottom:16px}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:8px; border:1px solid #ddd; background:#fff; cursor:pointer; text-decoration:none; color:#222}
    .btn-primary{background:linear-gradient(135deg,#667eea,#764ba2); border:none; color:#fff}
    .btn-danger{background:#ff6b6b; border:none; color:#fff}
    .grid{display:grid; grid-template-columns:1fr 1fr; gap:24px}
    .card{background:#fff; border:1px solid #eee; border-radius:12px; padding:18px}
    .form-group{display:flex; flex-direction:column; gap:6px; margin-bottom:12px}
    label{font-weight:600}
    input, textarea{padding:10px; border:1px solid #ddd; border-radius:8px; font-family:inherit; width:100%}
    textarea{min-height:90px; resize:vertical}
    .message{margin-bottom:16px; color:green}
    .list{display:flex; flex-direction:column; gap:16px; max-height:600px; overflow:auto; padding-right:4px}
    .client-row{display:grid; grid-template-columns:80px 1fr; gap:12px; border:1px solid #eee; border-radius:12px; padding:16px}
    .client-row header{display:flex; align-items:center; gap:12px}
    .client-row header .order{font-size:1.4rem; font-weight:700; color:#667eea}
    .client-row header .icon-preview{width:48px; height:48px; border-radius:12px; background:#f1f3ff; display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:#4a4e91}
    .client-row .actions{display:flex; gap:10px}
    .client-row .actions button{flex:1; padding:10px; border-radius:8px; border:none; cursor:pointer}
    .client-row .actions .save{background:linear-gradient(135deg,#00b09b,#96c93d); color:#fff}
    .client-row .actions .delete{background:#ff6b6b; color:#fff}
  </style>
</head>
<body>
  <div class="container">
    <div class="top-actions">
      <a href="dashboard.php" class="btn"><i class="fa fa-arrow-left"></i> Dashboard</a>
      <a href="../index.php#clients" class="btn"><i class="fa fa-eye"></i> Shiko Faqen</a>
    </div>
    <h1><i class="fa fa-handshake"></i> Klientë Besnikë</h1>
    <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3>Shto Klient të Ri</h3>
        <form method="post">
          <div class="form-group">
            <label>Renditja (01, 02...)</label>
            <input type="number" name="client_order" placeholder="p.sh. 1" min="1">
          </div>
          <div class="form-group">
            <label>Emri</label>
            <input type="text" name="client_name" placeholder="p.sh. Kompani Ndërtimi" required>
          </div>
          <div class="form-group">
            <label>Ikona (klasa Font Awesome)</label>
            <input type="text" name="client_icon" placeholder="p.sh. fas fa-building">
          </div>
          <div class="form-group">
            <label>Përshkrimi</label>
            <textarea name="client_description" placeholder="Shkruaj përmbledhje të shkurtër"></textarea>
          </div>
          <button class="btn btn-primary" name="add_client"><i class="fa fa-plus"></i> Shto</button>
        </form>
      </div>

      <div class="card">
        <h3>Lista e Klientëve</h3>
        <div class="list">
          <?php if (empty($clients)): ?>
            <p>Asnjë klient i shtuar ende.</p>
          <?php else: ?>
            <?php foreach ($clients as $client): ?>
              <form method="post" class="client-row">
                <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client['id']); ?>">
                <div>
                  <label>Renditja</label>
                  <input type="number" name="client_order" value="<?php echo htmlspecialchars($client['order'] ?? 1); ?>" min="1">
                </div>
                <div>
                  <header>
                    <div class="order"><?php echo str_pad((string)($client['order'] ?? 0), 2, '0', STR_PAD_LEFT); ?></div>
                    <div class="icon-preview"><i class="<?php echo htmlspecialchars($client['icon'] ?? 'fas fa-user-friends'); ?>"></i></div>
                    <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                  </header>
                  <div class="form-group" style="margin-top:12px;">
                    <label>Emri</label>
                    <input type="text" name="client_name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Ikona (Font Awesome)</label>
                    <input type="text" name="client_icon" value="<?php echo htmlspecialchars($client['icon'] ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Përshkrimi</label>
                    <textarea name="client_description"><?php echo htmlspecialchars($client['description'] ?? ''); ?></textarea>
                  </div>
                  <div class="actions">
                    <button class="save" name="update_client" value="1"><i class="fa fa-save"></i> Ruaj</button>
                    <button class="delete" name="delete_client" value="1" onclick="return confirm('Je i sigurt që dëshiron ta fshish?');"><i class="fa fa-trash"></i> Fshi</button>
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
