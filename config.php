<?php
declare(strict_types=1);

// -----------------------------------------------------------------------------
// Environment + error display
// -----------------------------------------------------------------------------
$IS_VERCEL = (getenv('VERCEL') === '1') || !empty($_SERVER['VERCEL']);
$IS_PROD  = $IS_VERCEL && (getenv('VERCEL_ENV') === 'production');

if ($IS_PROD) {
  // Don’t leak notices/warnings/stack traces to users.
  ini_set('display_errors', '0');
  ini_set('display_startup_errors', '0');
  error_reporting(0);
} else {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
}

// -----------------------------------------------------------------------------
// Config: defaults -> local override -> env override
// -----------------------------------------------------------------------------
$config = [
  'DB_HOST' => '127.0.0.1',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_NAME' => 'shop_db',
  'DB_PORT' => 3306,
  // TLS / SSL (TiDB Cloud requires TLS)
  'DB_SSL' => false,
  'DB_SSL_CA_PEM' => '',
];

// Local-only file (never commit). Ignore it on Vercel even if it accidentally exists.
$local = __DIR__ . '/config.local.php';
if (!$IS_VERCEL && file_exists($local)) {
  $override = require $local;
  if (is_array($override)) {
    $config = array_merge($config, $override);
  }
}

// Environment variables always win
foreach (array_keys($config) as $k) {
  $env = getenv($k);
  if ($env !== false && $env !== '') {
    if ($k === 'DB_PORT') {
      $config[$k] = (int)$env;
    } elseif ($k === 'DB_SSL') {
      $config[$k] = filter_var($env, FILTER_VALIDATE_BOOLEAN);
    } else {
      $config[$k] = $env;
    }
  }
}

// If a CA cert is provided, automatically enable TLS
if (!empty($config['DB_SSL_CA_PEM'])) {
  $config['DB_SSL'] = true;
}

// -----------------------------------------------------------------------------
// Database connection (MySQL / TiDB)
// -----------------------------------------------------------------------------
if (!$IS_PROD) {
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

$mysqli = mysqli_init();
if (!$mysqli) {
  throw new RuntimeException('mysqli_init failed');
}

try {
  $flags = 0;

  if (!empty($config['DB_SSL'])) {
    $pem = (string)$config['DB_SSL_CA_PEM'];

    if ($pem !== '') {
      // Write PEM to a temp file inside the serverless runtime
      $tmp = tempnam(sys_get_temp_dir(), 'tidb-ca-');
      if ($tmp === false) {
        throw new RuntimeException('Failed to create temp file for CA cert');
      }
      file_put_contents($tmp, $pem);
      mysqli_ssl_set($mysqli, null, null, $tmp, null, null);
    } else {
      // TLS requested but no CA PEM provided. Still attempt SSL.
      mysqli_ssl_set($mysqli, null, null, null, null, null);
    }

    $flags |= MYSQLI_CLIENT_SSL;
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

  mysqli_set_charset($mysqli, 'utf8mb4');
  $conn = $mysqli;
} catch (Throwable $e) {
  if ($IS_PROD) {
    http_response_code(500);
    exit('Database connection failed.');
  }
  throw $e;
}

// -----------------------------------------------------------------------------
// Helpers (session, escaping, redirects, CSRF)
// -----------------------------------------------------------------------------
function ensure_session_started(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

  session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => $isHttps,
  ]);

  session_start();
}

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void {
  header('Location: ' . $path);
  exit;
}

function csrf_token(): string {
  ensure_session_started();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION['csrf'];
}

function csrf_validate_post(): void {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
  ensure_session_started();

  $token = $_POST['csrf'] ?? '';
  if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], $token)) {
    http_response_code(403);
    exit('Invalid CSRF token');
  }
}
