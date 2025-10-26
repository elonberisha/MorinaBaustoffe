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
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Morina Baustoffe</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at top left, #1f2937, #0f172a 55%, #040716);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .header-content {
            max-width: 1120px;
            margin: 0 auto;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #f8fafc;
        }
        .header-actions { display: flex; gap: 12px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.25s ease;
            font-size: 0.95rem;
        }
        .btn-light {
            background: rgba(148, 163, 184, 0.15);
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.25);
        }
        .btn-light:hover {
            background: rgba(148, 163, 184, 0.28);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: #fff;
        }
        .btn-danger:hover { transform: translateY(-2px); }
        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 36px 24px 64px;
            width: 100%;
            flex: 1;
        }
        .welcome-card {
            margin-bottom: 36px;
            padding: 28px;
            border-radius: 22px;
            background: linear-gradient(145deg, rgba(99, 102, 241, 0.25), rgba(59, 130, 246, 0.16));
            border: 1px solid rgba(129, 140, 248, 0.25);
            box-shadow: 0 32px 80px rgba(30, 64, 175, 0.28);
        }
        .welcome-card h2 {
            font-size: 1.6rem;
            margin-bottom: 10px;
            color: #f8fafc;
        }
        .welcome-card p { color: rgba(226, 232, 240, 0.75); font-size: 1rem; }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
        }
        .quick-card {
            position: relative;
            padding: 28px;
            border-radius: 24px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.14);
            box-shadow: 0 22px 65px rgba(15, 23, 42, 0.45);
            text-decoration: none;
            color: inherit;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 18px;
            transition: transform 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
        }
        .quick-card:hover {
            transform: translateY(-8px) scale(1.01);
            border-color: rgba(129, 140, 248, 0.45);
            box-shadow: 0 28px 75px rgba(99, 102, 241, 0.32);
        }
        .quick-card h2 {
            font-size: 1.35rem;
            color: #f1f5f9;
            margin: 0;
        }
        .quick-card p {
            color: rgba(203, 213, 225, 0.75);
            font-size: 0.98rem;
            line-height: 1.5;
            margin: 0;
        }
        .quick-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: linear-gradient(130deg, rgba(129, 140, 248, 0.28), rgba(99, 102, 241, 0.16));
            color: #818cf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .quick-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(203, 213, 225, 0.6);
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .header-content { flex-direction: column; align-items: flex-start; }
            .header-actions { width: 100%; justify-content: space-between; }
            .welcome-card { padding: 24px; }
            .quick-card { padding: 24px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1><i class="fas fa-tachometer-alt"></i> Paneli Administrativ</h1>
            <div class="header-actions">
                <a class="btn btn-light" href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Shiko faqen</a>
                <a class="btn btn-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Dil</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h2>Përshëndetje, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?>!</h2>
            <p>Zgjidh një nga opsionet më poshtë për të menaxhuar faqen Morina Baustoffe. Katalogët dhe klientët përditësohen në kohë reale pasi ruani ndryshimet.</p>
        </section>

        <section class="quick-actions">
            <a class="quick-card" href="clients.php">
                <div class="quick-icon"><i class="fas fa-users"></i></div>
                <div>
                    <h2>Klientë Besnikë</h2>
                    <p>Shto ose përditëso logot dhe emrat e klientëve që na besojnë çdo ditë.</p>
                </div>
                <div class="quick-meta"><i class="fas fa-shield-check"></i> Përditësim i menjëhershëm në faqen kryesore</div>
            </a>
            <a class="quick-card" href="partners.php">
                <div class="quick-icon"><i class="fas fa-handshake"></i></div>
                <div>
                    <h2>Partnerë Strategjikë</h2>
                    <p>Menaxho partnerët që shfaqen në faqen kryesore me logo, përshkrim dhe renditje.</p>
                </div>
                <div class="quick-meta"><i class="fas fa-star"></i> Kontrollo partnerët e veçantë</div>
            </a>
            <a class="quick-card" href="catalogs.php">
                <div class="quick-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <h2>Kategori &amp; Katalogë</h2>
                    <p>Organizo kategoritë e materialeve dhe katalogët PDF për vizitorët.</p>
                </div>
                <div class="quick-meta"><i class="fas fa-file-alt"></i> Menaxho skedarët dhe renditjen</div>
            </a>
        </section>
    </main>
</body>
</html>
