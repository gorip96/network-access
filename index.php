<?php 

include 'controllers/authController.php';
// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}
if (empty($_SESSION['isadmin'])) {
    $_SESSION['isadmin'] = '0';
}
if (empty($_SESSION['username'])) {
    $_SESSION['username'] = '';
}
if (empty($_SESSION['fullname'])) {
    $_SESSION['fullname'] = '';
}
if (empty($_SESSION['email'])) {
    $_SESSION['email'] = '';
}
if (empty($_SESSION['2fa'])) {
    $_SESSION['2fa'] = '';
} else {
    if ($_SESSION['2fa'] == '1' && $_SESSION['verify2fa'] != '1') {
        header('location: verify2fa.php');
}
}
if ($_SESSION['isadmin'] == '1' && $_SESSION['2fa'] != '1') {
    header('location: 2fa.php');
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // last request was more than 15 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header('location: login.php');
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
?>
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
  <title>IX Telecom Network Access</title>
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

        <h4>Welcome, <?php echo $_SESSION['fullname']; ?></h4>
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
        <?php if (isset($_SESSION['verified']) && (($_SESSION['isadmin']) == 1)): ?>
           <div class="container">
             <h2>Users List</h2>
             <table class="table table-striped">
               <thead>
                 <tr>
                   <th>Username</th>
                   <th>Admin</th>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Admin Access</th>'; }; ?>
                   <th>Network Access</th>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Network Permission</th>'; }; ?>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Reset Password</th>'; }; ?>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Delete</th>'; }; ?>
                 </tr>
               </thead>
               <tbody>
	<?php
		$query = "SELECT username, isadmin FROM users";
		$stmt = $conn->prepare($query);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_OBJ)){
			$queryrug = "SELECT * FROM radusergroup WHERE username = :username AND groupname = 'Disabled Users'";
			$stmtrug = $radconn->prepare($queryrug);
			$stmtrug->bindValue('username', $row->username);
			$stmtrug->execute();
			$resultrug = $stmtrug->fetchColumn();			
		echo '<tr>';
                echo '   <td>'.$row->username.'</td>';
                echo '   <td>';
		if ($row->isadmin == 1 ) {
		  echo '&#x1F7E2;</td>';
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><form method="post"><input type="hidden" name="update-user" value="'.$row->username.'"><button type="submit" class="btn  btn-primary btn-block" name="revokeadmin-btn">Revoke Admin</button></form></td>'; }
		} else {
		  echo '&#x1F534;</td>';
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><form method="post"><input type="hidden" name="update-user" value="'.$row->username.'"><button type="submit" class="btn  btn-primary btn-block" name="makeadmin-btn">Make Admin</button></form></td>'; }
		};
		echo '   <td>';
		if ($resultrug == 0) {
		  echo '&#x1F7E2;</td>';
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><form method="post"><input type="hidden" name="update-user" value="'.$row->username.'"><button type="submit" class="btn  btn-primary btn-block" name="raddisable-btn">Disable</button></form></td>'; }
                } else {
		  echo '&#x1F534;</td>'; 
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><form method="post"><input type="hidden" name="update-user" value="'.$row->username.'"><button type="submit" class="btn  btn-primary btn-block" name="radenable-btn">Enable</button></form></td>'; }
		}
		  if(($_SESSION['isadmin']) == 1) {
                  echo '<td><form method="post" action="resetpassword.php"><input type="hidden" name="resetpw-user" value="'.$row->username.'"><button type="submit" class="btn  btn-danger btn-block" name="resetpwuser-btn">Reset Password</button></form></td>';
                  echo '<td><form method="post"><input type="hidden" name="delete-user" value="'.$row->username.'"><button type="submit" class="btn  btn-danger btn-block" name="deluser-btn">Delete</button></form></td>'; }
                echo ' </tr>'; 
		
		}
	?>
             </table>
           </div>
        <?php endif;?>
</body>
</html>
