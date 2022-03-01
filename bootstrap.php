<?php
require(__DIR__.'/.settings.php');
require_once(__DIR__.'/functions.php');

define('REQUEST_TIME', (int) $_SERVER['REQUEST_TIME']);
if(!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'America/New_York');
}

date_default_timezone_set(APP_TIMEZONE);

// Starts a PHP Session.
session_start();


// Check for remember-me.
if(!isLoggedIn() && ($remembered_user = validate_auth_token())) {
    loggedin_init($remembered_user);
}

// Redirect to the login page if not logged in.
if(!isLoggedIn() && $_SERVER['REQUEST_URI'] !== '/login.php') {
    redirect('/login.php');
}
