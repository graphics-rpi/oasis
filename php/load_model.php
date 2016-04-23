<?php
session_start();
require_once('user.php');
require_once('model.php');


// Getting user object
$userobj = unserialize($_SESSION['user']);

// Getting id from load_previous_model in util.js
$uniqueId = $_POST['id'];

$modelNum = $userobj->pull_modelNum_db($uniqueId);

$renovationNum = $userobj->pull_renovationNum_db($uniqueId);

$wallTxt = $userobj->pull_wall_db($uniqueId);

$transTxt = $userobj->pull_tran_db($uniqueId);

$model_obj =  new Model($uniqueId, $modelNum, $renovationNum, $wallTxt, $transTxt);

// Loaded
$userobj->workingModel = $model_obj;

$userobj->create_render_files();

$_SESSION['user'] = serialize($userobj);

?>
