<?php
declare(strict_types=1);

require 'config.php';
ensure_session_started();
csrf_validate_post();

$user_id = $_SESSION['user_id'] ?? null;
if (empty($user_id)) {
  redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_to_cart'])) {
  redirect('shop.php');
}

$name   = trim((string)($_POST['product_name'] ?? ''));
$price  = trim((string)($_POST['product_price'] ?? ''));
$image  = trim((string)($_POST['product_image'] ?? ''));
$qtyRaw = (string)($_POST['product_quantity'] ?? '1');
$qty    = max(1, (int)$qtyRaw);

if ($name === '' || $price === '' || $image === '') {
  redirect('shop.php');
}

// Don’t duplicate the same item for the same user
$stmt = $conn->prepare('SELECT id FROM `cart` WHERE user_id = ? AND name = ? LIMIT 1');
$stmt->bind_param('is', $user_id, $name);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
  $_SESSION['flash'] = 'Already added to cart!';
  redirect('shop.php');
}

$stmt = $conn->prepare('INSERT INTO `cart` (user_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('issis', $user_id, $name, $price, $qty, $image);
$stmt->execute();

$_SESSION['flash'] = 'Product added to cart!';
redirect('shop.php');
