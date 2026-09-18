<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));

    $stmt = ts_db()->prepare('SELECT COUNT(*) FROM consultas WHERE email = ? AND phone = ?');
    $stmt->execute([$email, $phone]);

    if ($email && $phone && $stmt->fetchColumn() > 0) {
        session_regenerate_id(true);
        $_SESSION['ts_client_email'] = $email;
        $_SESSION['ts_client_phone'] = $phone;
        header('Location: index.php');
        exit;
    }
    $error = 'No encontramos ninguna consulta con esos datos. Revisá el email y el teléfono que usaste al escribirnos.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso a clientes | Travel Support</title>
<style>
  :root { --navy: #10243e; --navy-deep: #0a192d; --teal: #19a7a0; --cream: #f7f5ef; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(160deg, var(--navy) 0%, var(--navy-deep) 100%);
    font-family: Inter, system-ui, sans-serif; padding: 20px;
  }
  .card { background: #fff; border-radius: 18px; box-shadow: 0 30px 70px rgba(0,0,0,.35); padding: 40px; width: min(400px, 100%); }
  .card img { display: block; max-width: 190px; margin: 0 auto 8px; }
  h1 { font-size: 1.15rem; color: var(--navy); text-align: center; margin: 14px 0 6px; }
  .sub { text-align: center; color: #64748b; font-size: .85rem; margin-bottom: 26px; }
  label { display: block; font-size: .82rem; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
  input { width: 100%; padding: 11px 13px; border: 1px solid #dce6e8; border-radius: 9px; margin-bottom: 16px; font: inherit; }
  button { width: 100%; padding: 12px; border: none; border-radius: 9px; background: var(--teal); color: #fff; font-weight: 700; cursor: pointer; font-size: .95rem; }
  button:hover { background: #087f7c; }
  .error { background: #fdecea; color: #c0392b; padding: 10px 12px; border-radius: 8px; font-size: .82rem; margin-bottom: 16px; }
  .back { display: block; text-align: center; margin-top: 20px; color: #94a8b8; font-size: .8rem; text-decoration: none; }
</style>
</head>
<body>
  <form class="card" method="post">
    <img src="../assets/logo-dark.svg" alt="Travel Support">
    <h1>Seguí tu viaje</h1>
    <div class="sub">Ingresá con el email y teléfono que usaste al escribirnos para ver tus consultas y cotizaciones.</div>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autofocus>
    <label for="phone">Teléfono</label>
    <input id="phone" name="phone" required placeholder="Como lo escribiste en el formulario">
    <button type="submit">Ingresar</button>
    <a class="back" href="../index.html">&larr; Volver al sitio</a>
  </form>
</body>
</html>
