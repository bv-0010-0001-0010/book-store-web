<?php
include 'config.php';
ensure_session_started();
csrf_validate_post();

if (isset($_POST['submit'])) {
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message[] = 'Enter a valid email.';
  } else {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, user_type FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;

    $ok = false;
    if ($row) {
      if (password_verify($pass, $row['password'])) {
        $ok = true;
      } else {
        if (hash_equals(md5($pass), (string)$row['password'])) {
          $ok = true;
          $newHash = password_hash($pass, PASSWORD_DEFAULT);
          $u = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
          mysqli_stmt_bind_param($u, "si", $newHash, $row['id']);
          mysqli_stmt_execute($u);
        }
      }
    }

    if ($ok) {
      session_regenerate_id(true);

      if ($row['user_type'] === 'admin') {
        $_SESSION['admin_name'] = $row['name'];
        $_SESSION['admin_email'] = $row['email'];
        $_SESSION['admin_id'] = (int)$row['id'];
        redirect('admin_page.php');
      } else {
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_id'] = (int)$row['id'];
        redirect('index.php'); 
      }
    } else {
      $message[] = 'Incorrect email or password!';
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
   <title>login</title>

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
      <h3>login now</h3>
      <input type="email" name="email" placeholder="enter your email" required class="box">
      <input type="password" name="password" placeholder="enter your password" required class="box">
      <input type="submit" name="submit" value="login now" class="btn">
      <p>don't have an account? <a href="register.php">register now</a></p>
   </form>

</div>

</body>
</html>