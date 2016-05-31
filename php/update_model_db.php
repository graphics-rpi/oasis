<?php
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');
$userobj = unserialize($_SESSION['user']);

$userobj->save_to_database();

$userobj->workingModel->setStatus("Exisiting");

$_SESSION['user'] = serialize($userobj);

$username = $userobj->username;
$id = $userobj->workingModel->id; 

pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
	date("Y-m-d").'|'.date("h:i:sa"),
	$username,
	$id,
	"update_model_db.php",
	""
));

?>
