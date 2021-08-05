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

	<h3 class="text-center form-title">User Group</h3>
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

	$querygroups = "SELECT * FROM radiusgroups";
	$stmtgroups = $conn->prepare($querygroups);

	while($row = $stmt->fetch(PDO::FETCH_OBJ)){
	echo '<p>';
	echo '<h3>'.$row->username.'</h3>';
	echo '<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#newusergroup-'.$row->id.'" aria-expanded="false" aria-controls="newusergroup-'.$row->id.'">New User Group Binding</button>';
	echo '</p>';
	echo '    <div class="collapse" id="newusergroup-'.$row->id.'">';
	echo '        <table class="table">';
	echo '          <thead><tr>';
	echo '            <th>Groups</th><th>Priority</th><th></th>';
	echo '          </tr></thead>';
	echo '		<tbody><tr>';
	echo '            <form method="post">';
	echo '		    <input type="hidden" name="username" value="'.$row->username.'">';
	echo '   	    <td><select name="groupname" class="form-control">';
	echo '			<option value="" disabled selected>Select group to add</option>';
	$stmtgroups->execute();
	while($rowgroups = $stmtgroups->fetch(PDO::FETCH_OBJ)){
	echo '			<option value="'.$rowgroups->groups.'">'.$rowgroups->groups.'</option>';
	}
	echo '		    </select></td>';
	echo '		    <td><input type="text" size="2" name="priority" class="form-control"></td>';
	echo '		    <td><button type="submit" class="btn  btn-primary btn-block" name="addusergroup-btn">Add Group</button></td>';
	echo '		  </form>';
	echo '		</tr></tbody>';
	echo '        </table>';
	echo '    </div>';
	echo '<h5>User Groups</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Groups</th><th>Priority</th><th>Delete</th>';
	echo '  </tr></thead>';
	echo '		<tbody>';
	$queryrug = "SELECT * FROM radusergroup WHERE username = :username AND groupname != 'Disabled Users' ORDER BY priority";
	$stmtrug = $radconn->prepare($queryrug);
	$stmtrug->bindValue('username', $row->username);
	$stmtrug->execute();
	while($rowrug = $stmtrug->fetch(PDO::FETCH_OBJ)){
	echo '            <tr><form method="post">';
	echo '		    <input type="hidden" name="username" value="'.$row->username.'">';
	echo '		    <input type="hidden" name="groupname" value="'.$rowrug->groupname.'">';
	echo '		    <td>'.$rowrug->groupname.'</td>';
	echo '		    <td>'.$rowrug->priority.'</td>';
	echo '		    <td><button type="submit" class="btn  btn-danger btn-block" name="delusergroup-btn">Delete</button></td>';
	echo '		  </form></tr>';
	}
	echo '		</tbody>';
	echo '</table>';
	}
?>
  </div>
</body>
</html>
