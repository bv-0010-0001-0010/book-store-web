<?php
declare(strict_types=1);

$config = [
  'DB_HOST' => 'localhost',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_NAME' => 'shop_db',
  'DB_PORT' => 3306,
];

// Prefer local-only file if it exists
$local = __DIR__ . '/config.local.php';
if (file_exists($local)) {
  $override = require $local;
  if (is_array($override)) {
    $config = array_merge($config, $override);
  }
}

// Also allow environment variables to override (optional)
foreach ($config as $k => $v) {
  $env = getenv($k);
  if ($env !== false && $env !== '') {
    $config[$k] = ($k === 'DB_PORT') ? (int)$env : $env;
  }
}

// --- CONNECT (TiDB + MySQL compatible) ---
$sslEnabled = filter_var($config['DB_SSL'] ?? false, FILTER_VALIDATE_BOOLEAN);
$sslCaPath  = (string)($config['DB_SSL_CA_PATH'] ?? '');

// TiDB Cloud: SSL should be ON when using gateway host + port 4000
// Local MySQL: SSL can be OFF
$mysqli = mysqli_init();

// If SSL is enabled, set SSL before connecting
if ($sslEnabled) {
  // If you provide a CA path, it will verify chain. If empty, it still uses SSL but may not verify.
  mysqli_ssl_set($mysqli, null, null, $sslCaPath !== '' ? $sslCaPath : null, null, null);
}

$clientFlags = $sslEnabled ? MYSQLI_CLIENT_SSL : 0;

$ok = mysqli_real_connect(
  $mysqli,
  (string)$config['DB_HOST'],
  (string)$config['DB_USER'],
  (string)$config['DB_PASS'],
  (string)$config['DB_NAME'],
  (int)$config['DB_PORT'],
  null,
  $clientFlags
);

if (!$ok) {
  die('Database connection failed: ' . mysqli_connect_error());
}

$conn = $mysqli;
mysqli_set_charset($conn, 'utf8mb4');


if (!$conn) {
  die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');


function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ensure_session_started(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    // Good defaults for local dev; add "secure" => true when using HTTPS
    session_set_cookie_params([
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
    session_start();
  }
}

function csrf_token(): string {
  ensure_session_started();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_validate_post(): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  ensure_session_started();

  $token = $_POST['csrf'] ?? '';
  if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(403);
    exit('Invalid CSRF token');
  }
}

function redirect(string $path): void {
  header('Location: ' . $path);
  exit;
}
