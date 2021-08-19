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
  <title>Radius Groups</title>
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

	<h3 class="text-center form-title">Radius Groups</h3>
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

	$query = "SELECT * FROM radiusgroups ORDER BY groups";
	$stmt = $conn->prepare($query);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_OBJ)){
	echo '<p>';
	echo '<h3>'.$row->groups.'</h3>';
	echo '<div class="row">';
	echo '<div><button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#newgroupcheck-'.$row->id.'" aria-expanded="false" aria-controls="newgroupcheck-'.$row->id.'">New Group Check</button></div>';
	echo '<div><button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#newgroupreply-'.$row->id.'" aria-expanded="false" aria-controls="newgroupreply-'.$row->id.'">New Group Reply</button></div>';
	echo '<div><form method="post"><input type="hidden" name="radgroup" value="'.$row->groups.'"><button type="submit" class="btn  btn-danger btn-block" name="delgroup-btn">Delete Group</button></form></div>';
	echo '</div></p>';
	// echo '<div class="row">';
	// echo '  <div class="col">';
	echo '    <div class="collapse" id="newgroupcheck-'.$row->id.'">';
	// echo '      <div>';
	echo '        <table class="table">';
	echo '          <thead><tr>';
	echo '            <th>Attribute</th><th>Op</th><th>Value</th><th></th>';
	echo '          </tr></thead>';
	echo '		<tbody><tr>';
	echo '            <form method="post">';
	echo '		    <input type="hidden" name="radgroup" value="'.$row->groups.'">';
	echo '		    <td><input type="text" name="attribute" class="form-control"></td>';
	echo '		    <td><input type="text" size="2" name="op" class="form-control"></td>';
	echo '		    <td><input type="text" name="value" class="form-control"></td>';
	echo '		    <td><button type="submit" class="btn  btn-primary btn-block" name="addgroupcheck-btn">Add Group Check</button></td>';
	echo '		  </form>';
	echo '		</tr></tbody>';
	echo '        </table>';
	// echo '      </div>';
	echo '    </div>';
	// echo '  </div>';
	// echo '  <div class="col">';
	echo '    <div class="collapse" id="newgroupreply-'.$row->id.'">';
	// echo '      <div>';
	echo '        <table class="table">';
	echo '          <thead><tr>';
	echo '            <th>Attribute</th><th>Op</th><th>Value</th><th></th>';
	echo '          </tr></thead>';
	echo '		<tbody><tr>';
	echo '            <form method="post">';
	echo '		    <input type="hidden" name="radgroup" value="'.$row->groups.'">';
	echo '		    <td><input type="text" name="attribute" class="form-control"></td>';
	echo '		    <td><input type="text" size="2" name="op" class="form-control"></td>';
	echo '		    <td><input type="text" name="value" class="form-control"></td>';
	echo '		    <td><button type="submit" class="btn  btn-primary btn-block" name="addgroupreply-btn">Add Group Reply</button></td>';
	echo '		  </form>';
	echo '		</tr></tbody>';
	echo '        </table>';
	// echo '      </div>';
	echo '    </div>';
	// echo '  </div>';
	// echo '</div>';
	echo '<h5>Group Check</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Attribute</th><th>Op</th><th>Value</th><th>Delete</th>';
	echo '  </tr></thead>';
	echo '		<tbody>';
	$queryrgc = "SELECT * FROM radgroupcheck WHERE groupname = :groupname";
	$stmtrgc = $radconn->prepare($queryrgc);
	$stmtrgc->bindValue('groupname', $row->groups);
	$stmtrgc->execute();
	while($rowrgc = $stmtrgc->fetch(PDO::FETCH_OBJ)){
	echo '            <tr><form method="post">';
	echo '		    <input type="hidden" name="radgroup" value="'.$row->groups.'">';
	echo '		    <input type="hidden" name="attribute" value="'.$rowrgc->attribute.'">';
	echo '		    <td>'.$rowrgc->attribute.'</td>';
	echo '		    <td>'.$rowrgc->op.'</td>';
	echo '		    <td>'.$rowrgc->value.'</td>';
	echo '		    <td><button type="submit" class="btn  btn-danger btn-block" name="delgroupcheck-btn">Delete</button></td>';
	echo '		  </form></tr>';
	}
	echo '		</tbody>';
	echo '</table>';
	echo '<h5>Group Reply</h5>';
	echo '<table class="table table-striped">';
	echo '  <thead><tr>';
	echo '    <th>Attribute</th><th>Op</th><th>Value</th><th>Delete</th>';
	echo '  </tr></thead>';
	echo '		<tbody>';
	$queryrgr = "SELECT * FROM radgroupreply WHERE groupname = :groupname ORDER BY id";
	$stmtrgr = $radconn->prepare($queryrgr);
	$stmtrgr->bindValue('groupname', $row->groups);
	$stmtrgr->execute();
	while($rowrgr = $stmtrgr->fetch(PDO::FETCH_OBJ)){
	echo '            <tr><form method="post">';
	echo '		    <input type="hidden" name="radgroup" value="'.$row->groups.'">';
	echo '		    <input type="hidden" name="id" value="'.$rowrgr->id.'">';
	echo '		    <td>'.$rowrgr->attribute.'</td>';
	echo '		    <td>'.$rowrgr->op.'</td>';
	echo '		    <td>'.$rowrgr->value.'</td>';
	echo '		    <td><button type="submit" class="btn  btn-danger btn-block" name="delgroupreply-btn">Delete</button></td>';
	echo '		  </form></tr>';
	}
	echo '		</tbody>';
	echo '</table>';
	}
?>
  </div>
</body>
</html>
