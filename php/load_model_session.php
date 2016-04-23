<?php
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');

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
// TODO: Find values to assign before uploading working model
// =========================================================

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


// Change view path match
$user_folder_name  = $userobj->getUserFolderName();
$_SESSION['view_path'] = '../user_output/'.$user_folder_name.'/'.'model_'.$id.'/slow/';

$userobj->workingModel = $session_model;
$_SESSION['user'] = serialize($userobj);



?>
