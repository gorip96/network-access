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
  <title>User Groups</title>
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
<?php

	$query = "SELECT * FROM users";
	$stmt = $conn->prepare($query);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_OBJ)){
	echo '<p>';
	echo '<h3>'.$row->username.'</h3>';
	echo '<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#newusergroup-'.$row->id.'" aria-expanded="false" aria-controls="newusergroup-'.$row->id.'">New User Group BBinding</button>';
	echo '</p>';
	// echo '<div class="row">';
	// echo '  <div class="col">';
	echo '    <div class="collapse" id="newusergroup-'.$row->id.'">';
	// echo '      <div>';
	echo '        <table class="table">';
	echo '          <thead><tr>';
	echo '            <th>Groups</th><th></th>';
	echo '          </tr></thead>';
	echo '		<tbody><tr>';
	echo '            <form method="post">';
	echo '		    <input type="hidden" name="username" value="'.$row->username.'">';
	echo '		    <td><input type="text" name="radgroup" class="form-control"></td>';
	echo '		    <td><button type="submit" class="btn  btn-primary btn-block" name="addgroupcheck-btn">Add Group</button></td>';
	echo '		  </form>';
	echo '		</tr></tbody>';
	echo '        </table>';
	// echo '      </div>';
	echo '    </div>';
	// echo '  </div>';
	// echo '  <div class="col">';
	// echo '  </div>';
	// echo '</div>';
	echo '<h5>Usergroups</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Groups</th><th>Delete</th>';
	echo '  </tr></thead>';
	echo '		<tbody>';
	$queryrug = "SELECT * FROM radusergroup WHERE username = :username AND groupname != 'Disabled Users'";
	$stmtrug = $radconn->prepare($queryrug);
	$stmtrug->bindValue('username', $row->username);
	$stmtrug->execute();
	while($rowrug = $stmtrug->fetch(PDO::FETCH_OBJ)){
	echo '            <tr><form method="post">';
	echo '		    <input type="hidden" name="username" value="'.$row->username.'">';
	echo '		    <td>'.$rowrug->groupname.'</td>';
	echo '		    <td><button type="submit" class="btn  btn-primary btn-block" name="delgroupcheck-btn">Delete</button></td>';
	echo '		  </form></tr>';
	}
	echo '		</tbody>';
	echo '</table>';
	}
?>
  </div>
</body>
</html>
