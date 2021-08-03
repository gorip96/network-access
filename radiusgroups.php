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
  <title>Radius Groups</title>
</head>
<body>
<?php include "navbar.php"; ?>
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
        <?php if (!$_SESSION['verified']): ?>
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
    <br>
    <div class="float-right">
      <a href="#addgroup"class="btn btn-primary btn-block" name="newgroup-btn" data-toggle="collapse">New Group</a><br>
	<div id="addgroup" class="collapse">
	<form method="post">
	  <div><h4>Group Name </h4></div>
	  <div>  <input type="text" name="radgroup" class="form-control"></div>
	  <div>  <button type="submit" class="btn btn-primary btn-block" name="newgroup-btn">Add Group</button></div>
	</form>
	</div>
    </div>
    <br>
  </div>
  <div class="container">
<?php

	$query = "SELECT * FROM radiusgroups";
	$stmt = $conn->prepare($query);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_OBJ)){
	echo '<div><h3>'.$row->groups.'</h3></div><div>test</div><div>test2</div>';
	echo '<h5>Group Check</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Attribute</th><th>Op</th><th>Value</th>';
	echo '  </tr></thead>';
	echo '</table>';
	echo '<h5>Group Reply</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Attribute</th><th>Op</th><th>Value</th>';
	echo '  </tr></thead>';
	echo '</table>';
	}
?>
  </div>
</body>
</html>
