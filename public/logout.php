<?php
require(__DIR__.'/../bootstrap.php');

session_unset();
session_destroy();
redirect('/login.php');