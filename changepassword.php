<?php 
include 'controllers/authController.php';

// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}
if ($_SESSION['2fa'] == '1' && $_SESSION['verify2fa'] != '1') {
    header('location: verify2fa.php');
}
if ($_SESSION['isadmin'] == '1' && $_SESSION['2fa'] != '1') {
    header('location: 2fa.php');
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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <title>Change Password</title>
</head>
<body>
<?php include "navbar.php"; ?>
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
        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert <?php echo $_SESSION['type'] ?>">
          <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['type']);
          ?>
        </div>
        <?php endif;?>
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
            <button type="submit" name="changepw-btn" class="btn btn-lg btn-block">Change Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
