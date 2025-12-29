<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

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
   <title>home</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="home">

   <div class="content">
      <h3>Hand Picked Book to your door.</h3>
      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Excepturi, quod? Reiciendis ut porro iste totam.</p>
      <a href="about.php" class="white-btn">discover more</a>
   </div>

</section>

<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

      <?php  
         $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
     <form action="" method="post" class="box">
      <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
      <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
      <div class="name"><?php echo $fetch_products['name']; ?></div>
      <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
      <input type="number" min="1" name="product_quantity" value="1" class="qty">
      <input type="hidden" name="product_id" value="<?php echo (int)$fetch_products['id']; ?>">
      <input type="submit" value="add to cart" name="add_to_cart" class="btn">
     </form>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

   <div class="load-more" style="margin-top: 2rem; text-align:center">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>

<section class="about">

   <div class="flex">

      <div class="image">
         <img src="images/about-img.jpg" alt="">
      </div>

      <div class="content">
         <h3>about us</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Impedit quos enim minima ipsa dicta officia corporis ratione saepe sed adipisci?</p>
         <a href="about.php" class="btn">read more</a>
      </div>

   </div>

</section>

<section class="home-contact">

   <div class="content">
      <h3>have any questions?</h3>
      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Atque cumque exercitationem repellendus, amet ullam voluptatibus?</p>
      <a href="contact.php" class="white-btn">contact us</a>
   </div>

</section>





<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>