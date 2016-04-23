<?php
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');

// Get user object
$userobj       = unserialize($_SESSION['user']);
$username      = $userobj->username;
$session_model = $userobj->workingModel;

// We need to first update our session variable
$id 				       = "Not Assigned"; 

// Meta
// $title          = filter_var( $_POST['title'],  FILTER_SANITIZE_STRING);
$session_model->user_model_num    = $session_model->user_model_num;
$session_model->user_renov_num    = $session_model->user_renov_num + 1;

// Data
// $wallfile_txt 	   = $_POST['wallfile_txt'];
// $paths_txt		     = $_POST['paths_txt'];

// Getting unique Model id
$cmd = "SELECT id FROM model_meta;";
$res = pg_query($cmd);
$session_model->id  = pg_num_rows($res);


// Update the working/session model
// $session_model = new Model(
//   $id,
//   $title,
//   $user_model_num,
//   $user_renov_num,
//   $wallfile_txt,
//   $paths_txt
// );

$session_model->setStatus("Exisiting");

$_SESSION['user'] = serialize($userobj);

// Now that our session is sync with ui
// We send this to the database
$userobj->save_to_database();

pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
	date("Y-m-d").'|'.date("h:i:sa"),
	$username,
	$id,
	"update_model_db.php",
	""
));

?>
