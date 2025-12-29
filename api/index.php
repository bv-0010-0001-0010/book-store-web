<?php
declare(strict_types=1);

// Make relative includes like "include 'config.php';" work correctly
chdir(__DIR__ . '/..');

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = ltrim($uriPath ?: '/', '/');

// Default route
if ($path === '' || $path === '/') {
  $path = 'index.php';
}

// Optional: map /login -> /login.php (if user visits without .php)
if (!preg_match('/\.php$/i', $path)) {
  $candidate = $path . '.php';
  if (file_exists($candidate)) {
    $path = $candidate;
  }
}

// Security
if (str_contains($path, '..')) {
  http_response_code(400);
  exit('Bad request');
}

if (!file_exists($path)) {
  http_response_code(404);
  exit('Not Found');
}

require $path;
