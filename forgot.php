<?php
include 'controllers/authController.php';
include 'config.php';

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
  <title>Reset Password</title>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4 form-wrapper auth login">
        <h3 class="text-center form-title">Forgot Password</h3>
         <?php if (count($errors) > 0): ?>
           <div class="alert alert-danger">
             <?php foreach ($errors as $error): ?>
             <li>
               <?php echo $error; ?>
             </li>
             <?php endforeach;?>
           </div>
         <?php endif;?>
        <form action="resetpassword.php" method="post">
          <div class="form-group">
            <label>Input Your Registered Email</label>
            <input type="text" name="email" class="form-control form-control-lg" value="<?php echo $email; ?>">
          </div>
          <div class="form-group">
            <button type="submit" name="login-btn" class="btn btn-lg btn-block">Send e-Mail</button>
          </div>
        <input type="hidden" name="recaptcha_response" value="" id="recaptchaResponse">
        </form>
      </div>
    </div>
     <script>
     grecaptcha.ready(function(){
         grecaptcha.execute('<?php echo $recaptcha_site_key; ?>', { action:'submit'}).then(function (token){
             var recaptchaResponse=document.getElementById('recaptchaResponse');
             recaptchaResponse.value=token;
         })
     })
     </script>
  </div>
</body>
</html>

