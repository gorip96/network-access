 <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
   <a class="navbar-brand" href="index.php">
    <img src="IX.png" alt="logo" style="width:40px;">
   </a>
   <ul class="navbar-nav">
<?php
if ($_SESSION['isadmin'] == 1) {
	echo '
     <li class="nav-item">
       <a class="nav-link" href="radiusgroups.php">Radius Groups</a>';
     </li>
     <li class="nav-item">
       <a class="nav-link" href="usergroup.php">User Group</a>
     echo '</li>'; }
?>
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

