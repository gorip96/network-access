<?php

session_start();
$mailtype = $_SESSION['mailtype'];

switch ($mailtype) {
	case "signup";
		$subject = 'Verify your email';
        	$content = '<!DOCTYPE html>
    				<html lang="en">

    				<head>
      				  <meta charset="UTF-8">
      				  <title>Email Verification</title>
      				  <style>
        			  .wrapper {
        			    padding: 20px;
        			    color: #444;
        			    font-size: 1.3em;
        			  }
        			  a {
        			    background: #592f80;
        			    text-decoration: none;
        			    padding: 8px 15px;
        			    border-radius: 5px;
        			    color: #fff;
      				  }
      				  </style>
    				</head>

    				<body>
    				  <div class="wrapper">
    				    <p>Thank you for signing up on our site. Please click on the link below to verify your account:.</p>
    				    <a href="https://network-access.ixtelecom.net/verify_email.php?token=' . $token . '">Verify Email!</a>
    				  </div>
    				</body>
    				</html>';

}
?>
