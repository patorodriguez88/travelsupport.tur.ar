<?php

require_once __DIR__ . '/auth.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = (string) ($_POST['pass'] ?? '');
    if (ts_attempt_login($user, $pass)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar | Back office Travel Support</title>
<style>
  :root { --navy: #10243e; --teal: #19a7a0; --cream: #f7f5ef; }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--cream); font-family: Inter, system-ui, sans-serif; }
  .card { background: #fff; border-radius: 16px; box-shadow: 0 18px 50px rgba(16,36,62,.12); padding: 36px; width: min(360px, calc(100% - 32px)); }
  .card img { display: block; max-width: 200px; margin: 0 auto 24px; }
  label { display: block; font-size: .85rem; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
  input { width: 100%; padding: 10px 12px; border: 1px solid #dce6e8; border-radius: 8px; margin-bottom: 16px; font: inherit; }
  button { width: 100%; padding: 11px; border: none; border-radius: 8px; background: var(--teal); color: #fff; font-weight: 700; cursor: pointer; }
  button:hover { background: #087f7c; }
  .error { color: #c0392b; font-size: .85rem; margin-bottom: 12px; }
</style>
</head>
<body>
  <form class="card" method="post">
    <img src="../assets/logo.png" alt="Travel Support">
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label for="user">Usuario</label>
    <input id="user" name="user" autocomplete="username" required autofocus>
    <label for="pass">Contraseña</label>
    <input id="pass" name="pass" type="password" autocomplete="current-password" required>
    <button type="submit">Ingresar</button>
  </form>
</body>
</html>
