<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if (isset($_POST['add_to_cart'])) {
  csrf_validate_post();

  $product_id = (int)($_POST['product_id'] ?? 0);
  $qty = (int)($_POST['product_quantity'] ?? 1);
  if ($qty < 1) $qty = 1;
  if ($qty > 99) $qty = 99;

  // Fetch trusted product data from DB
  $stmt = mysqli_prepare($conn, "SELECT name, price, image FROM products WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $product_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $p = $res ? mysqli_fetch_assoc($res) : null;

  if (!$p) {
    $message[] = 'Product not found.';
  } else {
    // If already in cart, increase quantity instead of “already added”
    $stmt = mysqli_prepare($conn, "SELECT id, quantity FROM cart WHERE user_id = ? AND name = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $p['name']);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($existing) {
      $newQty = (int)$existing['quantity'] + $qty;
      if ($newQty > 99) $newQty = 99;

      $u = mysqli_prepare($conn, "UPDATE cart SET quantity = ?, price = ?, image = ? WHERE id = ? AND user_id = ?");
      mysqli_stmt_bind_param($u, "idsii", $newQty, $p['price'], $p['image'], $existing['id'], $user_id);
      mysqli_stmt_execute($u);

      $message[] = 'Cart updated!';
    } else {
      $i = mysqli_prepare($conn, "INSERT INTO cart(user_id, name, price, quantity, image) VALUES(?,?,?,?,?)");
      mysqli_stmt_bind_param($i, "isdis", $user_id, $p['name'], $p['price'], $qty, $p['image']);
      mysqli_stmt_execute($i);

      $message[] = 'Product added to cart!';
    }
  }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>search page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>search page</h3>
   <p> <a href="index.php">home</a> / search </p>
</div>

<section class="search-form">
   <form action="" method="post">
      <input type="text" name="search" placeholder="search products..." class="box">
      <input type="submit" name="submit" value="search" class="btn">
   </form>
</section>

<section class="products" style="padding-top: 0;">

   <div class="box-container">
   <?php
      if (isset($_POST['submit'])) {
      csrf_validate_post();

      $search_item = trim($_POST['search'] ?? '');
         if ($search_item === '') {
            echo '<p class="empty">search something!</p>';
         } else {
            $like = '%' . $search_item . '%';
            $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE name LIKE ?");
            mysqli_stmt_bind_param($stmt, "s", $like);
            mysqli_stmt_execute($stmt);
            $select_products = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($select_products) > 0) {
               while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                  ?>
                  <form action="" method="post" class="box">
                     <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">

                     <img src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="" class="image">
                     <div class="name"><?php echo $fetch_product['name']; ?></div>
                     <div class="price">$<?php echo $fetch_product['price']; ?>/-</div>
                     <input type="number"  class="qty" name="product_quantity" min="1" value="1">
                     <input type="hidden" name="product_id" value="<?php echo (int)$fetch_products['id']; ?>">
                     <input type="submit" class="btn" value="add to cart" name="add_to_cart">
                  </form>
                  <?php
               }
            } else {
               echo '<p class="empty">no result found!</p>';
            }
         }
      }

   ?>
   </div>
  

</section>









<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>