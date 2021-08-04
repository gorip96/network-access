<?php

require "config.php";

require_once ("lib/MailService.php");

session_start();
$username = "";
$email = "";
$errors = [];

$conn = new PDO("mysql:host=$dbhost;dbname=$dbname;port=$dbport", "$dbuser", "$dbpass");
$radconn = new PDO("mysql:host=$raddbhost;dbname=$raddbname;port=$raddbport", "$raddbuser", "$raddbpass");

// SIGN UP USER
if (isset($_POST['signup-btn'])) {
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

    $username = $_POST['username'];
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // generate unique token
    $password = $_POST['password'];

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
        $query = "insert into users(username, password, email, token) values(:username, :password, :email, :token)";
        $stmt = $conn->prepare($query);
	$stmt->bindValue('username', $_POST['username']);
	$stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
	$stmt->bindValue('email', $_POST['email']);
	$stmt->bindValue('token', $token);
	$result = $stmt->execute();

	$query = "insert into radcheck(username,attribute,op,value) values(:username, 'MD5-Password', ':=', :password)";
	$stmtradcheck = $radconn->prepare($query);
	$stmtradcheck->bindValue('username', $_POST['username']);
	$stmtradcheck->bindValue('password', md5($_POST['password']));
	$stmtradcheck->execute();

	$query = "insert into radusergroup(username,groupname,priority) values(:username, 'Disabled Users', '99')";
	$stmtradusergroup = $radconn->prepare($query);
	$stmtradusergroup->bindValue('username', $_POST['username']);
	$stmtradusergroup->execute();

        if ($result) {
            $user_id = $conn->lastInsertId();

            // TO DO: send verification email to user
	    sendContactMail($email, $username, $token);

            $_SESSION['id'] = $user_id;
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

// LOGIN
if (isset($_POST['login-btn'])) {
    if (empty($_POST['username'])) {
        $errors['username'] = 'Username or email required';
    }
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password required';
    }
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (count($errors) === 0) {
        $query = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $conn->prepare($query);
	$stmt->bindValue('username', $_POST['username']);
	$stmt->bindValue('email', $_POST['username']);

        if ($stmt->execute()) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if (password_verify($password, $user->password)) { // if password matches

                $_SESSION['id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                $_SESSION['verified'] = $user->verified;
                $_SESSION['isadmin'] = $user->isadmin;
                $_SESSION['message'] = 'You are logged in!';
                $_SESSION['type'] = 'alert-success';
                header('location: index.php');
                exit(0);
            } else { // if password does not match
                $errors['login_fail'] = "Wrong username / password";
            }
        } else {
            $_SESSION['message'] = "Database error. Login failed!";
            $_SESSION['type'] = "alert-danger";
        }
    }
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
	    $query = "UPDATE users set password = :password where username = :username";
	    $stmt = $conn->prepare($query);
	    $stmt->bindValue('username', $_SESSION['username']);
	    $stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
	    $stmt->execute();

	    $queryrad = "UPDATE radcheck set value = :password where username = :username";
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


// Make admin
if (isset($_POST['makeadmin-btn'])) {
   
   $username = $_POST['update-user'];

   $query = "UPDATE users SET isadmin = '1' where username = :username";
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

   $query = "UPDATE users SET isadmin = '0' where username = :username";
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


//   $radgroup = $_POST['radgroup'];

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


//   $radgroup = $_POST['radgroup'];

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

// Add user to group

if (isset($_POST['addusergroup-btn'])) {

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

}


// Remove User

if (isset($_POST['deluser-btn'])) {

   $query = "DELETE FROM users WHERE username =: username";
   $stmt = $conn->prepare($query);
   $stmt->bindValue('username', $_POST['delete-user']);
   $stmt->execute();

   $queryrug = "DELETE FROM radusergroup WHERE username =: username";
   $stmtrug = $radconn->prepare($queryrug);
   $stmt->bindValue('username', $_POST['delete-user']);
   $stmt->execute();

        $_SESSION['message'] = 'Success!';
        $_SESSION['type'] = 'alert-success';
        header('location: index.php');

}
