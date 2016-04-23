<?php
session_start();
require_once('config.inc.php'); // Connect to DB
require_once('user.php');
require_once('model.php');

// Get user object
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;

// We need to first update our session variable
$id = "Not Assigned";

// Meta
$title          = $userobj->workingModel->title;
$user_model_num = "Not Assigned";
$user_renov_num = 0;

// Data
$wallfile_txt = $userobj->workingModel->wallfile_txt;
$paths_txt    = $userobj->workingModel->paths_txt;

// Getting unique Model id
$cmd = "SELECT id FROM model_meta;";
$res = pg_query($cmd);
$id  = pg_num_rows($res);

// Getting model numbers ordered
$cmd = "SELECT id FROM model_meta WHERE username='$username' AND user_renov_num=0;";
$res = pg_query($cmd);
$user_model_num = pg_num_rows($res);

// Update the working/session model
$session_model = new Model(
  $id, 
  $title, 
  $user_model_num, 
  $user_renov_num, 
  $wallfile_txt, 
  $paths_txt
);

// Because the model is saved, this is no longer
// just a new model.
$session_model->setStatus("Exisiting");

$userobj->workingModel = $session_model;

$_SESSION['user']      = serialize($userobj);

// Now that our session is sync with ui
// We send this to the database
$userobj->save_to_database();

  pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
    date("Y-m-d").'|'.date("h:i:sa"),
    $username,
    $id,
    "save_model_db.php",
    ""
  ));

?>
