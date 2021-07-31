<?php

session_start();

//Destroy entire session data.
session_destroy();

//redirect page to index.php
header('location:index.php');

?>

