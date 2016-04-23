<?php

// Get Session
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');


$user_type = "normal";

if(!isset($_COOKIE['oasis_developer'])) 
{
	$user_type = "normal";
}
else
{
	$user_type = "developer";
}


// send off json obj
echo json_encode(array("data" => $user_type ));

?>