<?php

session_start();
require_once('user.php');
require_once('model.php');


// Getting user object
$userobj = unserialize($_SESSION['user']);
$userobj->logout();

?>
