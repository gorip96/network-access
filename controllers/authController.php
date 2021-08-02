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
    $countuser = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $countstmt = $conn->prepare($countuser);
    $countstmt->bindValue('email', $_POST['email']);
    $countstmt->execute();
    $result = $countstmt->fetchColumn();
    if ($result > 0) {
        $errors['email'] = "Email already exists";
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

	$query = "insert into radusergroup(username,groupname,priority) values(:username, 'Disabled Users', '1')";
	$stmtradusergroup = $radconn->prepare($query);
	$stmtradusergroup->bindValue('username', $_POST['username']);
	$stmtradusergroup->execute();

        if ($result) {
            $user_id = $conn->lastInsertId();

            // TO DO: send verification email to user
            // sendVerificationEmail($email, $token);
	    sendContactMail($email, $username, $token);
	    // MailService($email, $username, $token);

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
