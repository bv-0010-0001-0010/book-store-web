<?php
// This file expects config.php to already be required by the page.
ensure_session_started();

$user_id = $_SESSION['user_id'] ?? null;
$isLoggedIn = !empty($user_id);

$cart_rows_number = 0;
if ($isLoggedIn) {
  $stmt = $conn->prepare('SELECT COUNT(*) AS c FROM `cart` WHERE user_id = ?');
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    $cart_rows_number = (int)$row['c'];
  }
  $stmt->close();
}

$user_name  = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
?>

<header class="header">

  <div class="flex">

    <a href="index.php" class="logo">Bookly<span>.</span></a>

    <nav class="navbar">
      <a href="index.php">home</a>
      <a href="about.php">about</a>
      <a href="shop.php">shop</a>
      <a href="contact.php">contact</a>
      <?php if ($isLoggedIn) { ?>
        <a href="orders.php">orders</a>
      <?php } ?>
    </nav>

    <div class="icons">
      <div id="menu-btn" class="fas fa-bars"></div>
      <a href="search_page.php" class="fas fa-search"></a>
      <?php if ($isLoggedIn) { ?>
        <div id="user-btn" class="fas fa-user"></div>
        <a href="cart.php" class="fas fa-shopping-cart"><span>(<?php echo (int)$cart_rows_number; ?>)</span></a>
      <?php } ?>
    </div>

    <div class="account-box">
      <?php if ($isLoggedIn) { ?>
        <p>username : <span><?php echo e($user_name); ?></span></p>
        <p>email : <span><?php echo e($user_email); ?></span></p>
        <a href="logout.php" class="delete-btn">logout</a>
      <?php } else { ?>
        <div>new <a href="login.php">login</a> | <a href="register.php">register</a></div>
      <?php } ?>
    </div>

  </div>

</header>

<?php
if (!empty($message) && is_array($message)) {
  foreach ($message as $msg) {
    echo '<div class="message"><span>' . e((string)$msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
  }
}
?>
