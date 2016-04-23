<?php
session_start();
require_once('config.inc.php');
require_once('user.php');
require_once('model.php');

// Getting user folder
$userobj = unserialize($_SESSION['user']);
$user_path = "/var/www/user_output/".$userobj->getFolderName().'/';

// Updating the working model trans file
$workingModel = $userobj->workingModel; // = $_POST['transData'];
$workingModel->transTxt = $_POST['transData'];


// Updateing the model.trans file in usersfolder
// NOTE: we don't update the database because it hasn't
// been saved properly
$trans_handler = fopen($user_path.'wall/model.trans' , 'w');
fwrite($trans_handler, $_POST['transData']);


// Close and update
fclose($trans_handler);
$_SESSION['user'] = serialize($userobj);

// We will refresh afer this in the resize function
// found in sketching_ui. This will load bindly from
// that folder
?>
