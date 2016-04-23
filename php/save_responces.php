<?php
	
	session_start();
	require_once('config.inc.php');
	require_once('user.php');
	require_once('model.php');
	

	// Script is called when a form is submited and needs to saved to the database
	// Results of the forms are sent in the form of request
	
	// What tab am I on? Use this to get all the qid's + questions strings
	$tab = $_SESSION["tab"];

	// Getting user object + getting the model's unique ID
	$userobj = unserialize($_SESSION['user']);
	$username = $userobj->username;
	$workingModel = $userobj->workingModel; // = $_POST['transData'];	
  	$model_id = $workingModel->id;
	

	// Search though the feedback_question table to get questions that are in this tab
	$results = pg_query_params('SELECT qid, question FROM feedback_questions WHERE tab=$1', array($tab));
	
	// For all questions found for this tab find the correspting answers and record!
	while($cur_row = pg_fetch_row($results) ){
	
		$qid 			= $cur_row[0];
		$question 		= $cur_row[1];
		$str_name 		= $tab."_".$qid;
		$responce 		= $_POST[$str_name];

		if($responce == ""){
			$responce = 'NaN';
    }else{
      // scrub responce
		  $responce 		= filter_var($responce, FILTER_SANITIZE_STRING); 
    }
		
		// Did this user answer this question before?
		$fb_str = 'SELECT responce FROM feedback_responces WHERE qid='.$qid.' AND username=\''.$username.'\' ;';
		$responce_result = pg_query($fb_str);
		
		if ( pg_num_rows( $responce_result ) > 0 ) {	

			// Update entry
			$update_str = 'UPDATE feedback_responces SET responce = \''.$responce.'\' WHERE qid='.$qid.' AND username=\''.$username.'\' ;';
			pg_query($update_str);

		}else{
			
			// New entry
			 pg_query_params('INSERT INTO feedback_responces VALUES($1,$2,$3,$4,$5,$6)',
				 array($qid,$username,$model_id,$tab,$question,$responce));
			
			error_log("save_responces to INSERT feedback_responces", 0);
		}
		
	
	}
	
	
	// These are the questions I am expecting, prepare to save them each
	// Each requires qid, username, model_id, tab, question, responce
	
	
	
	error_log("save_responces ran! We are saving to ".$model_id, 0);
	
	
	
?>
