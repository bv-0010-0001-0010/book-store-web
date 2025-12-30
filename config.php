<?php
// --- CONNECT (TiDB + MySQL compatible, TLS enabled for TiDB Cloud) ---
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = mysqli_init();
if (!$mysqli) {
  die('mysqli_init failed');
}

// Read CA cert from env var (Vercel)
$caPem = getenv('DB_SSL_CA_PEM') ?: '';

$useTls = false;
$caFile = '';

// Enable TLS automatically if CA is provided
if ($caPem !== '') {
  $useTls = true;

  // Write PEM to temp file inside the serverless runtime
  $caFile = sys_get_temp_dir() . '/tidb-ca.pem';
  file_put_contents($caFile, $caPem);

  // Set SSL options (no client cert/key needed)
  mysqli_ssl_set($mysqli, null, null, $caFile, null, null);
}

$flags = $useTls ? MYSQLI_CLIENT_SSL : 0;

// Connect
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

// Use your existing $conn variable everywhere else
$conn = $mysqli;
mysqli_set_charset($conn, 'utf8mb4');
// --- END CONNECT ---
?>
