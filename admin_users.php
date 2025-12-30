<?php

require 'config.php';
ensure_session_started();
csrf_validate_post();

$admin_id = $_SESSION['admin_id'] ?? null;
if (empty($admin_id)) {
   redirect('login.php');
}

if (isset($_POST['delete_user_id'])) {
  $delete_id = (int)($_POST['delete_user_id'] ?? 0);

  $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $delete_id);
  mysqli_stmt_execute($stmt);

  redirect('admin_users.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="users">

   <h1 class="title"> user accounts </h1>

   <div class="box-container">
      <?php
         $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
         while($fetch_users = mysqli_fetch_assoc($select_users)){
      ?>
      <div class="box">
         <p> user id : <span><?php echo $fetch_users['id']; ?></span> </p>
         <p> username : <span><?php echo $fetch_users['name']; ?></span> </p>
         <p> email : <span><?php echo $fetch_users['email']; ?></span> </p>
         <p> user type : <span style="color:<?php if($fetch_users['user_type'] == 'admin'){ echo 'var(--orange)'; } ?>"><?php echo $fetch_users['user_type']; ?></span> </p>
         <form method="post">
            <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
            <input type="hidden" name="delete_user_id" value="<?php echo (int)$fetch_users['id']; ?>">
            <button type="submit" class="delete-btn" onclick="return confirm('delete this user?');">delete user</button>
         </form>
      </div>
      <?php
         };
      ?>
   </div>

</section>









<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>