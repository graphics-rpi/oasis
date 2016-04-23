<?php

session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');

// =======================================================
// Setting view path
// =======================================================
$view_path = $_POST['path'];
$_SESSION['view_path'] = $view_path;

// =======================================================
// Loading in this model in model session id
// =======================================================

// Get user object
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

// This is the model we are looking to load
$id                = $_POST['id'];

// Meta
$title			     = "Not Assigned";
$user_model_num    	 = "Not Assigned";
$user_renov_num      = "Not Assigned";

// Data
$wallfile_txt 	   	 = "Not Assigned";
$paths_txt		     = "Not Assigned";

// =========================================================
// Lets find the id most recent renovation of this model
// =========================================================
$cmd = 'SELECT user_model_num FROM model_meta WHERE id=$1';
$res = pg_query_params($cmd, array($id));
$user_model_num = (pg_fetch_row($res));
$user_model_num = $user_model_num[0];

$cmd = 'SELECT id FROM model_meta WHERE username=$1 AND user_model_num=$2 ORDER BY user_renov_num DESC';
$res = pg_query_params($cmd, array($username, $user_model_num));
$id  = pg_fetch_row($res);
$id  = $id[0];

// ========================================================
// Lets load this in our session
// ========================================================

// Lets collect the meta data from this model
$cmd = 'SELECT * FROM model_meta WHERE id=$1' ;
error_log('CMD: '.$cmd);
$res = pg_query_params($cmd, array($id));

if(pg_num_rows($res) > 1)
{ 
	error_log("Returned more then one model with id"); 
	die("Returned more then one model with id");
}

// Load all the metadata
while( $data = pg_fetch_row($res) )
{
	$title = $data[1];
	$user_model_num = $data[3];
	$user_renov_num = $data[4];
}

// Lets collect the meta data from this model
$cmd = "SELECT * FROM model_data WHERE id=$1";
error_log('CMD: '.$cmd);
$res = pg_query_params($cmd, array($id));

if(pg_num_rows($res) > 1)
{ 
	error_log("Returned more then one model with id"); 
	die("Returned more then one model with id");
}

// Load all the metadata
while( $data = pg_fetch_row($res) )
{
	$wallfile_txt = $data[1];
	$paths_txt    = $data[2];
}

// Update the working/session model
$session_model = new Model(
  $id,
  $title,
  $user_model_num,
  $user_renov_num,
  $wallfile_txt,
  $paths_txt
);

// Setting this as exisiting status
$session_model->setStatus('Exisiting');

$userobj->workingModel = $session_model;
$_SESSION['user'] = serialize($userobj);

?>
