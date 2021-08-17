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
  <title>My Groups</title>
</head>
<body>
<?php include "navbar.php"; ?>
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4 home-wrapper">
         <?php if (count($errors) > 0): ?>
           <div class="alert alert-danger">
             <?php foreach ($errors as $error): ?>
             <li>
               <?php echo $error; ?>
             </li>
             <?php endforeach;?>
           </div>
         <?php endif;?>

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

	<h3 class="text-center form-title">My Groups</h3>
        <?php if (!isset($_SESSION['verified'])): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            You need to verify your email address!
            Sign into your email account and click
            on the verification link we just emailed you
            at
            <strong><?php echo $_SESSION['email']; ?></strong>
          </div>
        <?php else: ?>
          <!-- <button class="btn btn-lg btn-primary btn-block">I'm verified!!!</button> -->
        <?php endif;?>
      </div>
    </div>
  </div>
  <div class="container">
<?php

	echo '<p>';
	echo '<h3>'.$_SESSION['username'].'</h3>';
	echo '</p>';
	echo '<table class="table table-striped">';
	echo '<tbody>';
	echo '<tr><th>Group Name</th><th>Priority</th></tr>';
	$queryrug = "SELECT * FROM radusergroup WHERE username = :username AND groupname != 'Disabled Users' ORDER BY priority";
	$stmtrug = $radconn->prepare($queryrug);
	$stmtrug->bindValue('username', $_SESSION['username']);
	$stmtrug->execute();
	while($rowrug = $stmtrug->fetch(PDO::FETCH_OBJ)){
	echo '            <tr>';
	echo '		    <td>'.$rowrug->groupname.'</td>';
	echo '		    <td>'.$rowrug->priority.'</td>';
	echo '		  </tr>';
	}
	echo '		</tbody>';
	echo '</table>';
?>
  </div>
</body>
</html>
