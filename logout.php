<?php

require 'config.php';
ensure_session_started();

$_SESSION = [];

// If you want to fully nuke the session cookie:
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params['path'] ?? '/',
    $params['domain'] ?? '',
    (bool)($params['secure'] ?? false),
    (bool)($params['httponly'] ?? true)
  );
}

session_destroy();
redirect('login.php');

?>