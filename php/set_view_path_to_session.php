<?php
error_log("task_remesh.php: Start");

session_start();
require_once('config.inc.php');          
require_once('user.php');
require_once('model.php');


$userobj       = unserialize($_SESSION['user']);
$username      = $userobj->username;
$session_model = $userobj->workingModel;
$id            = $session_model->id;

// Hashed version of the users folder name
$user_folder_name  = $userobj->getUserFolderName();

// ========================================
// Setting global view path
// ========================================
$_SESSION['view_path'] = '../user_output/'.$user_folder_name.'/'.'model_'.$id.'/slow/';
error_log("task_remesh.php: Done");
?>
