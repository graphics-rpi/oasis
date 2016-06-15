<?php
	
	session_start();
	require_once('config.inc.php');
	require_once('user.php');
	require_once('model.php');
	

	// Script is called when a form is submited and needs to saved to the database
	// Results of the forms are sent in the form of request
	
	// What tab am I on? Use this to get all the qid's + questions strings
	
	// // Getting user object + getting the model's unique ID
	$userobj = unserialize($_SESSION['user']);
	$username = $userobj->username;
  	$model_id = $workingModel->id;
  	$tab = $userobj->tab;

    $email = $_POST["bug_email"];
    $intent = $_POST["intentions"];
    $oc = $_POST["outcome"];
    $rep = $_POST["repro"];
    $ot = $_POST["other"];

	//Something to write to txt logs
		$log  = "-------------------------".PHP_EOL.
				"User: ".$username.PHP_EOL.
				"Email: ".$email.PHP_EOL.
				"Date: ".date("F j, Y, g:i a").PHP_EOL.
				"Working on tab: ".$tab.PHP_EOL.
				"Model: ".$model_id.PHP_EOL.
		        "Intentions: ".$intent.PHP_EOL.
		        "Outcome: ".$oc.PHP_EOL.
		        "Reproduction: ".$rep.PHP_EOL.
		        "Other: ".$ot.PHP_EOL.
		        "-------------------------".PHP_EOL;

	$dest = '../bug_reports/'.$username.date('_sgifj').'.txt';
	//Save string to log, use FILE_APPEND to append.
	file_put_contents($dest, $log, FILE_APPEND | LOCK_EX);
?>
