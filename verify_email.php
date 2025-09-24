<?php

include 'controllers/authController.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $query = "SELECT * FROM users WHERE token='$token' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetchColumn();

    if ($result > 0) {
        $user = $stmt->fetch(PDO::FETCH_OBJ);;
        $query = "UPDATE users SET verified=1 WHERE token='$token'";
	$stmtupdate = $conn->prepare($query);
	$stmtupdate->execute();
	$resultupdate =  $stmtupdate->execute();

        if ($resultupdate) {
            $_SESSION['id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['verified'] = true;
            $_SESSION['message'] = "Your email address has been verified successfully";
            $_SESSION['type'] = 'alert-success';
            header('location: index.php');
            exit(0);
        }
    } else {
        echo "User not found!";
    }
} else {
    echo "No token provided!";
}
