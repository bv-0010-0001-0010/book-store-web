<?php
// This file expects config.php to already be required by the page.
ensure_session_started();

// Messages (safe)
if (isset($message)) {
  $msgs = is_array($message) ? $message : [$message];
  foreach ($msgs as $m) {
    echo '
      <div class="message">
        <span>' . e((string)$m) . '</span>
        <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
    ';
  }
}

// Session (safe)
$user_id    = (int)($_SESSION['user_id'] ?? 0);
$user_name  = (string)($_SESSION['user_name'] ?? '');
$user_email = (string)($_SESSION['user_email'] ?? '');

// Cart count (safe)
$cart_rows_number = 0;
if ($user_id > 0) {
  if ($stmt = $conn->prepare("SELECT COUNT(*) FROM `cart` WHERE user_id = ?")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($cart_rows_number);
    $stmt->fetch();
    $stmt->close();
  }
}
?>

<header class="header">

  <div class="header-1">
    <div class="flex">
      <div class="share">
        <a href="#" class="fab fa-facebook-f"></a>
        <a href="#" class="fab fa-twitter"></a>
        <a href="#" class="fab fa-instagram"></a>
        <a href="#" class="fab fa-linkedin"></a>
      </div>

      <p>
        <?php if ($user_id > 0) { ?>
          welcome, <span><?php echo e($user_name); ?></span>
        <?php } else { ?>
          new <a href="login.php">login</a> | <a href="register.php">register</a>
        <?php } ?>
      </p>
    </div>
  </div>

  <div class="header-2">
    <div class="flex">

      <a href="index.php" class="logo">Bookly<span>.</span></a>

      <nav class="navbar">
        <a href="index.php">home</a>
        <a href="about.php">about</a>
        <a href="shop.php">shop</a>
        <a href="contact.php">contact</a>
        <a href="orders.php">orders</a>
      </nav>

      <div class="icons">
        <div id="menu-btn" class="fas fa-bars"></div>
        <a href="search_page.php" class="fas fa-search"></a>
        <div id="user-btn" class="fas fa-user"></div>
        <a href="cart.php"> <i class="fas fa-shopping-cart"></i>
          <span>(<?php echo (int)$cart_rows_number; ?>)</span>
        </a>
      </div>

      <div class="user-box">
        <?php if ($user_id > 0) { ?>
          <p>username : <span><?php echo e($user_name); ?></span></p>
          <p>email : <span><?php echo e($user_email); ?></span></p>
          <a href="logout.php" class="delete-btn">logout</a>
        <?php } else { ?>
          <p>new <a href="login.php">login</a> | <a href="register.php">register</a></p>
        <?php } ?>
      </div>

    </div>
  </div>

</header>
