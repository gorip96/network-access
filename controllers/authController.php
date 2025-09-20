<?php

require "config.php";
require 'vendor/autoload.php';

require_once ("lib/MailService.php");

include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

use Base32\Base32;

session_start();
$username = "";
$email = "";
$errors = [];

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

$conn = new PDO("mysql:host=$dbhost;dbname=$dbname;port=$dbport", "$dbuser", "$dbpass");
$radconn = new PDO("mysql:host=$raddbhost;dbname=$raddbname;port=$raddbport", "$raddbuser", "$raddbpass");

// Sign Up User

if (isset($_POST['signup-btn'])) {
    if (empty($_POST['fullname'])) {
        $errors['fullname'] = 'Full name required';
    }
    if (empty($_POST['username'])) {
        $errors['username'] = 'Username required';
    } else if (strrpos($_POST["username"], ' ') !== false) {
    	$errors['username']  = 'Username must not contain space';
    } 
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email required';
    }
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password required';
    }
    if (isset($_POST['password']) && $_POST['password'] !== $_POST['passwordConf']) {
        $errors['passwordConf'] = 'The two passwords do not match';
    }

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // generate unique token
    $password = $_POST['password'];
    $code2fa = Base32::encode(rand());

    // Check if email already exists
    $countuser = "SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1";
    $countstmt = $conn->prepare($countuser);
    $countstmt->bindValue('username', $_POST['username']);
    $countstmt->bindValue('email', $_POST['email']);
    $countstmt->execute();
    $result = $countstmt->fetchColumn();
    if ($result > 0) {
        $errors['email'] = "Username or Email already exists";
    }

    if (count($errors) === 0) {
        $query = "INSERT INTO users(name, username, password, email, token, code2fa) values(:fullname, :username, :password, :email, :token, :code2fa)";
        $stmt = $conn->prepare($query);
	$stmt->bindValue('fullname', $_POST['fullname']);
	$stmt->bindValue('username', $_POST['username']);
	$stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
	$stmt->bindValue('email', $_POST['email']);
	$stmt->bindValue('token', $token);
	$stmt->bindValue('code2fa', $code2fa);
	$result = $stmt->execute();

	$query = "INSERT INTO radcheck(username,attribute,op,value) values(:username, 'MD5-Password', ':=', :password)";
	$stmtradcheck = $radconn->prepare($query);
	$stmtradcheck->bindValue('username', $_POST['username']);
	$stmtradcheck->bindValue('password', md5($_POST['password']));
	$stmtradcheck->execute();

	$query = "INSERT INTO radusergroup(username,groupname,priority) values(:username, 'Disabled Users', '99')";
	$stmtradusergroup = $radconn->prepare($query);
	$stmtradusergroup->bindValue('username', $_POST['username']);
	$stmtradusergroup->execute();

        if ($result) {
            $user_id = $conn->lastInsertId();

            // TO DO: send verification email to user
	    sendContactMail($email, $fullname, $token);

            $_SESSION['id'] = $user_id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['verified'] = false;
            $_SESSION['message'] = 'You are logged in!';
            $_SESSION['type'] = 'alert-success';
            header('location: index.php');
        } else {
            $_SESSION['error_msg'] = "Database error: Could not register user";
        }
    }
}

// Login

