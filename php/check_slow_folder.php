<?php

// This file returns the username and foldername of the current users so that
// sketching.js knowns where to look to load in previous models
session_start();
require_once('user.php');
require_once('model.php');

error_log("check_slow_folder.php: begin");

$model_status = "none";

// Getting user object
$userobj = unserialize($_SESSION['user']);

// Getting folder name
$session_model = $userobj->workingModel;
$id = $session_model->id;

// Hashed version of the users folder name
$model_folder_path = '/var/www/user_output/geometry/'.$id.'/';

// Check if this file exisit
//
// Recreating the path where we need our slow file to be in
if(file_exists($model_folder_path))
{
  $slow_obj = $model_folder_path.'slow/foo.obj';

  if( file_exists($slow_obj))
  {
    $model_status = 'ready'; // We have a slow file
  }
  else
  {
    $model_status = 'error'; // We do not have slow file to do lighting clac
  }
}
else
{
  $model_status = "update"; // We didn't even run yet
}

error_log("check_slow_folder.php: status is ".$model_status);
// Generate json object needed by sketchui
// to find user folder
echo json_encode(array("stat" => $model_status));

error_log("check_slow_folder.php: end");
?>
