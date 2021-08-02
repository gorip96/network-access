<?php 

include 'controllers/authController.php';
// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // last request was more than 15 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
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
 <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
   <a class="navbar-brand" href="index.php">
    <img src="IX.png" alt="logo" style="width:40px;">
   </a>
   <ul class="navbar-nav">
     <li class="nav-item">
       <a class="nav-link" href="#">Link</a>
     </li>
     <li class="nav-item">
       <a class="nav-link" href="#">Link</a>
     </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
        Profile
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="changepassword.php">Change Password</a>
        <a class="dropdown-item" href="logout.php">Logout</a>
      </div>
    </li>   
  </ul>
 </nav>
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
          <button class="btn btn-lg btn-primary btn-block">I'm verified!!!</button>
        <?php endif;?>
      </div>
    </div>
  </div>
           <div class="container">
             <h2>Users List</h2>
             <!-- <p>The .table-striped class adds zebra-stripes to a table:</p> -->
             <table class="table table-striped">
               <thead>
                 <tr>
                   <th>Username</th>
                   <th>Admin</th>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Make / Revoke Admin</th>'; }; ?>
                   <th>Network Access Status</th>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Suspend / Unsuspend</th>'; }; ?>
                 </tr>
               </thead>
               <tbody>
	<?php
		$query = "SELECT username, isadmin FROM users";
		$stmt = $conn->prepare($query);
		$stmt->execute();
		// $result = $stmt->fetch(PDO::FETCH_OBJ);

		// while($row = $stmt->execute()){
		while($row = $stmt->fetch(PDO::FETCH_OBJ)){
		// $result = $stmt->fetch(PDO::FETCH_OBJ);
		echo '<tr>';
                echo '   <td>'.$row->username.'</td>';
                echo '   <td>';
		if ($row->isadmin == 1 ) {
		  echo '&#x1F7E2;</td>';
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><button class="btn  btn-primary btn-block" name="revokeadmin-btn">Revoke Admin</button></td>'; };
		} else {
		  echo '&#x1F534;</td>';
		  if(($_SESSION['isadmin']) == 1) {
		  echo '<td><button class="btn  btn-primary btn-block" name="makeadmin-btn">Make Admin</button></td>'; };
		};
                echo '   <td>Doe</td>';
                if(($_SESSION['isadmin']) == 1) { echo  '<td>john@example.com</td>'; };
                echo ' </tr>'; 
		}
	?>
             </table>
           </div>
</body>
</html>
