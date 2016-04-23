<?php
session_start();
require_once('config.inc.php'); // Connect to DB
require_once('user.php');
require_once('model.php');

// Get user object
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;

// We need to create blank files
$id = "Not Assigned";

// Meta
$title          = "Not Assigned";
$user_model_num = "Not Assigned";
$user_renov_num = "Not Assigned";

// Data
$wallfile_txt = "Not Assigned";
$paths_txt    = "Not Assigned";

// Update the working/session model
$session_model = new Model($id, $title, $user_model_num, $user_renov_num, $wallfile_txt, $paths_txt);
$session_model->setStatus('New');

$userobj->workingModel = $session_model;
$_SESSION['user']      = serialize($userobj);

?>
