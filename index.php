<?php 

include 'controllers/authController.php';
// redirect user to login page if they're not logged in
if (empty($_SESSION['id'])) {
    header('location: login.php');
}
session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // last request was more than 15 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
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
  <title>User verification system PHP</title>
</head>

<body>
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
        <a href="logout.php" style="color: red">Logout</a>
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
                   <th>Firstname</th>
                   <th>Lastname</th>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<th>Email</th>'; }; ?>
                 </tr>
               </thead>
               <tbody>
                 <tr>
                   <td>John</td>
                   <td>Doe</td>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<td>john@example.com</td>'; }; ?>
                 </tr>
                 <tr>
                   <td>Mary</td>
                   <td>Moe</td>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<td>mary@example.com</td>'; }; ?>
                 </tr>
                 <tr>
                   <td>July</td>
                   <td>Dooley</td>
                   <?php if(($_SESSION['isadmin']) == 1) { echo  '<td>july@example.com</td> '; }; ?>
                 </tr>
               </tbody>
             </table>
           </div>
</body>
</html>
