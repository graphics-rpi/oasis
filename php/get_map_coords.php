<?php
error_log("get_map_coords: Start");

session_start();
require_once('config.inc.php');          
require_once('user.php');
require_once('model.php');

// Get user object and username and session model
$userobj       = unserialize($_SESSION['user']);
$session_model = $userobj->workingModel;

$txt = $session_model->wallfile_txt;
echo $txt;

error_log("get_map_coords: Done");
?>
