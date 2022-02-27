<?php
require(__DIR__.'/.settings.php');
require_once(__DIR__.'/functions.php');

define('REQUEST_TIME', (int) $_SERVER['REQUEST_TIME']);
define('APP_TIMEZONE', 'America/New_York');

// Starts a PHP Session.
session_start();

// Redirect to the login page if not logged in.
if(!isLoggedIn() && $_SERVER['REQUEST_URI'] !== '/login.php') {
    redirect('/login.php');
}
