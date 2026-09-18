<?php

require_once __DIR__ . '/../lib/status.php';

function ts_status_counts(PDO $db): array
{
    $counts = ['nueva' => 0, 'contactada' => 0, 'cotizada' => 0, 'cerrada' => 0, 'descartada' => 0];
    $rows = $db->query('SELECT status, COUNT(*) AS n FROM consultas GROUP BY status')->fetchAll();
    foreach ($rows as $row) {
        $counts[$row['status']] = (int) $row['n'];
    }
    return $counts;
}

function ts_admin_layout_start(string $title, string $activeStatus = ''): void
{
    $db = ts_db();
    $counts = ts_status_counts($db);
    $total = array_sum($counts);
    ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> | Back office Travel Support</title>
<style>
  :root {
    --navy: #10243e; --navy-deep: #0a192d; --teal: #19a7a0; --teal-dark: #087f7c;
    --coral: #f2826b; --sun: #f6c85f; --cream: #f7f5ef; --line: #e3e9ea; --ink: #17304c; --muted: #64748b;
  }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--cream); font-family: Inter, system-ui, -apple-system, sans-serif; color: var(--ink); }
  a { text-decoration: none; color: inherit; }
  .shell { display: flex; min-height: 100vh; }
  .sidebar { width: 240px; flex-shrink: 0; background: linear-gradient(180deg, var(--navy), var(--navy-deep)); color: #fff; display: flex; flex-direction: column; padding: 22px 16px; }
  .sidebar-logo { padding: 4px 6px 10px; margin-bottom: 18px; display: flex; align-items: center; }
  .sidebar-logo img { width: 100%; max-width: 170px; display: block; }
  .nav-section-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: #93a9bd; margin: 18px 10px 8px; }
  .nav-link { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 12px; border-radius: 9px; font-size: .88rem; font-weight: 600; color: #d8e3ea; margin-bottom: 3px; transition: background .15s; }
  .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
  .nav-link.active { background: var(--teal); color: #fff; }
  .nav-count { background: rgba(255,255,255,.14); border-radius: 999px; padding: 1px 8px; font-size: .72rem; font-weight: 700; }
  .nav-link.active .nav-count { background: rgba(255,255,255,.28); }
  .nav-link.nav-danger { color: #ffb4a8; }
  .nav-link.nav-danger:hover { background: rgba(230,84,64,.15); color: #ffcfc6; }
  .nav-link.nav-danger.active { background: #b3382a; color: #fff; }
  .sidebar-foot { margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,.12); }
  .sidebar-foot a { font-size: .82rem; color: #9fb3c4; }
  .sidebar-foot a:hover { color: #fff; }
  .content { flex: 1; min-width: 0; padding: 30px 36px; }
  .page-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 8px; }
  .page-head h1 { font-size: 1.4rem; margin: 0; }
  .page-head .sub { color: var(--muted); font-size: .85rem; }
  .card { background: #fff; border-radius: 14px; box-shadow: 0 10px 30px rgba(16,36,62,.07); padding: 24px; margin-bottom: 22px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--line); font-size: .89rem; }
  th { color: var(--muted); font-weight: 700; text-transform: uppercase; font-size: .7rem; letter-spacing: .05em; }
  tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
  .badge-nueva { background: #fff3d8; color: #8a5a00; }
  .badge-contactada { background: #dbeafe; color: #1d4ed8; }
  .badge-cotizada { background: #dcfce7; color: #15803d; }
  .badge-cerrada { background: #e5e7eb; color: #374151; }
  .badge-descartada { background: #fde2df; color: #b3382a; }
  .btn { display: inline-block; padding: 8px 15px; border-radius: 8px; background: var(--teal); color: #fff; font-size: .82rem; font-weight: 700; border: none; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .btn-ghost { background: transparent; border: 1px solid var(--line); color: var(--ink); }
  .empty { padding: 40px; text-align: center; color: var(--muted); }
  .back-link { display: inline-block; margin-bottom: 16px; color: var(--teal); font-weight: 600; font-size: .85rem; }
</style>
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="sidebar-logo"><img src="../assets/logo-light.svg" alt="Travel Support"></div>
    <a class="nav-link <?= $activeStatus === '' ? 'active' : '' ?>" href="index.php"><span>Todas las consultas</span><span class="nav-count"><?= $total ?></span></a>
    <div class="nav-section-label">Por estado</div>
    <?php foreach (['nueva', 'contactada', 'cotizada', 'cerrada'] as $status): ?>
    <a class="nav-link <?= $activeStatus === $status ? 'active' : '' ?>" href="index.php?status=<?= $status ?>">
      <span><?= ts_status_label($status) ?></span>
      <span class="nav-count"><?= $counts[$status] ?></span>
    </a>
    <?php endforeach; ?>
    <a class="nav-link nav-danger <?= $activeStatus === 'descartada' ? 'active' : '' ?>" href="index.php?status=descartada">
      <span>Descartadas</span>
      <span class="nav-count"><?= $counts['descartada'] ?></span>
    </a>
    <div class="sidebar-foot">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </aside>
  <div class="content">
<?php
}

function ts_admin_layout_end(): void
{
    ?>
  </div>
</div>
</body>
</html>
<?php
}
