
<?php

// Get Session
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');


// Get user object
$userobj       = unserialize($_SESSION['user']);
$username      = $userobj->username;
$session_model = $userobj->workingModel;
$title         = $session_model->title;
error_log("get_model_title.php: ".$title);

// send off json obj
echo json_encode(array("data" => $title ));

?>
