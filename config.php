<?php
declare(strict_types=1);

// Base defaults (safe for local dev)
$config = [
  'DB_HOST' => '127.0.0.1',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_NAME' => 'shop_db',
  'DB_PORT' => 3306,
];

// Prefer local-only file if it exists (local machine only)
$local = __DIR__ . '/config.local.php';
if (file_exists($local)) {
  $override = require $local;
  if (is_array($override)) {
    $config = array_merge($config, $override);
  }
}

// Environment variables override everything (Vercel uses these)
foreach (array_keys($config) as $k) {
  $env = getenv($k);
  if ($env !== false && $env !== '') {
    $config[$k] = ($k === 'DB_PORT') ? (int)$env : $env;
  }
}

// ---- CONNECT (TLS for TiDB Cloud) ----
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = mysqli_init();
if (!$mysqli) {
  die('mysqli_init failed');
}

// Read CA cert from Vercel env var
$caPem = getenv('DB_SSL_CA_PEM') ?: '';
$useTls = ($caPem !== '');

if ($useTls) {
  // If Vercel stored the cert with "\n" characters, convert them to real newlines
  $caPem = str_replace("\\n", "\n", $caPem);

  $caFile = sys_get_temp_dir() . '/tidb-ca.pem';
  file_put_contents($caFile, $caPem);

  mysqli_ssl_set($mysqli, null, null, $caFile, null, null);
}

$flags = $useTls ? MYSQLI_CLIENT_SSL : 0;

// IMPORTANT: If DB_HOST is empty, mysqli tries a local socket and fails on Vercel
if (empty($config['DB_HOST'])) {
  die('DB_HOST is empty. Check Vercel Environment Variables.');
}

mysqli_real_connect(
  $mysqli,
  (string)$config['DB_HOST'],
  (string)$config['DB_USER'],
  (string)$config['DB_PASS'],
  (string)$config['DB_NAME'],
  (int)$config['DB_PORT'],
  null,
  $flags
);

$conn = $mysqli;
mysqli_set_charset($conn, 'utf8mb4');

// Helpers (keep yours below)
function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
