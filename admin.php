<?php
/**
 * Suchinta's Guardian Angel - Owner's Control Dashboard
 * 
 * Features:
 * - Session-based authentication ($ADMIN_PASSWORD)
 * - Directly linked to the user-facing angel sanctuary
 * - Adds and deletes advices in MySQL (guardian_angel_db.advices)
 * - Clean pure-white & warm gold aesthetic matching the sanctuary
 */

if (session_status() === PHP_SESSION_NONE) {
    // Keep authenticated session active for 30 days on this device
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// -------------------------------------------------------------
// 1. Configuration & Database Connection
// -------------------------------------------------------------
$ADMIN_PASSWORD = 'angelpassword123'; // Secret password to manage advices

$pdo = require_once __DIR__ . '/db.php';

$login_error = '';
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -------------------------------------------------------------
// 2. Authentication Actions
// -------------------------------------------------------------

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $password_input = $_POST['password'] ?? '';
    if ($password_input === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = 'Incorrect secret passcode. Please try again.';
    }
}

$is_logged_in = !empty($_SESSION['admin_logged_in']);

// -------------------------------------------------------------
// 3. Authenticated CRUD Actions
// -------------------------------------------------------------
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add New Normal Advice
    if ($action === 'add') {
        $content = trim(strip_tags($_POST['content'] ?? ''));

        if ($content === '') {
            $_SESSION['flash_error'] = 'Advice text cannot be empty.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO advices (content) VALUES (:content)');
                $stmt->execute([':content' => $content]);
                $_SESSION['flash_success'] = 'New normal advice was whispered to the angel! It is now live in the 70% rotation.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Failed to save advice: ' . $e->getMessage();
            }
        }
        header('Location: admin.php?tab=normal');
        exit;
    }

    // Delete Normal Advice
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid advice ID.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM advices WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $_SESSION['flash_success'] = 'Advice #' . $id . ' was removed from rotation.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
        }
        header('Location: admin.php?tab=normal');
        exit;
    }

    // Add New Pro Advice (Deep Guidance / Full Paragraph)
    if ($action === 'add_pro') {
        $content = trim(strip_tags($_POST['content'] ?? ''));

        if ($content === '') {
            $_SESSION['flash_error'] = 'Pro advice text cannot be empty.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO pro_advices (content) VALUES (:content)');
                $stmt->execute([':content' => $content]);
                $_SESSION['flash_success'] = '🌟 New Pro Advice saved! It will appear with 30% chance in Suchinta\'s sanctuary.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Failed to save pro advice: ' . $e->getMessage();
            }
        }
        header('Location: admin.php?tab=pro');
        exit;
    }

    // Delete Pro Advice
    if ($action === 'delete_pro') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid pro advice ID.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM pro_advices WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $_SESSION['flash_success'] = 'Pro Advice #' . $id . ' was removed.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
        }
        header('Location: admin.php?tab=pro');
        exit;
    }
}

// -------------------------------------------------------------
// 4. Fetch All Active Advice & Pro Advice Records
// -------------------------------------------------------------
$advices = [];
$pro_advices = [];
$current_tab = isset($_GET['tab']) && $_GET['tab'] === 'pro' ? 'pro' : 'normal';

