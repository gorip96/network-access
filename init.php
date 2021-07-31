<?php
require "config.php";
	$conn = new PDO("mysql:host=$dbhost;dbname=$dbname;port=$dbport", "$dbuser", "$dbpass");
	// $conn = new PDO('mysql:host=localhost;dbname=engineering', 'engineering', 'IXT3l3c0m');
?>
