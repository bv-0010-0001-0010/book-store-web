<?php

require 'config.php';
ensure_session_started();
csrf_validate_post();

$admin_id = $_SESSION['admin_id'] ?? null;
if (empty($admin_id)) {
   redirect('login.php');
}

if (isset($_POST['delete_message_id'])) {
  $delete_id = (int)($_POST['delete_message_id'] ?? 0);

  $stmt = mysqli_prepare($conn, "DELETE FROM message WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $delete_id);
  mysqli_stmt_execute($stmt);

  redirect('admin_contacts.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>messages</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title"> messages </h1>

   <div class="box-container">
   <?php
      $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
      if(mysqli_num_rows($select_message) > 0){
         while($fetch_message = mysqli_fetch_assoc($select_message)){
      
   ?>
   <div class="box">
      <p> user id : <span><?php echo $fetch_message['user_id']; ?></span> </p>
      <p> name : <span><?php echo $fetch_message['name']; ?></span> </p>
      <p> number : <span><?php echo $fetch_message['number']; ?></span> </p>
      <p> email : <span><?php echo $fetch_message['email']; ?></span> </p>
      <p> message : <span><?php echo $fetch_message['message']; ?></span> </p>
      <form method="post">
         <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
         <input type="hidden" name="delete_message_id" value="<?php echo (int)$fetch_message['id']; ?>">
         <button type="submit" class="delete-btn" onclick="return confirm('delete this message?');">delete message</button>
      </form>
   </div>
   <?php
      };
   }else{
      echo '<p class="empty">you have no messages!</p>';
   }
   ?>
   </div>

</section>









<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>