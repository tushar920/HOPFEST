<?php
declare(strict_types=1);
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/signup-lib.php';

session_name('hfadmin');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);

$pass = (string)($cfg['admin_password'] ?? '');
$locked = $pass === '' || $pass === 'CHANGE-ME';
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: signups.php');
    exit;
}
if (!$locked && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (hash_equals($pass, (string)($_POST['password'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['hf_ok'] = true;
        header('Location: signups.php');
        exit;
    }
    sleep(1);
    $error = 'Wrong password.';
}
$in = !$locked && !empty($_SESSION['hf_ok']);

if ($in && isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="hopfest-signups-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF" . hf_csv_text(); // BOM so Excel reads Indian names and symbols correctly
    exit;
}

$rows = $in ? array_reverse(hf_read_all()) : [];
$byCity = [];
foreach ($rows as $r) { $c = $r[4] ?? ''; $byCity[$c] = ($byCity[$c] ?? 0) + 1; }
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>HOP FEST · Sign-ups</title>
<style>
body{margin:0;font:15px/1.45 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f6f1e4;color:#090909}
header{background:#1c4892;color:#fff;padding:14px 20px;border-bottom:3px solid #090909;display:flex;justify-content:space-between;align-items:center;gap:12px}
header b{font-size:18px;letter-spacing:.03em}header a{color:#faee30;font-weight:700}
main{padding:20px;max-width:1200px;margin:0 auto}
.card{background:#fff;border:3px solid #090909;box-shadow:6px 6px 0 #090909;padding:20px;max-width:420px;margin:40px auto}
input{width:100%;box-sizing:border-box;border:3px solid #090909;padding:10px;font-size:16px;margin:8px 0 12px}
button,.btn{display:inline-block;border:3px solid #090909;background:#e84b81;color:#fff;font-weight:800;padding:10px 18px;font-size:15px;cursor:pointer;text-decoration:none;box-shadow:4px 4px 0 #090909}
.err{color:#c4161c;font-weight:700}
.stats{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 16px}.stats span{background:#fff;border:3px solid #090909;padding:6px 12px;font-weight:700}
.wrap{overflow-x:auto;background:#fff;border:3px solid #090909}
table{border-collapse:collapse;width:100%;min-width:760px}th,td{text-align:left;padding:9px 12px;border-bottom:1px solid #ddd;white-space:nowrap}
th{background:#faee30;border-bottom:3px solid #090909;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.empty{padding:30px;text-align:center;color:#666}
</style></head><body>
<header><b>HOP FEST · Sign-ups</b><?php if ($in): ?><a href="?logout=1">Log out</a><?php endif; ?></header>
<main>
<?php if ($locked): ?>
  <div class="card"><p><b>This page is locked.</b></p><p>Set a password in <code>config.php</code> (change <code>CHANGE-ME</code>) to view sign-ups.</p></div>
<?php elseif (!$in): ?>
  <form class="card" method="post"><b>Enter the admin password</b>
    <input type="password" name="password" autocomplete="current-password" autofocus required>
    <?php if ($error): ?><p class="err"><?= h($error) ?></p><?php endif; ?>
    <button type="submit">Log in</button></form>
<?php else: ?>
  <div class="stats"><span>Total: <?= count($rows) ?></span>
    <?php foreach ($byCity as $c => $n): ?><span><?= h($c) ?>: <?= $n ?></span><?php endforeach; ?></div>
  <p><a class="btn" href="?download=1">Download CSV (opens in Excel / Google Sheets)</a></p>
  <div class="wrap"><?php if (!$rows): ?><div class="empty">No sign-ups yet.</div><?php else: ?>
    <table><thead><tr><?php foreach (HF_COLUMNS as $c): ?><th><?= h($c) ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><?php foreach (array_keys(HF_COLUMNS) as $i): ?><td><?= h(ltrim((string)($r[$i] ?? ''), "'")) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
    </tbody></table><?php endif; ?></div>
<?php endif; ?>
</main></body></html>