if ($is_logged_in) {
    try {
        $stmt = $pdo->query('SELECT id, content, created_at FROM advices ORDER BY id DESC');
        $advices = $stmt->fetchAll();

        $stmtPro = $pdo->query('SELECT id, content, created_at FROM pro_advices ORDER BY id DESC');
        $pro_advices = $stmtPro->fetchAll();
    } catch (PDOException $e) {
        $flash_error = 'Error querying advices: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guardian Portal • Owner Dashboard</title>
  
  <!-- Google Fonts: Outfit & Playfair Display -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-pure: #ffffff;
      --bg-soft: #f8fafc;
      --bg-card: #ffffff;
      --border-card: #e2e8f0;
      --accent-gold: #d97706;
      --accent-gold-bright: #f59e0b;
      --accent-gold-light: #fef3c7;
      --accent-rose: #f43f5e;
      --accent-danger: #ef4444;
      --accent-danger-bg: #fee2e2;
      --text-main: #0f172a;
      --text-body: #1e293b;
      --text-muted: #64748b;
      --font-body: 'Outfit', -apple-system, sans-serif;
      --font-serif: 'Playfair Display', Georgia, serif;
      --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-pure);
      background-image: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #fdf8f0 100%);
      background-attachment: fixed;
      color: var(--text-body);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .container {
      width: 100%;
      max-width: 960px;
      margin: 0 auto;
      padding: 2rem 1.25rem 3rem;
    }

    /* Top Bar */
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1.2rem;
      border-bottom: 1px solid var(--border-card);
      flex-wrap: wrap;
      gap: 1rem;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .brand-icon {
      font-size: 1.8rem;
    }

    .brand-title {
      font-family: var(--font-serif);
      font-size: 1.45rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .brand-subtitle {
      font-size: 0.8rem;
      color: var(--accent-gold);
      font-weight: 600;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.55rem 1.15rem;
      border-radius: 9999px;
      font-size: 0.88rem;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s var(--ease-spring);
      border: none;
      font-family: var(--font-body);
    }

    .btn-live {
      background: linear-gradient(135deg, #f59e0b, #ec4899);
      color: #ffffff;
      box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    }

    .btn-live:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 18px rgba(236, 72, 153, 0.4);
    }

    .btn-secondary {
      background: #ffffff;
      border: 1px solid var(--border-card);
      color: var(--text-body);
    }

    .btn-secondary:hover {
      background: #f1f5f9;
      transform: translateY(-2px);
    }

    .btn-logout {
      background: #fee2e2;
      border: 1px solid #fecaca;
      color: #b91c1c;
    }

    .btn-logout:hover {
      background: #fca5a5;
      color: #7f1d1d;
      transform: translateY(-2px);
    }

    .btn-primary {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: #ffffff;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 18px rgba(217, 119, 6, 0.4);
    }

    .btn-delete {
      background: #fee2e2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 0.35rem 0.8rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .btn-delete:hover {
      background: #ef4444;
      color: #ffffff;
      transform: scale(1.05);
    }

    .btn-pro {
      background: linear-gradient(135deg, #ec4899, #f59e0b);
      color: #ffffff;
      font-weight: 600;
      box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35);
    }

    .btn-pro:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 18px rgba(236, 72, 153, 0.45);
    }

    /* Dashboard Navigation Tabs */
    .dashboard-tabs {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid var(--border-card);
      padding-bottom: 0.6rem;
      overflow-x: auto;
    }

    .tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.55rem;
      padding: 0.75rem 1.4rem;
      border-radius: 12px;
      font-size: 0.95rem;
      font-weight: 600;
      text-decoration: none;
      color: var(--text-muted);
      background: #ffffff;
      border: 1px solid var(--border-card);
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .tab-btn:hover {
      color: var(--text-main);
      background: #f8fafc;
      transform: translateY(-1px);
    }

    .tab-btn.active {
      color: #92400e;
      background: #fef3c7;
      border-color: #fde68a;
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
    }

    .tab-btn.tab-pro.active {
      color: #831843;
      background: #fce7f3;
      border-color: #fbcfe8;
      box-shadow: 0 4px 12px rgba(236, 72, 153, 0.15);
    }

    .tab-badge {
      font-size: 0.75rem;
      padding: 0.15rem 0.55rem;
      border-radius: 999px;
      background: #e2e8f0;
      color: #475569;
    }

    .tab-btn.active .tab-badge {
      background: #f59e0b;
      color: #ffffff;
    }

    .tab-btn.tab-pro.active .tab-badge {
      background: #ec4899;
      color: #ffffff;
    }

    /* Pro Advice Banner */
    .pro-banner {
      background: linear-gradient(135deg, #fffbeb 0%, #fdf2f8 100%);
      border: 1px solid #fbcfe8;
      border-radius: 18px;
      padding: 1.4rem;
      margin-bottom: 1.75rem;
      display: flex;
      gap: 1.1rem;
      align-items: flex-start;
      box-shadow: 0 6px 20px -8px rgba(236, 72, 153, 0.12);
    }

    .pro-banner-icon {
      font-size: 2.2rem;
      line-height: 1;
      flex-shrink: 0;
    }

    .pro-banner-title {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      color: #831843;
      margin-bottom: 0.35rem;
      font-weight: 700;
    }

    .pro-banner-desc {
      font-size: 0.9rem;
      color: #475569;
      line-height: 1.6;
    }


    /* Cards */
    .card {
      background: var(--bg-card);
      border: 1px solid var(--border-card);
      border-radius: 20px;
      padding: 1.8rem;
      margin-bottom: 2rem;
      box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
    }

    .card-title {
      font-family: var(--font-serif);
      font-size: 1.3rem;
      margin-bottom: 0.35rem;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-subtitle {
      font-size: 0.88rem;
      color: var(--text-muted);
      margin-bottom: 1.25rem;
    }

    /* Alerts */
    .alert {
      padding: 0.9rem 1.25rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      font-size: 0.92rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .alert-success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
    }

    .alert-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
    }

    /* Forms */
    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-control {
      width: 100%;
      background: #f8fafc;
      border: 1px solid var(--border-card);
      border-radius: 14px;
      padding: 0.9rem 1.1rem;
      color: var(--text-main);
      font-family: var(--font-body);
      font-size: 1rem;
      line-height: 1.6;
      transition: all 0.2s ease;
      resize: vertical;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent-gold-bright);
      background: #ffffff;
      box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
    }

    /* Login Specific View */
    .login-wrapper {
      min-height: 80vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    .login-icon-ring {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: var(--accent-gold-light);
      border: 2px solid rgba(245, 158, 11, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      margin: 0 auto 1.25rem;
    }

    /* Table */
    .table-container {
      overflow-x: auto;
      border-radius: 12px;
      border: 1px solid var(--border-card);
    }

    .custom-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.92rem;
    }

    .custom-table th {
      background: #f8fafc;
      color: var(--accent-gold);
      padding: 0.85rem 1rem;
      font-weight: 600;
      border-bottom: 1px solid var(--border-card);
      text-transform: uppercase;
      font-size: 0.76rem;
      letter-spacing: 0.05em;
    }

    .custom-table td {
      padding: 1rem;
      border-bottom: 1px solid var(--border-card);
      vertical-align: middle;
      color: var(--text-body);
      background: #ffffff;
    }

    .custom-table tr:last-child td {
      border-bottom: none;
    }

    .id-pill {
      font-size: 0.78rem;
      background: #f1f5f9;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .content-cell {
      max-width: 480px;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    .date-cell {
      white-space: nowrap;
      font-size: 0.82rem;
      color: var(--text-muted);
    }

    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--text-muted);
    }

    .empty-icon {
      font-size: 2.5rem;
      margin-bottom: 0.75rem;
    }

    @media (max-width: 650px) {
      .top-bar {
        flex-direction: column;
        align-items: flex-start;
      }
      .nav-actions {
        width: 100%;
        justify-content: space-between;
      }
      .card {
        padding: 1.25rem;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <?php if (!$is_logged_in): ?>
      <!-- ================= LOGIN SCREEN ================= -->
      <div class="login-wrapper">
        <div class="card login-card">
          <div class="login-icon-ring">🔒</div>
          <h1 class="card-title" style="justify-content: center;">Guardian Portal</h1>
          <p class="card-subtitle">Authenticate to write and manage Suchinta's advices</p>

          <?php if (!empty($login_error)): ?>
            <div class="alert alert-error">
              <span>⚠️</span>
              <span><?= htmlspecialchars($login_error) ?></span>
            </div>
          <?php endif; ?>

          <form action="admin.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group" style="text-align: left;">
              <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);" for="password">
                Secret Passcode
              </label>
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control" 
                placeholder="Enter password..." 
                required 
                autofocus
              >
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem;">
              Unlock Sanctuary 🗝️
            </button>
          </form>

          <div style="margin-top: 1.5rem;">
            <a href="index.html" class="btn btn-secondary" style="font-size: 0.82rem;">
              ← Back to Angel Sanctuary
            </a>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ================= DASHBOARD SCREEN ================= -->
      
      <!-- Top Navigation -->
      <header class="top-bar">
        <div class="brand">
          <span class="brand-icon">👼</span>
          <div>
            <h1 class="brand-title">Suchinta's Guardian Angel</h1>
            <span class="brand-subtitle">✨ Owner Control Dashboard</span>
          </div>
        </div>

        <nav class="nav-actions">
          <!-- Direct Link to Live User Site -->
          <a href="index.html" target="_blank" class="btn btn-live" title="Open Suchinta's live view">
            <span>✨ Open Live Sanctuary</span>
            <span>→</span>
          </a>
          <a href="together.html" target="_blank" class="btn btn-secondary" style="font-weight: 700; border-color: rgba(245, 158, 11, 0.4); color: #92400e; background: #fffbeb;" title="View Credits & Copyright">
            <span>📜 Credits</span>
          </a>
          <a href="admin.php?action=logout" class="btn btn-logout" title="Exit admin session">
            Log Out
          </a>
        </nav>
      </header>

      <!-- Flash Notifications -->
      <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success">
          <span>✨</span>
          <div>
            <strong>Success!</strong> <?= htmlspecialchars($flash_success) ?>
            <a href="index.html" target="_blank" style="color: #065f46; font-weight: 600; text-decoration: underline; margin-left: 0.5rem;">
              View on live sanctuary →
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($flash_error)): ?>
        <div class="alert alert-error">
          <span>⚠️</span>
          <span><?= htmlspecialchars($flash_error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Dashboard Navigation Tabs -->
      <nav class="dashboard-tabs" aria-label="Dashboard views">
        <a href="admin.php?tab=normal" class="tab-btn <?= $current_tab === 'normal' ? 'active' : '' ?>">
          <span>☁️ Normal Advices (70% Chance)</span>
          <span class="tab-badge"><?= count($advices) ?></span>
        </a>
        <a href="admin.php?tab=pro" class="tab-btn tab-pro <?= $current_tab === 'pro' ? 'active' : '' ?>">
          <span>🌟 Pro Advices Dashboard (30% Chance)</span>
          <span class="tab-badge"><?= count($pro_advices) ?></span>
        </a>
      </nav>

      <?php if ($current_tab === 'normal'): ?>
        <!-- ================= NORMAL ADVICES DASHBOARD (70%) ================= -->

        <!-- 1. Add Advice Form -->
        <section class="card">
          <h2 class="card-title">
            <span>✍️</span> Write New Normal Advice for Suchinta
          </h2>
          <p class="card-subtitle">
            Type your gentle reminder or quick sweet thought below. Normal advices have a <strong>70% chance</strong> of appearing directly inside the angel's cloud whenever she taps the angel.
          </p>

          <form action="admin.php?tab=normal" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <textarea 
                name="content" 
                class="form-control" 
                rows="3" 
                placeholder="e.g., Take a deep breath, drink some water, and remember that I am always cheering you on..."
                required
              ></textarea>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
              <span style="font-size: 0.82rem; color: var(--text-muted);">
                💡 Appears directly on the cloud with 70% probability.
              </span>
              <button type="submit" class="btn btn-primary">
                <span>Add Normal Advice</span>
                <span>✨</span>
              </button>
            </div>
          </form>
        </section>

        <!-- 2. Existing Normal Advice Table -->
        <section class="card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
              <h2 class="card-title">
                <span>☁️</span> Active Normal Advices in Cloud
              </h2>
              <p class="card-subtitle" style="margin-bottom: 0;">
                Regular reminders in the 70% rotation
              </p>
            </div>
            <span style="font-size: 0.85rem; background: var(--accent-gold-light); color: var(--accent-gold); border: 1px solid rgba(245, 158, 11, 0.25); padding: 0.3rem 0.8rem; border-radius: 9999px; font-weight: 600;">
              Total Normal: <?= count($advices) ?>
            </span>
          </div>

          <?php if (empty($advices)): ?>
            <div class="empty-state">
              <div class="empty-icon">🪶</div>
              <p>No normal advice entries found yet. Use the form above to whisper your first one!</p>
            </div>
          <?php else: ?>
            <div class="table-container">
              <table class="custom-table">
                <thead>
                  <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Advice Content</th>
                    <th style="width: 170px;">Date Added</th>
                    <th style="width: 90px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($advices as $item): ?>
                    <tr>
                      <td>
                        <span class="id-pill">#<?= htmlspecialchars($item['id']) ?></span>
                      </td>
                      <td class="content-cell">
                        <?= nl2br(htmlspecialchars($item['content'])) ?>
                      </td>
                      <td class="date-cell">
                        <?= date('M j, Y • g:i A', strtotime($item['created_at'])) ?>
                      </td>
                      <td style="text-align: center;">
                        <form action="admin.php?tab=normal" method="POST" onsubmit="return confirm('Are you sure you want to delete advice #<?= htmlspecialchars($item['id']) ?>?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                          <button type="submit" class="btn-delete" title="Delete advice">
                            🗑️ Delete
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>

      <?php else: ?>
        <!-- ================= PRO ADVICES DASHBOARD (30%) ================= -->

        <!-- Pro Advice Philosophy Banner -->
        <div class="pro-banner">
          <div class="pro-banner-icon">🌟</div>
          <div>
            <h3 class="pro-banner-title">Pro Advice Sanctuary — Deep Caring Guidance (30% Chance)</h3>
            <p class="pro-banner-desc">
              <strong>The true motto behind this entire site!</strong> Pro Advices are rich, heartfelt paragraphs of deep wisdom, comfort, and unconditional support. Whenever an angel cycle rolls a Pro Advice (30% appearance chance), the cloud displays <strong>"Pro Advice"</strong>. Suchinta taps that writing, and an elegant message box smoothly opens to reveal your entire paragraph.
            </p>
          </div>
        </div>

        <!-- 1. Add Pro Advice Form -->
        <section class="card" style="border-color: #fbcfe8;">
          <h2 class="card-title" style="color: #831843;">
            <span>🌟</span> Write New Pro Advice for Suchinta (Full Paragraph)
          </h2>
          <p class="card-subtitle">
            Write your deeper life advice, comforting paragraph, or heartfelt reflection. These are usually substantial in length and carry special meaning.
          </p>

          <form action="admin.php?tab=pro" method="POST">
            <input type="hidden" name="action" value="add_pro">
            <div class="form-group">
              <textarea 
                name="content" 
                class="form-control" 
                rows="6" 
                placeholder="Write your long heartfelt paragraph here... (e.g. When life feels hectic, pause and remember how much you've overcome. You have an ocean of strength inside you...)"
                required
              ></textarea>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
              <span style="font-size: 0.82rem; color: #831843;">
                🌟 30% chance to pop up • Clickable to open message box on Suchinta's screen
              </span>
              <button type="submit" class="btn btn-pro">
                <span>Save Pro Advice Paragraph</span>
                <span>✨</span>
              </button>
            </div>
          </form>
        </section>

        <!-- 2. Existing Pro Advice Table -->
        <section class="card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
              <h2 class="card-title">
                <span>🌟</span> Active Pro Advices in Database
              </h2>
              <p class="card-subtitle" style="margin-bottom: 0;">
                All deep guidance paragraphs stored for the 30% pop-in chance
              </p>
            </div>
            <span style="font-size: 0.85rem; background: #fce7f3; color: #831843; border: 1px solid #fbcfe8; padding: 0.3rem 0.8rem; border-radius: 9999px; font-weight: 600;">
              Total Pro Advices: <?= count($pro_advices) ?>
            </span>
          </div>

          <?php if (empty($pro_advices)): ?>
            <div class="empty-state">
              <div class="empty-icon">🌟</div>
              <p>No pro advice paragraphs added yet. Write your first deep paragraph above!</p>
            </div>
          <?php else: ?>
            <div class="table-container">
              <table class="custom-table">
                <thead>
                  <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Pro Advice Content (Full Paragraph)</th>
                    <th style="width: 170px;">Date Added</th>
                    <th style="width: 90px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pro_advices as $item): ?>
                    <tr>
                      <td>
                        <span class="id-pill" style="background: #fce7f3; color: #9d174d;">#<?= htmlspecialchars($item['id']) ?></span>
                      </td>
                      <td class="content-cell" style="max-width: 540px; font-size: 0.94rem; line-height: 1.7;">
                        <?= nl2br(htmlspecialchars($item['content'])) ?>
                      </td>
                      <td class="date-cell">
                        <?= date('M j, Y • g:i A', strtotime($item['created_at'])) ?>
                      </td>
                      <td style="text-align: center;">
                        <form action="admin.php?tab=pro" method="POST" onsubmit="return confirm('Are you sure you want to delete Pro Advice #<?= htmlspecialchars($item['id']) ?>?');">
                          <input type="hidden" name="action" value="delete_pro">
                          <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                          <button type="submit" class="btn-delete" title="Delete pro advice">
                            🗑️ Delete
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>

      <?php endif; ?>

    <?php endif; ?>
  </div>

</body>
</html>
