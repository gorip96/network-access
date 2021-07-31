<?php
/**
 * Copyright (C) Vincy - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Vincy <vincy@phppot.com>
 */
namespace Phppot;

session_start();
require '../init.php';
    $stmt = $conn->prepare('select * from users where username = :username');
        $stmt->bindValue('username', $_SESSION['username']);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_OBJ);
/**
 * This class contains the configuration options
 */
class Config
{

    const SENDER_NAME = 'Riv';

    const SENDER_EMAIL = 'riv@ixtelecom.net';

    // you can add one or more emails separated by a comma (,).
    const RECIPIENT_EMAIL = $account->email;

    const OAUTH_USER_EMAIL = 'riv@ixtelecom.net';

    const OAUTH_CLIENT_ID = '';

    const OAUTH_SECRET_KEY = '';

    const REFRESH_TOKEN = '';

    const SMTP_HOST = 'smtp.gmail.com';

    const SMTP_PORT = 587;
}
