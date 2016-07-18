<?php
session_start();
require_once('config.inc.php'); // Connect to DB
require_once('user.php');
require_once('model.php');

// Get user object
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;
$session_model = $userobj->workingModel;

// Updating title
$session_model->title = filter_var( $_POST['title'],  FILTER_SANITIZE_STRING);

if($session_model == ""){
	// untitled model
	$session_model = "untitled model";
}


// Updating data
$session_model->wallfile_txt = $_POST['wallfile_txt'];
$session_model->paths_txt    = $_POST['paths_txt'];

// Check if this is a new model that has been just edited
if ($session_model->status == 'New'){
  $session_model->status = 'New Edited';
}

$_SESSION['user']      = serialize($userobj);


echo serialize($session_model);
?>
