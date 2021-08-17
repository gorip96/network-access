<?php
include 'controllers/authController.php';

// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}

?><!DOCTYPE html>
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
  <title>Two-Factor Authentication</title>
</head>
<body>
<?php

include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';
include "navbar.php";
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($query);
	$stmt->bindValue('username', $_SESSION['username']);
        $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_OBJ);

	$g = new \Google\Authenticator\GoogleAuthenticator();
	$secret = $row->token;
?>

  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4 home-wrapper">

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

        <h4>Welcome, <?php echo $_SESSION['username']; ?></h4>
        <?php if (!isset($_SESSION['verified'])): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            You need to verify your email address!
            Sign into your email account and click
            on the verification link we just emailed you
            at
            <strong><?php echo $_SESSION['email']; ?></strong>
          </div>
        <?php else: ?>
	<?php if (($row->twoFA) == '0') {
          echo '<button type="button" class="btn btn-primary btn-block" data-toggle="collapse" data-target="#2fa" aria-expanded="false" aria-controls="2fa">Enable 2FA</button>';
	  echo '<div class="collapse" id="2fa">';
	  echo '<img src="'.$g->getURL(''.$row->username.'', $systemhostname, $secret).'" />';
	  echo '</div>';
	} else {
          echo '<button type="submit" class="btn btn-danger btn-block" name="disable2fa-btn">Disable 2FA</button>';
	}  ?>
        <?php endif;?>
      </div>
    </div>
  </div>
</body>
</html>
