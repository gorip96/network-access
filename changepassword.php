<?php 
include 'controllers/authController.php';

// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.min.css" />
  <link rel="stylesheet" href="main.css">
  <title>Change Password</title>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4 form-wrapper auth">
        <h3 class="text-center form-title">Change Password</h3>
         <?php if (count($errors) > 0): ?>
           <div class="alert alert-danger">
             <?php foreach ($errors as $error): ?>
             <li>
               <?php echo $error; ?>
             </li>
             <?php endforeach;?>
           </div>
         <?php endif;?>  
        <form action="changepassword.php" method="post">
          <div class="form-group">
            <label>Old Password</label>
            <input type="password" name="oldpassword" class="form-control form-control-lg">
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="form-control form-control-lg">
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="passwordConf" class="form-control form-control-lg">
          </div>
          <div class="form-group">
            <button type="submit" name="reset-btn" class="btn btn-lg btn-block">Reset Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
