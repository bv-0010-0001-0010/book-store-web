<?php
// This file expects config.php to already be required by the page.
ensure_session_started();

$admin_name  = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
?>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">home</a>
         <a href="admin_products.php">products</a>
         <a href="admin_orders.php">orders</a>
         <a href="admin_users.php">users</a>
         <a href="admin_contacts.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="account-box">
         <p>username : <span><?= e($admin_name) ?></span></p>
         <?php if ($admin_email !== ''): ?>
         <p>email : <span><?= e($admin_email) ?></span></p>
         <?php endif; ?>
         <a href="logout.php" class="delete-btn">logout</a>
      </div>

   </div>

</header>