if (isset($_POST['login-btn'])) {
    if (empty($_POST['username'])) {
        $errors['username'] = 'Username or email required';
    }
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password required';
    }

    $recaptcha_url="https://www.google.com/recaptcha/api/siteverify";
    $secret_key=$recaptcha_secret_key;

    $post_data = http_build_query(
        array(
            'secret' => $secret_key,
            'response' => $_POST['recaptcha_response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post_data
        )
    );
    $context  = stream_context_create($opts);

    $recaptcha_response=$_POST['recaptcha_response'];
    $get_recaptcha_response=file_get_contents($recaptcha_url, false, $context);
    $response_json=json_decode($get_recaptcha_response);
    // print_r($get_recaptcha_response->score);
    if($response_json->success == true && $response_json->score>=0.5 && $response_json->action=='submit'){

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (count($errors) === 0) {
        $query = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $conn->prepare($query);
	$stmt->bindValue('username', strtolower($_POST['username']));
	$stmt->bindValue('email', $_POST['username']);

        if ($stmt->execute()) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if (password_verify($password, $user->password)) { // if password matches

                $_SESSION['id'] = $user->id;
                $_SESSION['name'] = $user->name;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                $_SESSION['verified'] = $user->verified;
                $_SESSION['isadmin'] = $user->isadmin;
		$_SESSION['2fa'] = $user->twoFA;
	if ($user->twoFA == '1') {
                header('location: verify2fa.php');
	} else {
                $_SESSION['message'] = 'You are logged in!';
                $_SESSION['type'] = 'alert-success';
                header('location: index.php');
	}
                exit(0);
            } else { // if password does not match
                $errors['login_fail'] = "Wrong username / password";
            }
        } else {
            $_SESSION['message'] = "Database error. Login failed!";
            $_SESSION['type'] = "alert-danger";
        }
   }
   } else {
                $errors['captcha_fail'] = "Fail Captcha Validation";
   }
}

// Make admin

if (isset($_POST['makeadmin-btn'])) {
   
   $username = $_POST['update-user'];

   $query = "UPDATE users SET isadmin = '1' WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['update-user']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');
        exit(0);
}


// Revoke admin

if (isset($_POST['revokeadmin-btn'])) {
   
   $username = $_POST['update-user'];

   $query = "UPDATE users SET isadmin = '0' WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['update-user']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');
        exit(0);
}


// Enable radius

