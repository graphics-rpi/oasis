<?php

session_start();
require_once('user.php');
require_once('model.php');


// Getting user object
$userobj = unserialize($_SESSION['user']);

// If we really want to log out then logout
// else we just recreate the folders after a logout
if($_POST['logout'] == "true"){
  $userobj->logout();
}else{
  $userobj->clear_user_folders();
}


?>
