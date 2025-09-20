 <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
   <a class="navbar-brand" href="index.php">
    <img src="IX.png" alt="logo" style="width:40px;">
   </a>
   <ul class="navbar-nav">
<?php
if ((isset($_SESSION['isadmin'])) && ($_SESSION['isadmin'] == 1)) {
	echo '
     <li class="nav-item">
       <a class="nav-link" href="radiusgroups.php">Radius Groups</a>
     </li>
     <li class="nav-item">
       <a class="nav-link" href="usergroup.php">User Group</a>';
     echo '</li>'; } else {
	$_SESSION['isadmin'] = 0; }
?>
     <li class="nav-item">
       <a class="nav-link" href="mygroups.php">My Groups</a>
     </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
<?php
if ((isset($_SESSION['fullname'])) {
	echo $_SESSION['fullname'];
      } else {
        echo 'Profile';
}
?>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="2fa.php">Two-Factor Authentication</a>
        <a class="dropdown-item" href="changepassword.php">Change Password</a>
        <a class="dropdown-item" href="logout.php">Logout</a>
      </div>
    </li> 
  </ul>
 </nav> 

