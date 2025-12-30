<?php
require 'config.php';
ensure_session_started();
csrf_validate_post();

if (isset($_POST['submit'])) {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');
  $cpass = (string)($_POST['cpassword'] ?? '');

  if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message[] = 'Please enter a valid name and email.';
  } elseif (strlen($pass) < 8) {
    $message[] = 'Password must be at least 8 characters.';
  } elseif ($pass !== $cpass) {
    $message[] = 'Confirm password not matched!';
  } else {
    // Check if email exists (email-only check, not email+password like before) :contentReference[oaicite:13]{index=13}
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) > 0) {
      $message[] = 'User already exists!';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $user_type = 'user'; // force user, never accept admin from UI :contentReference[oaicite:14]{index=14}

      $stmt = mysqli_prepare($conn, "INSERT INTO users(name, email, password, user_type) VALUES(?,?,?,?)");
      mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hash, $user_type);
      mysqli_stmt_execute($stmt);

      redirect('login.php');
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
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>



<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
   
<div class="form-container">

   <form action="" method="post">
      <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
      <h3>register now</h3>
      <input type="text" name="name" placeholder="enter your name" required class="box">
      <input type="email" name="email" placeholder="enter your email" required class="box">
      <input type="password" name="password" placeholder="enter your password" required class="box">
      <input type="password" name="cpassword" placeholder="confirm your password" required class="box">
      <input type="submit" name="submit" value="register now" class="btn">
      <p>already have an account? <a href="login.php">login now</a></p>
   </form>

</div>

</body>
</html>