if (isset($_POST['radenable-btn'])) {
   
   $username = $_POST['update-user'];

   $query = "DELETE FROM radusergroup WHERE username = :username AND groupname = 'Disabled Users'";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('username', $_POST['update-user']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');
        exit(0);
}


// Disable radius

if (isset($_POST['raddisable-btn'])) {
   
   $username = $_POST['update-user'];

   $query = "INSERT INTO radusergroup(username,groupname,priority) VALUES(:username, 'Disabled Users', '99')";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('username', $_POST['update-user']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');
        exit(0);
}

// Remove User

if (isset($_POST['deluser-btn'])) {

   $query = "DELETE FROM users WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['delete-user']);
   $stmt->execute();

   $queryrug = "DELETE FROM radusergroup WHERE username = :username";
   $stmtrug = $radconn->prepare($queryrug);
   $stmtrug->bindValue('username', $_POST['delete-user']);
   $stmtrug->execute();

   $queryrc = "DELETE FROM radcheck WHERE username = :username";
   $stmtrc = $radconn->prepare($queryrc);
   $stmtrc->bindValue('username', $_POST['delete-user']);
   $stmtrc->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');

}

// Change Password

if (isset($_POST['changepw-btn'])) {
    if (empty($_POST['oldpassword'])) {
        $errors['oldpassword'] = 'Old password required';
    }
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password required';
    }
    if (isset($_POST['password']) && $_POST['password'] !== $_POST['passwordConf']) {
        $errors['passwordConf'] = 'The two passwords do not match';
    }

    $username = $_SESSION['username'];
    $oldpassword = $_POST['oldpassword'];
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    if (count($errors) === 0) {
	$query = "SELECT * FROM users WHERE username = :username";
	$stmt = $conn->prepare($query);
	$stmt->bindValue('username', $_SESSION['username']);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_OBJ); 

	if (password_verify($oldpassword, $user->password)) {
	    $query = "UPDATE users SET password = :password WHERE username = :username";
	    $stmt = $conn->prepare($query);
	    $stmt->bindValue('username', $_SESSION['username']);
	    $stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
	    $stmt->execute();

	    $queryrad = "UPDATE radcheck SET value = :password WHERE username = :username";
	    $stmtrad = $radconn->prepare($queryrad);
            $stmtrad->bindValue('username', $_SESSION['username']);
	    $stmtrad->bindValue('password', md5($_POST['password']));
	    $stmtrad->execute();
 
                $_SESSION['id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                $_SESSION['verified'] = $user->verified;
                $_SESSION['isadmin'] = $user->isadmin;
		$_SESSION['message'] = 'Successfully change your password!';
		$_SESSION['type'] = 'alert-success';
		header('location: changepassword.php');
		exit(0); 
	} else {
		$errors['login_fail'] = "Old password doesn't match our record";
	
  }
 }
}


// Reset Password

if (isset($_POST['resetpw-btn'])) {
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password required';
    }
    if (isset($_POST['password']) && $_POST['password'] !== $_POST['passwordConf']) {
        $errors['passwordConf'] = 'The two passwords do not match';
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    if (count($errors) === 0) {
	    $query = "UPDATE users SET password = :password WHERE username = :username";
	    $stmt = $conn->prepare($query);
	    $stmt->bindValue('username', $_POST['username']);
	    $stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
	    $stmt->execute();

	    $queryrad = "UPDATE radcheck SET value = :password WHERE username = :username";
	    $stmtrad = $radconn->prepare($queryrad);
            $stmtrad->bindValue('username', $_POST['username']);
	    $stmtrad->bindValue('password', md5($_POST['password']));
	    $stmtrad->execute();
 
		$_SESSION['message'] = 'Successfully reset password for '.$_POST['username'].'!';
		$_SESSION['type'] = 'alert-success';
		header('location: resetpassword.php');
		exit(0); 
  }
}


// Add radius group

if (isset($_POST['newgroup-btn'])) {

   $radgroup = $_POST['radgroup'];

   $query = "INSERT INTO radiusgroups(groups) VALUES(:radgroup)";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('radgroup', $_POST['radgroup']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}

// Add group check

if (isset($_POST['addgroupcheck-btn'])) {

   $query = "INSERT INTO radgroupcheck(groupname,attribute,op,value) VALUES(:groupname, :attribute, :op, :value)";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('groupname', $_POST['radgroup']);
   $stmt->bindValue('attribute', $_POST['attribute']);
   $stmt->bindValue('op', $_POST['op']);
   $stmt->bindValue('value', $_POST['value']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}


// Add group reply

if (isset($_POST['addgroupreply-btn'])) {

   $query = "INSERT INTO radgroupreply(groupname,attribute,op,value) VALUES(:groupname, :attribute, :op, :value)";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('groupname', $_POST['radgroup']);
   $stmt->bindValue('attribute', $_POST['attribute']);
   $stmt->bindValue('op', $_POST['op']);
   $stmt->bindValue('value', $_POST['value']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}

// Remove Group

if (isset($_POST['delgroup-btn'])) {

   $query = "DELETE FROM radiusgroups WHERE groups = :radgroup";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('radgroup', $_POST['radgroup']);
   $stmt->execute();

   $queryrgc = "DELETE FROM radgroupcheck WHERE groupname = :radgroup";
   $stmtrgc = $radconn->prepare($queryrgc);
   $stmtrgc->bindValue('radgroup', $_POST['radgroup']);
   $stmtrgc->execute();

   $queryrgr = "DELETE FROM radgroupreply WHERE groupname = :radgroup";
   $stmtrgr = $radconn->prepare($queryrgr);
   $stmtrgr->bindValue('radgroup', $_POST['radgroup']);
   $stmtrgr->execute();

   $queryrug = "DELETE FROM radusergroup WHERE groupname = :radgroup";
   $stmtrug = $radconn->prepare($queryrug);
   $stmtrug->bindValue('radgroup', $_POST['radgroup']);
   $stmtrug->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}

// Delete Group Check

if (isset($_POST['delgroupcheck-btn'])) {

   $query = "DELETE FROM radgroupcheck WHERE attribute = :attribute AND groupname = :groupname";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('attribute', $_POST['attribute']);
   $stmt->bindValue('groupname', $_POST['radgroup']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}

// Delete Group Reply

if (isset($_POST['delgroupreply-btn'])) {

   $query = "DELETE FROM radgroupreply WHERE id = :id AND groupname = :groupname";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('id', $_POST['id']);
   $stmt->bindValue('groupname', $_POST['radgroup']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: radiusgroups.php');
        exit(0);

}

// Add user to group

if (isset($_POST['addusergroup-btn'])) {

    if (isset($_POST['priority']) && $_POST['priority'] < 100) {
        $errors['priority100'] = 'Priority has to be greater than 100';
    }

   $querypriority = "SELECT * FROM radusergroup WHERE username = :username AND priority = :priority LIMIT 1";
   $stmtpriority = $radconn->prepare($querypriority);
   $stmtpriority->bindValue('username', $_POST['username']);
   $stmtpriority->bindValue('priority', $_POST['priority']);
   $stmtpriority->execute();
   $countpriority = $stmtpriority->fetchColumn();
   if ($countpriority > 0) {
	$errors['priorityunique'] = "Priority have to be unique";
   }

    if (count($errors) === 0) {
       $query = "INSERT INTO radusergroup(username,priority,groupname) VALUES(:username, :priority, :groupname)";
       $stmt = $radconn->prepare($query);
       $stmt->bindValue('username', $_POST['username']);
       $stmt->bindValue('priority', $_POST['priority']);
       $stmt->bindValue('groupname', $_POST['groupname']);
       $stmt->execute();
    
            $_SESSION['message'] = 'Success!';
            $_SESSION['type'] = 'alert-success';
            header('location: usergroup.php');
            exit(0);

   }
}


// Remove user from group

if (isset($_POST['delusergroup-btn'])) {

   $query = "DELETE FROM radusergroup WHERE username = :username AND groupname = :groupname";
   $stmt = $radconn->prepare($query);
   $stmt->bindValue('username', $_POST['username']);
   $stmt->bindValue('groupname', $_POST['groupname']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: usergroup.php');
	exit(0);

}

// Enable 2FA

if (isset($_POST['enable2fa-btn'])) {

   $query = "SELECT * FROM users WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['username']);
   $stmt->execute();
   $user = $stmt->fetch(PDO::FETCH_OBJ);

   $g = new \Google\Authenticator\GoogleAuthenticator();
   $secret = $user->code2fa;

   $check_this_code = $_POST['twoFAcode'];

   if ($g->checkCode($secret, $check_this_code)) { 

   $query2fa = "UPDATE users SET twoFA = '1' WHERE username = :username";
   $stmt2fa = $conn->prepare($query2fa);
   $stmt2fa->bindValue('username', $_POST['username']);
   $stmt2fa->execute();

        $_SESSION['id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['verified'] = $user->verified;
        $_SESSION['isadmin'] = $user->isadmin;
        $_SESSION['2fa'] = '1';
	$_SESSION['verify2fa'] = '1';
        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: 2fa.php');
	exit(0);

   } else {

        $_SESSION['message'] = 'Invalid Code!';
        $_SESSION['type'] = 'alert-danger';
        header('location: 2fa.php');
	exit(0);

   }

}

// Disable 2FA

if (isset($_POST['disable2fa-btn'])) {

   $code2fa = Base32::encode(rand());

   $query = "UPDATE users SET twoFA = '0', code2fa = :code2fa WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['username']);
   $stmt->bindValue('code2fa', $code2fa);
   $stmt->execute();


	$_SESSION['2fa'] = '0';
        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: 2fa.php');
        exit(0);

}

// 2FA verification

if (isset($_POST['verify2fa-btn'])) {

   $query = "SELECT * FROM users WHERE username = :username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['username']);
   $stmt->execute();
   $user = $stmt->fetch(PDO::FETCH_OBJ);

   $g = new \Google\Authenticator\GoogleAuthenticator();
   $secret = $user->code2fa;

   $check_this_code = $_POST['twoFAcodeverify'];

   if ($g->checkCode($secret, $check_this_code)) {

        $_SESSION['id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['verified'] = $user->verified;
        $_SESSION['isadmin'] = $user->isadmin;
        $_SESSION['2fa'] = $user->twoFA;
	$_SESSION['verify2fa'] = '1';
        $_SESSION['message'] = 'You are logged in!';
        $_SESSION['type'] = 'alert-success';
	header('location: index.php');
	exit(0);

   } else {

        $_SESSION['message'] = 'Invalid Code!';
        $_SESSION['type'] = 'alert-danger';
        header('location: verify2fa.php');
        exit(0);
   }

}
